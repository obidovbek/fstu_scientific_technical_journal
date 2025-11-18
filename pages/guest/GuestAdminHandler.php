<?php

/**
 * @file pages/guest/GuestAdminHandler.php
 *
 * Admin handler for managing guest submissions
 */

namespace APP\pages\guest;

use APP\handler\Handler;
use APP\template\TemplateManager;
use PKP\core\PKPRequest;
use PKP\security\authorization\PKPSiteAccessPolicy;
use PKP\security\Role;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// Import GuestSubmission class
import('classes.guest.GuestSubmission');

class GuestAdminHandler extends Handler
{
    /** @copydoc PKPHandler::_isBackendPage */
    public $_isBackendPage = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        try {
            error_log('GuestAdminHandler::__construct() called');
            parent::__construct();
            
            // Assign roles to operations
            $this->addRoleAssignment(
                [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER],
                [
                    'admin', 'index', 'view', 'assign', 'decide', 
                    'updateStatus', 'updateNotes', 'download', 'delete'
                ]
            );
            error_log('GuestAdminHandler::__construct() completed');
        } catch (\Throwable $e) {
            error_log('GuestAdminHandler::__construct() ERROR: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * @see PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        try {
            error_log('GuestAdminHandler::authorize() called');
            // Use PKPSiteAccessPolicy with roleAssignments
            $this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
            
            $result = parent::authorize($request, $args, $roleAssignments);
            error_log('GuestAdminHandler::authorize() completed, result: ' . ($result ? 'true' : 'false'));
            return $result;
        } catch (\Throwable $e) {
            error_log('GuestAdminHandler::authorize() ERROR: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Handle admin operations - routes to appropriate method based on path
     * URL: /itj/guest/admin -> index (dashboard)
     * URL: /itj/guest/admin/view/{id} -> view submission
     * URL: /itj/guest/admin/download/{id} -> download file
     * etc.
     */
    public function admin($args, $request)
    {
        try {
            // Log for debugging
            error_log('GuestAdminHandler::admin() called with args: ' . print_r($args, true));
            
            // Check if there's a sub-operation in the args
            // For URL /itj/guest/admin/view/1, args would be ['view', '1']
            // For URL /itj/guest/admin, args would be empty
            
            if (empty($args)) {
                // No sub-operation, show dashboard
                $this->index($args, $request);
            } elseif (isset($args[0]) && $args[0] === 'view' && isset($args[1])) {
                // View specific submission: /admin/view/{id}
                $this->view([$args[1]], $request);
            } elseif (isset($args[0]) && $args[0] === 'download' && isset($args[1])) {
                // Download file: /admin/download/{id}
                $this->download([$args[1]], $request);
            } elseif (isset($args[0]) && $args[0] === 'assign') {
                // Assign submission (AJAX): /admin/assign
                $this->assign($args, $request);
            } elseif (isset($args[0]) && $args[0] === 'decide') {
                // Make decision (AJAX): /admin/decide
                $this->decide($args, $request);
            } elseif (isset($args[0]) && $args[0] === 'updateStatus') {
                // Update status (AJAX): /admin/updateStatus
                $this->updateStatus($args, $request);
            } elseif (isset($args[0]) && $args[0] === 'updateNotes') {
                // Update notes (AJAX): /admin/updateNotes
                $this->updateNotes($args, $request);
            } elseif (isset($args[0]) && $args[0] === 'delete') {
                // Delete submission (AJAX): /admin/delete
                $this->delete($args, $request);
            } else {
                // Default to dashboard
                $this->index($args, $request);
            }
        } catch (\Throwable $e) {
            error_log('GuestAdminHandler::admin() FATAL ERROR: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e; // Re-throw to show error page
        }
    }

    /**
     * Display admin dashboard
     */
    public function index($args, $request)
    {
        error_log('GuestAdminHandler::index() called');
        
        try {
            $templateMgr = TemplateManager::getManager($request);
            error_log('TemplateManager obtained');
            
            $this->setupTemplate($request);
            error_log('Template setup complete');
            
            // Add custom styles for guest admin dashboard
            $customStyles = '
.guest-admin-dashboard {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #2563eb;
}

.stat-card.pending { border-left-color: #f59e0b; }
.stat-card.assigned { border-left-color: #3b82f6; }
.stat-card.accepted { border-left-color: #059669; }
.stat-card.rejected { border-left-color: #dc2626; }

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #1f2937;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    text-transform: uppercase;
    margin-top: 5px;
}

.submissions-table {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    background: #f9fafb;
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h2 {
    margin: 0;
    font-size: 1.25rem;
    color: #1f2937;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.filter-btn {
    padding: 6px 12px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: #f9fafb;
}

.filter-btn.active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}

.guest-admin-dashboard table {
    width: 100%;
    border-collapse: collapse;
}

.guest-admin-dashboard thead {
    background: #f9fafb;
}

.guest-admin-dashboard th {
    text-align: left;
    padding: 12px 20px;
    font-weight: 600;
    color: #1f2937;
    font-size: 0.875rem;
    border-bottom: 2px solid #e5e7eb;
}

.guest-admin-dashboard td {
    padding: 12px 20px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.875rem;
}

.guest-admin-dashboard tr:hover {
    background: #f9fafb;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-assigned {
    background: #dbeafe;
    color: #1e40af;
}

.status-under_review {
    background: #e0e7ff;
    color: #3730a3;
}

.status-accepted {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.status-revision_required {
    background: #fed7aa;
    color: #9a3412;
}

.action-btn {
    padding: 6px 12px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #1d4ed8;
}

.action-btn.secondary {
    background: #6b7280;
}

.action-btn.secondary:hover {
    background: #4b5563;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state svg {
    width: 64px;
    height: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}
';
            
            $templateMgr->addStyleSheet(
                'guestAdminDashboard',
                $customStyles,
                [
                    'priority' => \PKP\template\PKPTemplateManager::STYLE_SEQUENCE_LAST,
                    'contexts' => ['backend'],
                    'inline' => true,
                ]
            );
            
            // Check if class exists
            if (!class_exists('APP\guest\GuestSubmission')) {
                error_log('GuestSubmission class NOT found');
                throw new \Exception('GuestSubmission class not found. Please clear cache.');
            }
            error_log('GuestSubmission class found');
            
            // Get statistics
            error_log('Calling getStatistics()...');
            $stats = \APP\guest\GuestSubmission::getStatistics();
            error_log('getStatistics() completed');
            
            // Get all submissions
            error_log('Calling getAll()...');
            $submissions = \APP\guest\GuestSubmission::getAll();
            error_log('getAll() completed, found ' . count($submissions) . ' submissions');
            
            // Convert Laravel collections to arrays for Smarty templates
            $submissionsArray = [];
            if ($submissions) {
                foreach ($submissions as $submission) {
                    $sub = (array)$submission;
                    if (isset($submission->authors) && method_exists($submission->authors, 'toArray')) {
                        $sub['authors'] = $submission->authors->toArray();
                    } else {
                        $sub['authors'] = is_array($submission->authors) ? $submission->authors : [];
                    }
                    $submissionsArray[] = (object)$sub;
                }
            }

            $templateMgr->assign([
                'pageTitle' => __('plugins.generic.guestSubmission.admin.dashboard'),
                'stats' => $stats,
                'submissions' => $submissionsArray,
            ]);
            
            // Set page title for backend template
            $templateMgr->assign('pageTitleTranslated', 'Guest Submissions Dashboard');

            error_log('Displaying template...');
            $templateMgr->display('guest/admin/dashboard.tpl');
            error_log('Template displayed successfully');
        } catch (\Throwable $e) {
            // Log the error
            error_log('GuestAdminHandler::index() ERROR: ' . $e->getMessage());
            error_log('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Try to show error page
            try {
                $templateMgr = TemplateManager::getManager($request);
                $this->setupTemplate($request);
                
                $templateMgr->assign([
                    'pageTitle' => 'Guest Submissions Dashboard - Error',
                    'error' => 'An error occurred: ' . htmlspecialchars($e->getMessage()),
                    'errorDetails' => 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . '. Please check PHP error logs.',
                    'stats' => ['total' => 0, 'pending' => 0, 'assigned' => 0, 'under_review' => 0, 'accepted' => 0, 'rejected' => 0, 'revision_required' => 0],
                    'submissions' => [],
                ]);
                $templateMgr->display('guest/admin/dashboard.tpl');
            } catch (\Throwable $e2) {
                // If we can't even show error page, output directly
                echo '<h1>Error in GuestAdminHandler</h1>';
                echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
                echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
        }
    }

    /**
     * View a specific submission
     */
    public function view($args, $request)
    {
        $submissionId = isset($args[0]) ? (int)$args[0] : 0;
        
        if (!$submissionId) {
            $request->redirect(null, 'guest', 'admin');
        }

        $submission = GuestSubmission::getById($submissionId);
        
        if (!$submission) {
            $request->redirect(null, 'guest', 'admin');
        }

        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);
        
        // Add custom styles for guest admin view
        $customViewStyles = '
.submission-view {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.back-link {
    display: inline-flex;
    align-items: center;
    color: #2563eb;
    text-decoration: none;
    margin-bottom: 20px;
    font-size: 0.875rem;
}

.back-link:hover {
    text-decoration: underline;
}

.submission-header {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.submission-header h2 {
    margin: 0 0 10px 0;
    color: #1f2937;
}

.submission-meta {
    display: flex;
    gap: 30px;
    color: #6b7280;
    font-size: 0.875rem;
}

.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.content-section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
}

.info-row {
    margin-bottom: 15px;
}

.info-label {
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 5px;
}

.info-value {
    color: #1f2937;
}

.abstract-box {
    background: #f9fafb;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #2563eb;
    line-height: 1.6;
}

.author-card {
    background: #f9fafb;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.author-card h4 {
    margin: 0 0 10px 0;
    color: #1f2937;
}

.author-card p {
    margin: 5px 0;
    font-size: 0.875rem;
    color: #4b5563;
}

.submission-view .status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
}

.submission-view .status-pending { background: #fef3c7; color: #92400e; }
.submission-view .status-assigned { background: #dbeafe; color: #1e40af; }
.submission-view .status-under_review { background: #e0e7ff; color: #3730a3; }
.submission-view .status-accepted { background: #d1fae5; color: #065f46; }
.submission-view .status-rejected { background: #fee2e2; color: #991b1b; }
.submission-view .status-revision_required { background: #fed7aa; color: #9a3412; }

.action-panel {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 5px;
}

.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    font-family: inherit;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-success {
    background: #059669;
    color: white;
}

.btn-success:hover {
    background: #047857;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-block {
    width: 100%;
    margin-bottom: 10px;
}

.download-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.2s;
}

.download-btn:hover {
    background: #1d4ed8;
}

.activity-log {
    max-height: 400px;
    overflow-y: auto;
}

.log-entry {
    padding: 10px;
    border-left: 3px solid #e5e7eb;
    margin-bottom: 10px;
    font-size: 0.875rem;
}

.log-entry.important {
    border-left-color: #2563eb;
    background: #f0f9ff;
}

.log-date {
    color: #6b7280;
    font-size: 0.75rem;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #059669;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #dc2626;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}
';
        
        $templateMgr->addStyleSheet(
            'guestAdminView',
            $customViewStyles,
            [
                'priority' => \PKP\template\PKPTemplateManager::STYLE_SEQUENCE_LAST,
                'contexts' => ['backend'],
                'inline' => true,
            ]
        );

        // Get list of editors for assignment
        $userDao = \DAORegistry::getDAO('UserDAO');
        $editors = $userDao->getUsersByRoleId(Role::ROLE_ID_MANAGER);

        // Convert Laravel collections to arrays for Smarty templates
        if ($submission) {
            $submission->authors = $submission->authors ? $submission->authors->toArray() : [];
            $submission->logs = $submission->logs ? $submission->logs->toArray() : [];
        }
        
        // Convert editors collection to array
        $editorsArray = [];
        if ($editors) {
            foreach ($editors as $editor) {
                $editorsArray[] = $editor;
            }
        }

        $templateMgr->assign([
            'pageTitle' => 'View Submission',
            'submission' => $submission,
            'editors' => $editorsArray,
        ]);

        $templateMgr->display('guest/admin/view.tpl');
    }

    /**
     * Assign submission to an editor (AJAX)
     */
    public function assign($args, $request)
    {
        header('Content-Type: application/json');
        
        if ($request->getRequestMethod() !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $submissionId = $request->getUserVar('submission_id');
        $editorId = $request->getUserVar('editor_id');
        $notes = $request->getUserVar('notes');

        if (!$submissionId || !$editorId) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $user = $request->getUser();
        $userId = $user ? $user->getId() : null;

        try {
            // Assign submission
            GuestSubmission::assign($submissionId, $editorId, $userId);
            
            // Add notes if provided
            if ($notes) {
                GuestSubmission::updateNotes($submissionId, $notes, $userId);
            }

            // Send notification email to editor
            $this->sendAssignmentEmail($submissionId, $editorId);

            echo json_encode([
                'success' => true,
                'message' => 'Submission assigned successfully'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Make a decision on submission (AJAX)
     */
    public function decide($args, $request)
    {
        header('Content-Type: application/json');
        
        if ($request->getRequestMethod() !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $submissionId = $request->getUserVar('submission_id');
        $decision = $request->getUserVar('decision');
        $notes = $request->getUserVar('notes');

        if (!$submissionId || !$decision) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $user = $request->getUser();
        $userId = $user ? $user->getId() : null;

        try {
            // Make decision
            GuestSubmission::makeDecision($submissionId, $decision, $notes, $userId);

            // Send decision email to author
            $this->sendDecisionEmail($submissionId, $decision, $notes);

            echo json_encode([
                'success' => true,
                'message' => 'Decision recorded successfully'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Update submission status (AJAX)
     */
    public function updateStatus($args, $request)
    {
        header('Content-Type: application/json');
        
        if ($request->getRequestMethod() !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $submissionId = $request->getUserVar('submission_id');
        $status = $request->getUserVar('status');

        if (!$submissionId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $user = $request->getUser();
        $userId = $user ? $user->getId() : null;

        try {
            GuestSubmission::updateStatus($submissionId, $status, $userId);

            echo json_encode([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Update admin notes (AJAX)
     */
    public function updateNotes($args, $request)
    {
        header('Content-Type: application/json');
        
        if ($request->getRequestMethod() !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $submissionId = $request->getUserVar('submission_id');
        $notes = $request->getUserVar('notes');

        if (!$submissionId) {
            echo json_encode(['success' => false, 'message' => 'Missing submission ID']);
            exit;
        }

        $user = $request->getUser();
        $userId = $user ? $user->getId() : null;

        try {
            GuestSubmission::updateNotes($submissionId, $notes, $userId);

            echo json_encode([
                'success' => true,
                'message' => 'Notes updated successfully'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Download manuscript file
     */
    public function download($args, $request)
    {
        $submissionId = isset($args[0]) ? (int)$args[0] : 0;
        
        if (!$submissionId) {
            $request->redirect(null, 'guest', 'admin');
        }

        $submission = GuestSubmission::getById($submissionId);
        
        if (!$submission || !$submission->manuscript_file_path) {
            $request->redirect(null, 'guest', 'admin');
        }

        $filePath = $submission->manuscript_file_path;
        
        if (!file_exists($filePath)) {
            echo 'File not found';
            exit;
        }

        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $submission->manuscript_file_name . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }

    /**
     * Delete a submission
     */
    public function delete($args, $request)
    {
        header('Content-Type: application/json');
        
        if ($request->getRequestMethod() !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $submissionId = $request->getUserVar('submission_id');

        if (!$submissionId) {
            echo json_encode(['success' => false, 'message' => 'Missing submission ID']);
            exit;
        }

        try {
            $submission = GuestSubmission::getById($submissionId);
            
            // Delete file if exists
            if ($submission && $submission->manuscript_file_path && file_exists($submission->manuscript_file_path)) {
                unlink($submission->manuscript_file_path);
            }

            GuestSubmission::delete($submissionId);

            echo json_encode([
                'success' => true,
                'message' => 'Submission deleted successfully'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Send assignment notification email to editor
     */
    private function sendAssignmentEmail($submissionId, $editorId)
    {
        $submission = GuestSubmission::getById($submissionId);
        $userDao = \DAORegistry::getDAO('UserDAO');
        $editor = $userDao->getById($editorId);

        if (!$editor || !$submission) {
            return false;
        }

        $mail = new PHPMailer(true);
        
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'fstu.uz';
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAuth = true;
            $mail->Username = 'stj_info@fstu.uz';
            $mail->Password = '7san3_9I3';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('stj_info@fstu.uz', 'International Technology Journal');
            $mail->addAddress($editor->getEmail());
            $mail->isHTML(true);
            $mail->Subject = 'New Submission Assigned: ' . $submission->manuscript_title;
            
            $mail->Body = $this->getAssignmentEmailBody($submission, $editor);
            
            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Assignment email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send decision notification email to author
     */
    private function sendDecisionEmail($submissionId, $decision, $notes)
    {
        $submission = GuestSubmission::getById($submissionId);
        
        if (!$submission || !$submission->authors) {
            return false;
        }

        $primaryAuthor = $submission->authors[0];
        
        $mail = new PHPMailer(true);
        
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'fstu.uz';
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAuth = true;
            $mail->Username = 'stj_info@fstu.uz';
            $mail->Password = '7san3_9I3';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('stj_info@fstu.uz', 'International Technology Journal');
            $mail->addAddress($primaryAuthor->email);
            $mail->isHTML(true);
            $mail->Subject = 'Decision on Your Submission: ' . $submission->manuscript_title;
            
            $mail->Body = $this->getDecisionEmailBody($submission, $decision, $notes, $primaryAuthor);
            
            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Decision email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get assignment email body
     */
    private function getAssignmentEmailBody($submission, $editor)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { background: white; padding: 20px; border: 1px solid #e5e7eb; }
                .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Submission Assigned</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($editor->getFullName()) . ",</p>
                    
                    <p>A new guest submission has been assigned to you for review:</p>
                    
                    <p><strong>Title:</strong> " . htmlspecialchars($submission->manuscript_title) . "</p>
                    <p><strong>Article Type:</strong> " . htmlspecialchars($submission->article_type) . "</p>
                    <p><strong>Submitted:</strong> " . date('F j, Y', strtotime($submission->date_submitted)) . "</p>
                    
                    <p><a href='https://stj.fstu.uz/itj/guest/admin/view/{$submission->submission_id}' class='button'>View Submission</a></p>
                    
                    <p>Please review this submission and make a decision at your earliest convenience.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get decision email body
     */
    private function getDecisionEmailBody($submission, $decision, $notes, $author)
    {
        $decisionText = [
            'accept' => 'Accepted',
            'reject' => 'Rejected',
            'revision_required' => 'Revision Required'
        ];

        $decisionLabel = $decisionText[$decision] ?? $decision;
        $color = $decision === 'accept' ? '#059669' : ($decision === 'reject' ? '#dc2626' : '#f59e0b');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: {$color}; color: white; padding: 20px; text-align: center; }
                .content { background: white; padding: 20px; border: 1px solid #e5e7eb; }
                .decision-box { background: #f9fafb; padding: 15px; border-left: 4px solid {$color}; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Decision on Your Submission</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($author->title . ' ' . $author->first_name . ' ' . $author->last_name) . ",</p>
                    
                    <p>We have made a decision on your submission:</p>
                    
                    <p><strong>Title:</strong> " . htmlspecialchars($submission->manuscript_title) . "</p>
                    
                    <div class='decision-box'>
                        <h3 style='margin-top: 0; color: {$color};'>Decision: {$decisionLabel}</h3>
                        " . ($notes ? "<p>" . nl2br(htmlspecialchars($notes)) . "</p>" : "") . "
                    </div>
                    
                    <p>If you have any questions, please contact us at stj_admin@fstu.uz.</p>
                    
                    <p>Best regards,<br>Editorial Team<br>International Technology Journal</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

