{**
 * templates/frontend/pages/guestSubmission.tpl
 *
 * Guest submission form integrated with OJS template system
 *}
{include file="frontend/components/header.tpl" pageTitle="Guest Submission Form"}

<style>
{literal}
/* Guest Submission Form Styles - Scoped to avoid conflicts */
.guest-submission-container * {
    box-sizing: border-box;
}

.guest-submission-container {
    --primary-color: #2563eb;
    --primary-hover: #1d4ed8;
    --success-color: #059669;
    --error-color: #dc2626;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --bg-gray: #f9fafb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.guest-submission-container {
    max-width: 900px;
    margin: -60px auto 40px auto;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

/* Header */
.guest-form-header {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    padding: 40px;
    text-align: center;
}

.guest-form-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.guest-form-header .subtitle {
    font-size: 1rem;
    opacity: 0.9;
}

/* Form Sections */
.guest-submission-form {
    padding: 10px;
}

.guest-form-section {
    margin-bottom: 40px;
    padding-bottom: 40px;
    border-bottom: 2px solid var(--border-color);
}

.guest-form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.guest-form-section h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
}

.guest-form-section h2::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 24px;
    background: var(--primary-color);
    margin-right: 12px;
    border-radius: 2px;
}

/* Author Blocks */
.guest-author-block {
    background: var(--bg-gray);
    padding: 24px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid var(--border-color);
}

.guest-author-block:first-child {
    background: white;
    border: none;
    padding: 0;
}

.guest-author-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.guest-author-header h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.guest-btn-remove-author {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: white;
    border: 1px solid var(--error-color);
    color: var(--error-color);
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.guest-btn-remove-author:hover {
    background: var(--error-color);
    color: white;
}

.guest-btn-remove-author svg {
    width: 16px;
    height: 16px;
}

/* Form Groups */
.guest-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.guest-form-row:last-child {
    margin-bottom: 0;
}

.guest-form-group {
    display: flex;
    flex-direction: column;
}

.guest-form-group.full-width {
    grid-column: 1 / -1;
}

.guest-form-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.guest-required {
    color: var(--error-color);
    font-weight: 600;
}

.guest-form-group input,
.guest-form-group select,
.guest-form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 0.9375rem;
    color: var(--text-primary);
    transition: all 0.2s;
    font-family: inherit;
}

.guest-form-group input:focus,
.guest-form-group select:focus,
.guest-form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.guest-form-group input::placeholder,
.guest-form-group textarea::placeholder {
    color: var(--text-secondary);
}

.guest-form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.guest-char-counter {
    margin-top: 8px;
    font-size: 0.875rem;
    color: var(--text-secondary);
    text-align: right;
}

.guest-char-counter span {
    font-weight: 600;
    color: var(--success-color);
}

.guest-help-text {
    margin-top: 6px;
    font-size: 0.8125rem;
    color: var(--text-secondary);
}

/* Add Author Button */
.guest-btn-add-author {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: var(--shadow-sm);
}

