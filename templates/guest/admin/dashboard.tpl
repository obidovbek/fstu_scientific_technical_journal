{**
 * templates/guest/admin/dashboard.tpl
 *
 * Guest Submissions Admin Dashboard
 *}
{extends file="layouts/backend.tpl"}

{block name="page"}
	<h1 class="app__pageHeading">
		Guest Submissions Dashboard
	</h1>

<div class="guest-admin-dashboard">
    {if $error}
    <div style="background: #fee2e2; border: 2px solid #dc2626; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
        <h2 style="color: #991b1b; margin-top: 0;">Error</h2>
        <p style="color: #991b1b; font-weight: 600;">{$error|escape}</p>
        {if $errorDetails}
        <p style="color: #7f1d1d; margin-top: 10px; font-size: 0.875rem;">{$errorDetails|escape}</p>
        {/if}
    </div>
    {/if}

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{$stats.total}</div>
            <div class="stat-label">Total Submissions</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-number">{$stats.pending}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card assigned">
            <div class="stat-number">{$stats.assigned}</div>
            <div class="stat-label">Assigned</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{$stats.under_review}</div>
            <div class="stat-label">Under Review</div>
        </div>
        <div class="stat-card accepted">
            <div class="stat-number">{$stats.accepted}</div>
            <div class="stat-label">Accepted</div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-number">{$stats.rejected}</div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="submissions-table">
        <div class="table-header">
            <h2>All Submissions</h2>
            <div class="filter-buttons">
                <button class="filter-btn active" data-status="all">All</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="assigned">Assigned</button>
                <button class="filter-btn" data-status="under_review">Under Review</button>
                <button class="filter-btn" data-status="accepted">Accepted</button>
                <button class="filter-btn" data-status="rejected">Rejected</button>
            </div>
        </div>

        {if $submissions && count($submissions) > 0}
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Author(s)</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="submissions-tbody">
                {foreach from=$submissions item=submission}
                <tr data-status="{$submission->status}">
                    <td>#{$submission->submission_id}</td>
                    <td><strong>{$submission->manuscript_title|escape}</strong></td>
                    <td>{$submission->article_type|escape}</td>
                    <td>
                        {if $submission->authors && count($submission->authors) > 0}
                            {$submission->authors[0]->first_name|escape} {$submission->authors[0]->last_name|escape}
                            {if count($submission->authors) > 1}
                                <br><small>+{count($submission->authors) - 1} more</small>
                            {/if}
                        {/if}
                    </td>
                    <td>
                        <span class="status-badge status-{$submission->status}">
                            {$submission->status|replace:'_':' '|ucwords}
                        </span>
                    </td>
                    <td>{$submission->date_submitted|date_format:"%b %d, %Y"}</td>
                    <td>
                        <a href="{url page="guest" op="admin" path="view" path=$submission->submission_id}" class="action-btn">
                            View
                        </a>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        {else}
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3>No submissions yet</h3>
            <p>Guest submissions will appear here once authors submit their manuscripts.</p>
        </div>
        {/if}
    </div>
</div>

<script>
{literal}
// Filter submissions by status
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const status = this.dataset.status;
        
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filter table rows
        document.querySelectorAll('#submissions-tbody tr').forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
{/literal}
</script>

{/block}

