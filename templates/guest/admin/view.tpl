{**
 * templates/guest/admin/view.tpl
 *
 * View and manage a guest submission
 *}
{extends file="layouts/backend.tpl"}

{block name="page"}
	<h1 class="app__pageHeading">
		View Submission
	</h1>

<div class="submission-view">
    <a href="{url page="guest" op="admin"}" class="back-link">
        ← Back to Dashboard
    </a>

    <!-- Submission Header -->
    <div class="submission-header">
        <h2 style="margin: 0 0 10px 0; color: #1f2937;">{$submission->manuscript_title|escape}</h2>
        <div class="submission-meta">
            <span><strong>ID:</strong> #{$submission->submission_id}</span>
            <span><strong>Type:</strong> {$submission->article_type|escape}</span>
            <span><strong>Submitted:</strong> {$submission->date_submitted|date_format:"%B %d, %Y"}</span>
            <span>
                <strong>Status:</strong> 
                <span class="status-badge status-{$submission->status}">
                    {$submission->status|replace:'_':' '|ucwords}
                </span>
            </span>
        </div>
    </div>

    <div class="content-grid">
        <!-- Left Column: Submission Details -->
        <div>
            <!-- Abstract -->
            <div class="content-section">
                <h3 class="section-title">Abstract</h3>
                <div class="abstract-box">
                    {$submission->abstract|escape|nl2br}
                </div>
            </div>

            <!-- Keywords -->
            <div class="content-section">
                <h3 class="section-title">Keywords</h3>
                <p>{$submission->keywords|escape}</p>
            </div>

            <!-- Authors -->
            <div class="content-section">
                <h3 class="section-title">Authors</h3>
                {foreach from=$submission->authors item=author}
                <div class="author-card">
                    <h4>{$author->title|escape} {$author->first_name|escape} {$author->last_name|escape}</h4>
                    <p><strong>Authorship:</strong> {$author->authorship|escape}</p>
                    <p><strong>Email:</strong> {$author->email|escape}</p>
                    <p><strong>Affiliation:</strong> {$author->affiliation|escape}</p>
                    <p><strong>Address:</strong> {$author->address|escape}</p>
                </div>
                {/foreach}
            </div>

            <!-- Activity Log -->
            {if $submission->logs && count($submission->logs) > 0}
            <div class="content-section">
                <h3 class="section-title">Activity Log</h3>
                <div class="activity-log">
                    {foreach from=$submission->logs item=log}
                    <div class="log-entry {if $log->action == 'decision_made' || $log->action == 'assigned'}important{/if}">
                        <div><strong>{$log->action|replace:'_':' '|ucwords}</strong></div>
                        {if $log->message}
                        <div>{$log->message|escape}</div>
                        {/if}
                        <div class="log-date">{$log->date_logged|date_format:"%B %d, %Y at %I:%M %p"}</div>
                    </div>
                    {/foreach}
                </div>
            </div>
            {/if}
        </div>

        <!-- Right Column: Actions -->
        <div>
            <!-- Download File -->
            <div class="action-panel">
                <h3 class="section-title">Manuscript File</h3>
                <a href="{url page="guest" op="admin" path="download" path=$submission->submission_id}" class="download-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download {$submission->manuscript_file_name|escape}
                </a>
            </div>

            <!-- Assign to Editor -->
            {if $submission->status == 'pending' || $submission->status == 'assigned'}
            <div class="action-panel">
                <h3 class="section-title">Assign to Editor</h3>
                <form id="assignForm">
                    <input type="hidden" name="submission_id" value="{$submission->submission_id}">
                    <div class="form-group">
                        <label>Select Editor</label>
                        <select name="editor_id" required>
                            <option value="">-- Select Editor --</option>
                            {foreach from=$editors item=editor}
                            <option value="{$editor->getId()}" {if $submission->assigned_editor_id == $editor->getId()}selected{/if}>
                                {$editor->getFullName()|escape}
                            </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes (optional)</label>
                        <textarea name="notes" placeholder="Add notes for the editor..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Assign Submission</button>
                </form>
            </div>
            {/if}

            <!-- Make Decision -->
            <div class="action-panel">
                <h3 class="section-title">Make Decision</h3>
                <form id="decisionForm">
                    <input type="hidden" name="submission_id" value="{$submission->submission_id}">
                    <div class="form-group">
                        <label>Decision</label>
                        <select name="decision" required>
                            <option value="">-- Select Decision --</option>
                            <option value="accept">Accept</option>
                            <option value="reject">Reject</option>
                            <option value="revision_required">Revision Required</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes to Author</label>
                        <textarea name="notes" placeholder="Provide feedback to the author..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Submit Decision</button>
                </form>
            </div>

            <!-- Update Status -->
            <div class="action-panel">
                <h3 class="section-title">Update Status</h3>
                <form id="statusForm">
                    <input type="hidden" name="submission_id" value="{$submission->submission_id}">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="pending" {if $submission->status == 'pending'}selected{/if}>Pending</option>
                            <option value="assigned" {if $submission->status == 'assigned'}selected{/if}>Assigned</option>
                            <option value="under_review" {if $submission->status == 'under_review'}selected{/if}>Under Review</option>
                            <option value="accepted" {if $submission->status == 'accepted'}selected{/if}>Accepted</option>
                            <option value="rejected" {if $submission->status == 'rejected'}selected{/if}>Rejected</option>
                            <option value="revision_required" {if $submission->status == 'revision_required'}selected{/if}>Revision Required</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                </form>
            </div>

            <!-- Admin Notes -->
            <div class="action-panel">
                <h3 class="section-title">Admin Notes</h3>
                <form id="notesForm">
                    <input type="hidden" name="submission_id" value="{$submission->submission_id}">
                    <div class="form-group">
                        <textarea name="notes" placeholder="Internal notes (not visible to author)...">{$submission->admin_notes|escape}</textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-block">Save Notes</button>
                </form>
            </div>

            <!-- Delete Submission -->
            <div class="action-panel">
                <button type="button" class="btn btn-danger btn-block" onclick="deleteSubmission({$submission->submission_id})">
                    Delete Submission
                </button>
            </div>
        </div>
    </div>
</div>

<script>
{literal}
// Handle form submissions
document.getElementById('assignForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{/literal}{url page="guest" op="admin" path="assign"}{literal}', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Submission assigned successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
});

document.getElementById('decisionForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    if (!confirm('Are you sure you want to submit this decision? The author will be notified via email.')) {
        return;
    }
    
    try {
        const response = await fetch('{/literal}{url page="guest" op="admin" path="decide"}{literal}', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Decision submitted successfully! Author has been notified.');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
});

document.getElementById('statusForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{/literal}{url page="guest" op="admin" path="updateStatus"}{literal}', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
});

document.getElementById('notesForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{/literal}{url page="guest" op="admin" path="updateNotes"}{literal}', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Notes saved successfully!');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
});

async function deleteSubmission(submissionId) {
    if (!confirm('Are you sure you want to delete this submission? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('{/literal}{url page="guest" op="admin" path="delete"}{literal}', {
            method: 'POST',
            body: new URLSearchParams({ submission_id: submissionId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Submission deleted successfully!');
            window.location.href = '{/literal}{url page="guest" op="admin"}{literal}';
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
}
{/literal}
</script>

{/block}