.guest-btn-add-author:hover {
    background: var(--primary-hover);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.guest-btn-add-author:active {
    transform: translateY(0);
}

/* File Upload Area */
.guest-upload-area {
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s;
    background: var(--bg-gray);
}

.guest-upload-area.drag-over {
    border-color: var(--primary-color);
    background: rgba(37, 99, 235, 0.05);
}

.guest-upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.guest-upload-icon {
    width: 64px;
    height: 64px;
    color: var(--text-secondary);
}

.guest-upload-text {
    font-size: 1rem;
    color: var(--text-primary);
    margin: 0;
}

.guest-btn-browse {
    padding: 10px 24px;
    background: white;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    border-radius: 6px;
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.guest-btn-browse:hover {
    background: var(--primary-color);
    color: white;
}

.guest-upload-constraints {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin: 0;
}

/* File Preview */
.guest-file-preview {
    display: flex;
    justify-content: center;
}

.guest-file-info {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 24px;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    max-width: 500px;
    width: 100%;
}

.guest-file-icon {
    width: 40px;
    height: 40px;
    color: var(--primary-color);
    flex-shrink: 0;
}

.guest-file-details {
    flex: 1;
    text-align: left;
}

.guest-file-name {
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--text-primary);
    margin: 0 0 4px 0;
    word-break: break-word;
}

.guest-file-size {
    font-size: 0.8125rem;
    color: var(--text-secondary);
    margin: 0;
}

.guest-btn-remove-file {
    padding: 8px;
    background: transparent;
    border: none;
    color: var(--error-color);
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.guest-btn-remove-file:hover {
    background: rgba(220, 38, 38, 0.1);
}

.guest-btn-remove-file svg {
    width: 20px;
    height: 20px;
}

/* Submit Button */
.guest-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 40px;
}

.guest-btn-test-email {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 32px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: var(--shadow-md);
    min-width: 140px;
}

.guest-btn-test-email:hover:not(:disabled) {
    background: var(--primary-hover);
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.guest-btn-test-email:active:not(:disabled) {
    transform: translateY(0);
}

.guest-btn-test-email:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.guest-btn-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 40px;
    background: #1f2937;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: var(--shadow-md);
    min-width: 160px;
}

.guest-btn-submit:hover:not(:disabled) {
    background: #111827;
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.guest-btn-submit:active:not(:disabled) {
    transform: translateY(0);
}

.guest-btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.guest-btn-loader {
    display: flex;
    align-items: center;
    gap: 8px;
}

.guest-spinner {
    width: 20px;
    height: 20px;
    animation: guest-spin 1s linear infinite;
}

@keyframes guest-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Modal */
.guest-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 20px;
}

.guest-modal-content {
    background: white;
    padding: 40px;
    border-radius: 12px;
    max-width: 500px;
    width: 100%;
    text-align: center;
    box-shadow: var(--shadow-lg);
    animation: guest-slideIn 0.3s ease-out;
}

@keyframes guest-slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.guest-success-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    color: var(--success-color);
}

.guest-success-icon svg {
    width: 100%;
    height: 100%;
}

.guest-error-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    color: var(--error-color);
}

.guest-error-icon svg {
    width: 100%;
    height: 100%;
}

.guest-modal-content h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
}

.guest-modal-content h2::before {
    display: none;
}

.guest-modal-content p {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 12px;
    line-height: 1.6;
}

.guest-confirmation-text {
    font-size: 0.9375rem;
    color: var(--text-secondary);
    margin-bottom: 24px;
}

.guest-btn-close-modal {
    padding: 12px 32px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 12px;
}

