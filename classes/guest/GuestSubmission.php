<?php

/**
 * @file classes/guest/GuestSubmission.php
 *
 * Guest Submission Model
 */

namespace APP\guest;

use Illuminate\Support\Facades\DB;

class GuestSubmission
{
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVISION_REQUIRED = 'revision_required';

    // Decision constants
    const DECISION_ACCEPT = 'accept';
    const DECISION_REJECT = 'reject';
    const DECISION_REVISION = 'revision_required';

    /**
     * Create a new guest submission
     */
    public static function create($data)
    {
        $submissionId = DB::table('guest_submissions')->insertGetId([
            'context_id' => (int)($data['context_id'] ?? 1),
            'manuscript_title' => $data['manuscript_title'],
            'article_type' => $data['article_type'],
            'abstract' => $data['abstract'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'manuscript_file_path' => $data['manuscript_file_path'] ?? null,
            'manuscript_file_name' => $data['manuscript_file_name'] ?? null,
            'status' => self::STATUS_PENDING,
            'date_submitted' => DB::raw('NOW()'),
        ]);

        // Add authors
        if (!empty($data['authors'])) {
            foreach ($data['authors'] as $index => $author) {
                self::addAuthor($submissionId, $author, $index);
            }
        }

        // Log creation
        self::addLog($submissionId, null, 'submission_created', 'Submission created via guest form');

        return $submissionId;
    }

    /**
     * Add an author to a submission
     */
    public static function addAuthor($submissionId, $authorData, $seq = 0)
    {
        return DB::table('guest_submission_authors')->insert([
            'submission_id' => (int)$submissionId,
            'title' => $authorData['title'] ?? null,
            'first_name' => $authorData['name'],
            'last_name' => $authorData['surname'],
            'authorship' => $authorData['authorship'] ?? null,
            'email' => $authorData['email'],
            'address' => $authorData['address'] ?? null,
            'affiliation' => $authorData['affiliation'] ?? null,
            'seq' => (int)$seq,
        ]);
    }

    /**
     * Get a submission by ID
     */
    public static function getById($submissionId)
    {
        $submission = DB::table('guest_submissions')
            ->where('submission_id', (int)$submissionId)
            ->first();
        
        if (!$submission) {
            return null;
        }
        
        $submission->authors = self::getAuthors($submissionId);
        $submission->logs = self::getLogs($submissionId);

        return $submission;
    }

    /**
     * Get authors for a submission
     */
    public static function getAuthors($submissionId)
    {
        return DB::table('guest_submission_authors')
            ->where('submission_id', (int)$submissionId)
            ->orderBy('seq')
            ->get();
    }

    /**
     * Get logs for a submission
     */
    public static function getLogs($submissionId)
    {
        return DB::table('guest_submission_log')
            ->where('submission_id', (int)$submissionId)
            ->orderBy('date_logged', 'desc')
            ->get();
    }

    /**
     * Get all submissions with optional filters
     */
    public static function getAll($filters = [])
    {
        $query = DB::table('guest_submissions');
        
        if (!empty($filters['context_id'])) {
            $query->where('context_id', (int)$filters['context_id']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['assigned_editor_id'])) {
            $query->where('assigned_editor_id', (int)$filters['assigned_editor_id']);
        }
        
        $submissions = $query->orderBy('date_submitted', 'desc')->get();
        
        // Attach authors to each submission
        foreach ($submissions as $submission) {
            $submission->authors = self::getAuthors($submission->submission_id);
        }
        
        return $submissions;
    }

    /**
     * Assign submission to an editor
     */
    public static function assign($submissionId, $editorId, $userId = null)
    {
        $result = DB::table('guest_submissions')
            ->where('submission_id', (int)$submissionId)
            ->update([
                'assigned_editor_id' => (int)$editorId,
                'status' => self::STATUS_ASSIGNED,
                'date_assigned' => DB::raw('NOW()'),
            ]);
        
        if ($result) {
            self::addLog($submissionId, $userId, 'assigned', "Assigned to editor ID: $editorId");
        }
        
        return $result;
    }

    /**
     * Update submission status
     */
    public static function updateStatus($submissionId, $status, $userId = null)
    {
        $result = DB::table('guest_submissions')
            ->where('submission_id', (int)$submissionId)
            ->update(['status' => $status]);
        
        if ($result) {
            self::addLog($submissionId, $userId, 'status_changed', "Status changed to: $status");
        }
        
        return $result;
    }

    /**
     * Make a decision on a submission
     */
    public static function makeDecision($submissionId, $decision, $notes, $userId = null)
    {
        $status = self::STATUS_PENDING;
        
        switch ($decision) {
            case self::DECISION_ACCEPT:
                $status = self::STATUS_ACCEPTED;
                break;
            case self::DECISION_REJECT:
                $status = self::STATUS_REJECTED;
                break;
            case self::DECISION_REVISION:
                $status = self::STATUS_REVISION_REQUIRED;
                break;
        }

        $result = DB::table('guest_submissions')
            ->where('submission_id', (int)$submissionId)
            ->update([
                'decision' => $decision,
                'decision_notes' => $notes,
                'status' => $status,
                'date_decided' => DB::raw('NOW()'),
            ]);
        
        if ($result) {
            self::addLog($submissionId, $userId, 'decision_made', "Decision: $decision");
        }
        
        return $result;
    }

    /**
     * Update admin notes
     */
    public static function updateNotes($submissionId, $notes, $userId = null)
    {
        $result = DB::table('guest_submissions')
            ->where('submission_id', (int)$submissionId)
            ->update(['admin_notes' => $notes]);
        
        if ($result) {
            self::addLog($submissionId, $userId, 'notes_updated', 'Admin notes updated');
        }
        
        return $result;
    }

    /**
     * Add a log entry
     */
    public static function addLog($submissionId, $userId, $action, $message)
    {
        return DB::table('guest_submission_log')->insert([
            'submission_id' => (int)$submissionId,
            'user_id' => $userId ? (int)$userId : null,
            'action' => $action,
            'message' => $message,
            'date_logged' => DB::raw('NOW()'),
        ]);
    }

    /**
     * Delete a submission
     */
    public static function delete($submissionId)
    {
        $submissionId = (int)$submissionId;
        
        // Delete authors
        DB::table('guest_submission_authors')->where('submission_id', $submissionId)->delete();
        
        // Delete logs
        DB::table('guest_submission_log')->where('submission_id', $submissionId)->delete();
        
        // Delete submission
        return DB::table('guest_submissions')->where('submission_id', $submissionId)->delete();
    }

    /**
     * Get submission statistics
     */
    public static function getStatistics($contextId = null)
    {
        $query = DB::table('guest_submissions');
        
        if ($contextId) {
            $query->where('context_id', (int)$contextId);
        }
        
        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', self::STATUS_PENDING)->count();
        $assigned = (clone $query)->where('status', self::STATUS_ASSIGNED)->count();
        $underReview = (clone $query)->where('status', self::STATUS_UNDER_REVIEW)->count();
        $accepted = (clone $query)->where('status', self::STATUS_ACCEPTED)->count();
        $rejected = (clone $query)->where('status', self::STATUS_REJECTED)->count();
        $revisionRequired = (clone $query)->where('status', self::STATUS_REVISION_REQUIRED)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'assigned' => $assigned,
            'under_review' => $underReview,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'revision_required' => $revisionRequired,
        ];
    }
}