.guest-btn-close-modal:hover {
    background: var(--primary-hover);
}
@media (max-width: 996px) {
    .guest-submission-container {
        margin: 0px auto 40px auto;
        border-radius: 8px;
    }
}
/* Responsive Design */
@media (max-width: 768px) {
    .guest-submission-container {
        margin: 0px auto 40px auto;
        border-radius: 8px;
    }

    .guest-form-header {
        padding: 30px 20px;
    }

    .guest-form-header h1 {
        font-size: 1.5rem;
    }

    .guest-form-header .subtitle {
        font-size: 0.875rem;
    }

    .guest-submission-form {
        padding: 24px 20px;
    }

    .guest-form-section {
        margin-bottom: 30px;
        padding-bottom: 30px;
    }

    .guest-form-section h2 {
        font-size: 1.25rem;
        margin-bottom: 20px;
    }

    .guest-form-row {
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .guest-author-block {
        padding: 20px 16px;
    }

    .guest-author-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .guest-upload-area {
        padding: 30px 20px;
    }

    .guest-upload-icon {
        width: 48px;
        height: 48px;
    }

    .guest-upload-text {
        font-size: 0.9375rem;
    }

    .guest-form-actions {
        justify-content: stretch;
        flex-direction: column;
    }

    .guest-btn-test-email,
    .guest-btn-submit {
        width: 100%;
    }

    .guest-modal-content {
        padding: 30px 20px;
    }

    .guest-success-icon,
    .guest-error-icon {
        width: 64px;
        height: 64px;
        margin-bottom: 20px;
    }

    .guest-modal-content h2 {
        font-size: 1.5rem;
    }

    .guest-modal-content p {
        font-size: 0.9375rem;
    }
}

@media (max-width: 480px) {
    .guest-form-header h1 {
        font-size: 1.25rem;
    }

    .guest-form-section h2 {
        font-size: 1.125rem;
    }

    .guest-btn-add-author {
        width: 100%;
        justify-content: center;
    }

    .guest-btn-remove-author {
        width: 100%;
        justify-content: center;
    }
}
{/literal}
</style>

<div class="page page_guest_submission">
    <div class="guest-submission-container">

        <form id="guestSubmissionForm" class="guest-submission-form" action="{url page="guest" op="submit"}" method="post" enctype="multipart/form-data">
            <!-- Author Info Section -->
            <section class="guest-form-section">
                <h2>Author Information</h2>
                <div id="authorsContainer">
                    <div class="guest-author-block" data-author-index="0">
                        <div class="guest-form-row">
                            <div class="guest-form-group">
                                <label for="author_title_0">Title <span class="guest-required">*</span></label>
                                <select id="author_title_0" name="authors[0][title]" required>
                                    <option value="">Your title</option>
                                    <option value="Dr.">Dr.</option>
                                    <option value="Prof.">Prof.</option>
                                    <option value="Assoc. Prof.">Assoc. Prof.</option>
                                    <option value="Asst. Prof.">Asst. Prof.</option>
                                    <option value="Mr.">Mr.</option>
                                    <option value="Ms.">Ms.</option>
                                    <option value="Mrs.">Mrs.</option>
                                </select>
                            </div>
                            <div class="guest-form-group">
                                <label for="author_name_0">Name <span class="guest-required">*</span></label>
                                <input type="text" id="author_name_0" name="authors[0][name]" placeholder="First name" required>
                            </div>
                        </div>

                        <div class="guest-form-row">
                            <div class="guest-form-group">
                                <label for="author_surname_0">Surname <span class="guest-required">*</span></label>
                                <input type="text" id="author_surname_0" name="authors[0][surname]" placeholder="Last name" required>
                            </div>
                            <div class="guest-form-group">
                                <label for="author_authorship_0">Authorship <span class="guest-required">*</span></label>
                                <select id="author_authorship_0" name="authors[0][authorship]" required>
                                    <option value="">Authorship</option>
                                    <option value="First Author">First Author</option>
                                    <option value="Co-Author">Co-Author</option>
                                    <option value="Corresponding Author">Corresponding Author</option>
                                </select>
                            </div>
                        </div>

                        <div class="guest-form-row">
                            <div class="guest-form-group">
                                <label for="author_email_0">Email <span class="guest-required">*</span></label>
                                <input type="email" id="author_email_0" name="authors[0][email]" placeholder="email@example.com" required>
                            </div>
                            <div class="guest-form-group">
                                <label for="author_address_0">Address <span class="guest-required">*</span></label>
                                <input type="text" id="author_address_0" name="authors[0][address]" placeholder="Full address" required>
                            </div>
                        </div>

                        <div class="guest-form-row">
                            <div class="guest-form-group full-width">
                                <label for="author_affiliation_0">Affiliation <span class="guest-required">*</span></label>
                                <input type="text" id="author_affiliation_0" name="authors[0][affiliation]" placeholder="University/Institution name" required>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="addAuthorBtn" class="guest-btn-add-author">+ Add Author</button>
            </section>

            <!-- Submission Details Section -->
            <section class="guest-form-section">
                <h2>Submission Details</h2>
                
                <div class="guest-form-group">
                    <label for="article_type">Article Type <span class="guest-required">*</span></label>
                    <select id="article_type" name="article_type" required>
                        <option value="">Select article type</option>
                        <option value="Original article">Original article</option>
                        <option value="Review article">Review article</option>
                        <option value="Case study">Case study</option>
                        <option value="Short communication">Short communication</option>
                        <option value="Technical note">Technical note</option>
                    </select>
                </div>

                <div class="guest-form-group">
                    <label for="manuscript_title">Manuscript Title <span class="guest-required">*</span></label>
                    <input type="text" id="manuscript_title" name="manuscript_title" placeholder="Manuscript title" required>
                </div>

                <div class="guest-form-group">
                    <label for="abstract">Abstract <span class="guest-required">*</span></label>
                    <textarea id="abstract" name="abstract" rows="8" placeholder="Abstract (max. 350 words)" required></textarea>
                    <div class="guest-char-counter">
                        <span id="wordCount">0</span> / 350 words
                    </div>
                </div>
            </section>

            <!-- File Upload Section -->
            <section class="guest-form-section">
                <h2>Manuscript File</h2>
                
                <div class="guest-upload-area" id="uploadArea">
                    <div class="guest-upload-content">
                        <svg class="guest-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="guest-upload-text">Drag & Drop your files here or</p>
                        <button type="button" class="guest-btn-browse" id="browseBtn">Browse files</button>
                        <input type="file" id="manuscript_file" name="manuscript_file" accept=".doc,.docx" hidden required>
                        <p class="guest-upload-constraints">Supports doc, docx. Max. 17 MB</p>
                    </div>
                    <div class="guest-file-preview" id="filePreview" style="display: none;">
                        <div class="guest-file-info">
                            <svg class="guest-file-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="guest-file-details">
                                <p class="guest-file-name" id="fileName"></p>
                                <p class="guest-file-size" id="fileSize"></p>
                            </div>
                            <button type="button" class="guest-btn-remove-file" id="removeFileBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Keywords Section -->
            <section class="guest-form-section">
                <h2>Keywords</h2>
                <div class="guest-form-group">
                    <label for="keywords">Keywords <span class="guest-required">*</span></label>
                    <input type="text" id="keywords" name="keywords" placeholder='Keywords (Use ";" to separate, max. 4-6 words)' required>
                    <small class="guest-help-text">Example: machine learning; artificial intelligence; neural networks</small>
                </div>
            </section>

            <!-- Submit Button -->
            <div class="guest-form-actions">
                <button type="button" class="guest-btn-test-email" id="testEmailBtn">
                    <span class="btn-text">📧 Test Email</span>
                    <span class="guest-btn-loader" style="display: none;">
                        <svg class="guest-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Testing...
                    </span>
                </button>
                <button type="submit" class="guest-btn-submit" id="submitBtn">
                    <span class="btn-text">Submit</span>
                    <span class="guest-btn-loader" style="display: none;">
                        <svg class="guest-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    </span>
                </button>
            </div>
        </form>

        <!-- Success Modal -->
        <div id="successModal" class="guest-modal" style="display: none;">
            <div class="guest-modal-content">
                <div class="guest-success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2>Submission Successful!</h2>
                <p>Thank you for your submission. Your manuscript has been received and will be reviewed by our editorial team.</p>
                <p class="guest-confirmation-text">A confirmation email has been sent to your registered email address.</p>
                <button type="button" class="guest-btn-close-modal" id="closeModalBtn">Close</button>
            </div>
        </div>

        <!-- Error Modal -->
        <div id="errorModal" class="guest-modal" style="display: none;">
            <div class="guest-modal-content error">
                <div class="guest-error-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2>Submission Failed</h2>
                <p id="errorMessage">An error occurred while submitting your manuscript. Please try again.</p>
                <button type="button" class="guest-btn-close-modal" id="closeErrorModalBtn">Close</button>
            </div>
        </div>
    </div>
</div><!-- .page -->

<script>
{literal}
// Guest Submission Form JavaScript - Scoped to avoid conflicts
(function() {
    'use strict';

    let authorCount = 1;
    const MAX_FILE_SIZE = 17 * 1024 * 1024; // 17 MB in bytes
    const ALLOWED_EXTENSIONS = ['doc', 'docx'];
    const MAX_WORDS = 350;

    // DOM Elements
    const form = document.getElementById('guestSubmissionForm');
    const authorsContainer = document.getElementById('authorsContainer');
    const addAuthorBtn = document.getElementById('addAuthorBtn');
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('manuscript_file');
    const browseBtn = document.getElementById('browseBtn');
    const filePreview = document.getElementById('filePreview');
    const removeFileBtn = document.getElementById('removeFileBtn');
    const abstractTextarea = document.getElementById('abstract');
    const wordCountSpan = document.getElementById('wordCount');
    const submitBtn = document.getElementById('submitBtn');
    const testEmailBtn = document.getElementById('testEmailBtn');
    const successModal = document.getElementById('successModal');
    const errorModal = document.getElementById('errorModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const closeErrorModalBtn = document.getElementById('closeErrorModalBtn');

    // Initialize
    init();

    function init() {
        setupEventListeners();
    }

    function setupEventListeners() {
        // Add Author Button
        addAuthorBtn.addEventListener('click', addAuthorBlock);

        // File Upload
        browseBtn.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelect);
        removeFileBtn.addEventListener('click', removeFile);

        // Drag and Drop
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('dragleave', handleDragLeave);
        uploadArea.addEventListener('drop', handleDrop);

        // Abstract Word Counter
        abstractTextarea.addEventListener('input', updateWordCount);

        // Form Submission
        form.addEventListener('submit', handleSubmit);

        // Test Email Button
        testEmailBtn.addEventListener('click', handleTestEmail);

        // Modal Close
        closeModalBtn.addEventListener('click', closeSuccessModal);
        closeErrorModalBtn.addEventListener('click', closeErrorModal);
    }

    // Add Author Block
    function addAuthorBlock() {
        const authorBlock = document.createElement('div');
        authorBlock.className = 'guest-author-block';
        authorBlock.setAttribute('data-author-index', authorCount);

        authorBlock.innerHTML = `
            <div class="guest-author-header">
                <h3>Author ${authorCount + 1}</h3>
                <button type="button" class="guest-btn-remove-author" onclick="window.guestRemoveAuthorBlock(${authorCount})">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                </button>
            </div>
            <div class="guest-form-row">
                <div class="guest-form-group">
                    <label for="author_title_${authorCount}">Title <span class="guest-required">*</span></label>
                    <select id="author_title_${authorCount}" name="authors[${authorCount}][title]" required>
                        <option value="">Your title</option>
                        <option value="Dr.">Dr.</option>
                        <option value="Prof.">Prof.</option>
                        <option value="Assoc. Prof.">Assoc. Prof.</option>
                        <option value="Asst. Prof.">Asst. Prof.</option>
                        <option value="Mr.">Mr.</option>
                        <option value="Ms.">Ms.</option>
                        <option value="Mrs.">Mrs.</option>
                    </select>
                </div>
                <div class="guest-form-group">
                    <label for="author_name_${authorCount}">Name <span class="guest-required">*</span></label>
                    <input type="text" id="author_name_${authorCount}" name="authors[${authorCount}][name]" placeholder="First name" required>
                </div>
            </div>
            <div class="guest-form-row">
                <div class="guest-form-group">
                    <label for="author_surname_${authorCount}">Surname <span class="guest-required">*</span></label>
                    <input type="text" id="author_surname_${authorCount}" name="authors[${authorCount}][surname]" placeholder="Last name" required>
                </div>
                <div class="guest-form-group">
                    <label for="author_authorship_${authorCount}">Authorship <span class="guest-required">*</span></label>
                    <select id="author_authorship_${authorCount}" name="authors[${authorCount}][authorship]" required>
                        <option value="">Authorship</option>
                        <option value="First Author">First Author</option>
                        <option value="Co-Author">Co-Author</option>
                        <option value="Corresponding Author">Corresponding Author</option>
                    </select>
                </div>
            </div>
            <div class="guest-form-row">
                <div class="guest-form-group">
                    <label for="author_email_${authorCount}">Email <span class="guest-required">*</span></label>
                    <input type="email" id="author_email_${authorCount}" name="authors[${authorCount}][email]" placeholder="email@example.com" required>
                </div>
                <div class="guest-form-group">
                    <label for="author_address_${authorCount}">Address <span class="guest-required">*</span></label>
                    <input type="text" id="author_address_${authorCount}" name="authors[${authorCount}][address]" placeholder="Full address" required>
                </div>
            </div>
            <div class="guest-form-row">
                <div class="guest-form-group full-width">
                    <label for="author_affiliation_${authorCount}">Affiliation <span class="guest-required">*</span></label>
                    <input type="text" id="author_affiliation_${authorCount}" name="authors[${authorCount}][affiliation]" placeholder="University/Institution name" required>
                </div>
            </div>
        `;

        authorsContainer.appendChild(authorBlock);
        authorCount++;

        // Smooth scroll to new author block
        setTimeout(() => {
            authorBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }

    // Remove Author Block (Global function for inline onclick)
    window.guestRemoveAuthorBlock = function(index) {
        const authorBlock = document.querySelector(`[data-author-index="${index}"]`);
        if (authorBlock && authorCount > 1) {
            authorBlock.remove();
            authorCount--;
            updateAuthorNumbers();
        }
    };

    function updateAuthorNumbers() {
        const authorBlocks = document.querySelectorAll('.guest-author-block');
        authorBlocks.forEach((block, index) => {
            const header = block.querySelector('.guest-author-header h3');
            if (header && index > 0) {
                header.textContent = `Author ${index + 1}`;
            }
        });
    }

    // File Upload Handlers
    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.add('drag-over');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('drag-over');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('drag-over');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    }

    function handleFileSelect(e) {
        const files = e.target.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    }

    function handleFile(file) {
        // Validate file extension
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        
        if (!ALLOWED_EXTENSIONS.includes(fileExtension)) {
            showError('Invalid file type. Please upload a .doc or .docx file.');
            fileInput.value = '';
            return;
        }

        // Validate file size
        if (file.size > MAX_FILE_SIZE) {
            showError('File size exceeds 17 MB. Please upload a smaller file.');
            fileInput.value = '';
            return;
        }

        // Display file preview
        displayFilePreview(file);
    }

    function displayFilePreview(file) {
        const fileName = file.name;
        const fileSize = formatFileSize(file.size);

        document.getElementById('fileName').textContent = fileName;
        document.getElementById('fileSize').textContent = fileSize;

        uploadArea.querySelector('.guest-upload-content').style.display = 'none';
        filePreview.style.display = 'block';
    }

    function removeFile() {
        fileInput.value = '';
        uploadArea.querySelector('.guest-upload-content').style.display = 'flex';
        filePreview.style.display = 'none';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Word Counter
    function updateWordCount() {
        const text = abstractTextarea.value.trim();
        const words = text ? text.split(/\s+/).filter(word => word.length > 0).length : 0;
        wordCountSpan.textContent = words;

        if (words > MAX_WORDS) {
            wordCountSpan.style.color = '#dc2626';
        } else {
            wordCountSpan.style.color = '#059669';
        }
    }

    // Form Submission
    async function handleSubmit(e) {
        e.preventDefault();

        // Validate abstract word count
        const text = abstractTextarea.value.trim();
        const words = text ? text.split(/\s+/).filter(word => word.length > 0).length : 0;
        if (words > MAX_WORDS) {
            showError(`Abstract exceeds ${MAX_WORDS} words. Please shorten it to ${MAX_WORDS} words or less.`);
            abstractTextarea.focus();
            return;
        }

        // Validate keywords
        const keywords = document.getElementById('keywords').value.trim();
        const keywordArray = keywords.split(';').map(k => k.trim()).filter(k => k.length > 0);
        if (keywordArray.length < 4 || keywordArray.length > 6) {
            showError('Please provide 4-6 keywords separated by semicolons (;).');
            document.getElementById('keywords').focus();
            return;
        }

        // Validate file upload
        if (!fileInput.files || fileInput.files.length === 0) {
            showError('Please upload a manuscript file.');
            return;
        }

        // Show loading state
        setLoadingState(true);

        // Prepare form data
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showSuccessModal();
                form.reset();
                removeFile();
                wordCountSpan.textContent = '0';
                
                // Remove additional author blocks
                const authorBlocks = document.querySelectorAll('.guest-author-block');
                authorBlocks.forEach((block, index) => {
                    if (index > 0) {
                        block.remove();
                    }
                });
                authorCount = 1;
            } else {
                showError(result.message || 'An error occurred while submitting your manuscript.');
            }
        } catch (error) {
            console.error('Submission error:', error);
            showError('Network error. Please check your connection and try again.');
        } finally {
            setLoadingState(false);
        }
    }

    function setLoadingState(loading) {
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoader = submitBtn.querySelector('.guest-btn-loader');

        if (loading) {
            btnText.style.display = 'none';
            btnLoader.style.display = 'flex';
            submitBtn.disabled = true;
        } else {
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    function setTestEmailLoadingState(loading) {
        const btnText = testEmailBtn.querySelector('.btn-text');
        const btnLoader = testEmailBtn.querySelector('.guest-btn-loader');

        if (loading) {
            btnText.style.display = 'none';
            btnLoader.style.display = 'flex';
            testEmailBtn.disabled = true;
        } else {
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            testEmailBtn.disabled = false;
        }
    }

    // Test Email Handler
    async function handleTestEmail() {
        // Get email from form or use default
        const authorEmail = document.getElementById('author_email_0')?.value || 'obidov.bekzod94@gmail.com';
        
        setTestEmailLoadingState(true);

        try {
{/literal}
            const handlerUrl = '{url page="guest" op="test-email"}';
{literal}
            const response = await fetch(handlerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    to: authorEmail
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccessModal();
                // Update success message
                const successMessage = successModal.querySelector('p');
                if (successMessage) {
                    successMessage.textContent = result.message || 'Test email sent successfully! Check your inbox.';
                }
            } else {
                showError(result.message || 'Failed to send test email. Please try again.');
            }
        } catch (error) {
            console.error('Test email error:', error);
            showError('Network error. Please check your connection and try again.');
        } finally {
            setTestEmailLoadingState(false);
        }
    }

    // Modal Functions
    function showSuccessModal() {
        successModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeSuccessModal() {
        successModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        errorModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeErrorModal() {
        errorModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

})();
{/literal}
</script>

{include file="frontend/components/footer.tpl"}

