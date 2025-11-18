<?php
/**
 * Guest Submission Form Handler
 * Processes guest manuscript submissions and sends email notifications
 * to the admin/editor for manual entry into OJS
 */

// Load OJS configuration and bootstrap (PHPMailer is already included)
if (!defined('INDEX_FILE_LOCATION')) {
    require_once(__DIR__ . '/../../config.inc.php');
    require_once(__DIR__ . '/../../lib/pkp/includes/bootstrap.php');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use APP\guest\GuestSubmission;

// Set error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON response header
header('Content-Type: application/json');

// Configuration
define('MAX_FILE_SIZE', 17 * 1024 * 1024); // 17 MB
define('ALLOWED_EXTENSIONS', ['doc', 'docx']);
define('ADMIN_EMAIL', 'stj_admin@fstu.uz'); // Change this to your admin email
define('SITE_NAME', 'International Technology Journal');
define('SITE_URL', 'https://stj.fstu.uz/itj');

/**
 * Send email using PHPMailer with SMTP
 * Uses the same configuration as testmail/index.php (which works)
 */
function sendEmailWithSMTP($to, $subject, $htmlBody, $textBody = null, $attachmentPath = null, $attachmentName = null, $replyTo = null) {
    // SMTP configuration - matching testmail/index.php exactly (local, not global)
    $smtpConfig = [
        'host' => 'fstu.uz',
        'port' => 465,
        'username' => 'stj_info@fstu.uz',
        'password' => '7san3_9I3'
    ];
    
    // Email options - hardcoded to avoid global variable issues (like testmail/index.php)
    $fromEmail = 'stj_info@fstu.uz';
    
    $mail = null;
    try {
        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings - matching testmail/index.php exactly
        $mail->isSMTP();
        $mail->Host = $smtpConfig['host'];
        $mail->Port = $smtpConfig['port'];
        
        // For port 465, use implicit SSL (matches Node.js secure: true)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAutoTLS = false; // Disable auto TLS for implicit SSL
        
        // Authentication
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];
        
        // Timeout - matching testmail/index.php (10 seconds)
        $mail->Timeout = 10;
        
        // SSL options - matching testmail/index.php tls: { rejectUnauthorized: false }
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Set email details - matching testmail/index.php
        $mail->setFrom($fromEmail, SITE_NAME);
        
        // Reply-To if provided
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }
        
        // Recipient
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);
        
        // Attachment if provided
        if ($attachmentPath && $attachmentName) {
            if (file_exists($attachmentPath)) {
                // Check file size (some servers have limits)
                $fileSize = filesize($attachmentPath);
                if ($fileSize > 0 && $fileSize < 20 * 1024 * 1024) { // Max 20MB
                    $mail->addAttachment($attachmentPath, $attachmentName);
                } else {
                    error_log('Attachment file too large or empty: ' . $attachmentPath . ' (size: ' . $fileSize . ')');
                }
            } else {
                error_log('Attachment file not found: ' . $attachmentPath);
            }
        }
        
        // Send email (PHPMailer handles connection internally)
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        $errorInfo = isset($mail) ? $mail->ErrorInfo : 'PHPMailer not initialized';
        
        error_log('PHPMailer Error: ' . $errorMsg);
        error_log('PHPMailer ErrorInfo: ' . $errorInfo);
        
        // Also log to a file for debugging
        $logFile = __DIR__ . '/../../logs/email-errors.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logFile, date('Y-m-d H:i:s') . " - To: $to - Error: " . $errorMsg . " | ErrorInfo: " . $errorInfo . "\n", FILE_APPEND);
        
        return ['success' => false, 'message' => $errorMsg, 'errorInfo' => $errorInfo];
    }
}

// Response function
function sendResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

try {
    // Validate and sanitize input data
    $authors = isset($_POST['authors']) ? $_POST['authors'] : [];
    $articleType = isset($_POST['article_type']) ? trim($_POST['article_type']) : '';
    $manuscriptTitle = isset($_POST['manuscript_title']) ? trim($_POST['manuscript_title']) : '';
    $abstract = isset($_POST['abstract']) ? trim($_POST['abstract']) : '';
    $keywords = isset($_POST['keywords']) ? trim($_POST['keywords']) : '';

    // Validation
    $errors = [];

    // Validate authors
    if (empty($authors) || !is_array($authors)) {
        $errors[] = 'At least one author is required';
    } else {
        foreach ($authors as $index => $author) {
            if (empty($author['title']) || empty($author['name']) || empty($author['surname']) || 
                empty($author['authorship']) || empty($author['email']) || 
                empty($author['address']) || empty($author['affiliation'])) {
                $errors[] = "All fields are required for author " . ($index + 1);
            }
            
            // Validate email format
            if (!empty($author['email']) && !filter_var($author['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format for author " . ($index + 1);
            }
        }
    }

    // Validate submission details
    if (empty($articleType)) {
        $errors[] = 'Article type is required';
    }
    if (empty($manuscriptTitle)) {
        $errors[] = 'Manuscript title is required';
    }
    if (empty($abstract)) {
        $errors[] = 'Abstract is required';
    } else {
        // Validate word count
        $wordCount = str_word_count($abstract);
        if ($wordCount > 350) {
            $errors[] = 'Abstract exceeds 350 words';
        }
    }

    // Validate keywords
    if (empty($keywords)) {
        $errors[] = 'Keywords are required';
    } else {
        $keywordArray = array_filter(array_map('trim', explode(';', $keywords)));
        if (count($keywordArray) < 4 || count($keywordArray) > 6) {
            $errors[] = 'Please provide 4-6 keywords';
        }
    }

    // Validate file upload
    if (!isset($_FILES['manuscript_file']) || $_FILES['manuscript_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Manuscript file is required';
    } elseif ($_FILES['manuscript_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error occurred';
    } else {
        $file = $_FILES['manuscript_file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmpName = $file['tmp_name'];
        
        // Validate file extension
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
            $errors[] = 'Only .doc and .docx files are allowed';
        }
        
        // Validate file size
        if ($fileSize > MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds 17 MB limit';
        }
        
        // Additional security check - verify MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmpName);
        finfo_close($finfo);
        
        $allowedMimeTypes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            $errors[] = 'Invalid file type';
        }
    }

    // Return errors if any
    if (!empty($errors)) {
        sendResponse(false, implode('; ', $errors));
    }

    // Save uploaded file to permanent location
    // Try multiple possible locations
    $possibleDirs = [];
    
    // 1. Try OJS config (if available)
    try {
        $baseFilesDir = \PKP\config\Config::getVar('files', 'files_dir');
        if (!empty($baseFilesDir)) {
            if (!preg_match('/^[\/\\\\]/', $baseFilesDir)) {
                // Relative path - resolve from OJS root
                $ojsRoot = dirname(dirname(__DIR__));
                $possibleDirs[] = $ojsRoot . '/' . $baseFilesDir . '/guest_submissions/';
            } else {
                // Absolute path
                $possibleDirs[] = rtrim($baseFilesDir, '/\\') . '/guest_submissions/';
            }
        }
    } catch (\Exception $e) {
        error_log('Could not get files_dir from config: ' . $e->getMessage());
    }
    
    // 2. Try common locations
    $ojsRoot = dirname(dirname(__DIR__));
    $possibleDirs[] = $ojsRoot . '/files/guest_submissions/';
    $possibleDirs[] = $ojsRoot . '/ojs_files/guest_submissions/';
    $possibleDirs[] = '/home/web/stj/frontend2/files/guest_submissions/';
    $possibleDirs[] = '/home/web/stj/files/guest_submissions/';
    $possibleDirs[] = __DIR__ . '/../../files/guest_submissions/';
    
    // Find first writable directory or create it
    $uploadDir = null;
    foreach ($possibleDirs as $dir) {
        if (is_dir($dir) && is_writable($dir)) {
            $uploadDir = $dir;
            error_log('Using existing writable directory: ' . $uploadDir);
            break;
        } elseif (is_dir(dirname($dir)) && is_writable(dirname($dir))) {
            // Parent exists and is writable, try to create subdirectory
            if (@mkdir($dir, 0755, true)) {
                $uploadDir = $dir;
                error_log('Created and using directory: ' . $uploadDir);
                break;
            }
        }
    }
    
    // If no directory found, try to create the first one
    if (!$uploadDir) {
        $uploadDir = $possibleDirs[0];
        error_log('Attempting to create directory: ' . $uploadDir);
    }
    
    // Ensure directory exists
    if (!is_dir($uploadDir)) {
        $created = @mkdir($uploadDir, 0755, true);
        if (!$created) {
            error_log('Failed to create upload directory: ' . $uploadDir);
            error_log('Directory exists: ' . (is_dir($uploadDir) ? 'yes' : 'no'));
            error_log('Is writable: ' . (is_writable(dirname($uploadDir)) ? 'yes' : 'no'));
            sendResponse(false, 'Failed to create upload directory. Please contact administrator.');
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        error_log('Upload directory is not writable: ' . $uploadDir);
        error_log('Directory permissions: ' . substr(sprintf('%o', fileperms($uploadDir)), -4));
        sendResponse(false, 'Upload directory is not writable. Please contact administrator.');
    }
    
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = 'submission_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $permanentFilePath = $uploadDir . $newFileName;
    
    // Log file save attempt
    error_log('Attempting to save file:');
    error_log('  Source: ' . $fileTmpName);
    error_log('  Destination: ' . $permanentFilePath);
    error_log('  Source exists: ' . (file_exists($fileTmpName) ? 'yes' : 'no'));
    error_log('  Source readable: ' . (is_readable($fileTmpName) ? 'yes' : 'no'));
    error_log('  Destination dir writable: ' . (is_writable($uploadDir) ? 'yes' : 'no'));
    
    if (!move_uploaded_file($fileTmpName, $permanentFilePath)) {
        $error = error_get_last();
        error_log('move_uploaded_file failed');
        error_log('  Error: ' . ($error ? $error['message'] : 'Unknown error'));
        error_log('  PHP upload_max_filesize: ' . ini_get('upload_max_filesize'));
        error_log('  PHP post_max_size: ' . ini_get('post_max_size'));
        error_log('  File size: ' . $fileSize . ' bytes');
        
        // Try alternative: copy instead of move
        if (file_exists($fileTmpName) && is_readable($fileTmpName)) {
            error_log('Attempting copy as fallback...');
            if (@copy($fileTmpName, $permanentFilePath)) {
                error_log('File copied successfully using copy()');
                @unlink($fileTmpName); // Clean up temp file
            } else {
                $copyError = error_get_last();
                error_log('copy() also failed: ' . ($copyError ? $copyError['message'] : 'Unknown error'));
                sendResponse(false, 'Failed to save uploaded file. Error: ' . ($error ? $error['message'] : 'Unknown error'));
            }
        } else {
            sendResponse(false, 'Failed to save uploaded file. Temporary file not accessible.');
        }
    } else {
        error_log('File saved successfully to: ' . $permanentFilePath);
    }
    
    // Verify file was saved
    if (!file_exists($permanentFilePath)) {
        error_log('File verification failed - file does not exist after save');
        sendResponse(false, 'File was not saved correctly. Please try again.');
    }

    // Save submission to database
    try {
        $submissionId = GuestSubmission::create([
            'context_id' => 1, // Default context, adjust as needed
            'manuscript_title' => $manuscriptTitle,
            'article_type' => $articleType,
            'abstract' => $abstract,
            'keywords' => $keywords,
            'manuscript_file_path' => $permanentFilePath,
            'manuscript_file_name' => $fileName,
            'authors' => $authors,
        ]);
        
        error_log('Guest submission saved to database with ID: ' . $submissionId);
    } catch (Exception $e) {
        error_log('Failed to save submission to database: ' . $e->getMessage());
        // Continue with email notification even if database save fails
        $submissionId = null;
    }

    // Prepare email content
    $submittingAuthor = $authors[0];
    $submittingAuthorEmail = $submittingAuthor['email'];
    
    // Build author list HTML
    $authorListHtml = '';
    foreach ($authors as $index => $author) {
        $authorNum = $index + 1;
        $authorListHtml .= "
        <div style='background: #f9fafb; padding: 15px; margin-bottom: 15px; border-radius: 5px; border-left: 4px solid #2563eb;'>
            <h4 style='margin: 0 0 10px 0; color: #1f2937;'>Author {$authorNum}</h4>
            <p style='margin: 5px 0;'><strong>Title:</strong> " . htmlspecialchars($author['title']) . "</p>
            <p style='margin: 5px 0;'><strong>Name:</strong> " . htmlspecialchars($author['name']) . " " . htmlspecialchars($author['surname']) . "</p>
            <p style='margin: 5px 0;'><strong>Authorship:</strong> " . htmlspecialchars($author['authorship']) . "</p>
            <p style='margin: 5px 0;'><strong>Email:</strong> " . htmlspecialchars($author['email']) . "</p>
            <p style='margin: 5px 0;'><strong>Address:</strong> " . htmlspecialchars($author['address']) . "</p>
            <p style='margin: 5px 0;'><strong>Affiliation:</strong> " . htmlspecialchars($author['affiliation']) . "</p>
        </div>";
    }

    // Admin email content
    $adminEmailSubject = "New Guest Submission: " . $manuscriptTitle;
    $adminEmailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: white; padding: 30px; border: 1px solid #e5e7eb; border-radius: 0 0 8px 8px; }
            .section { margin-bottom: 25px; }
            .section-title { font-size: 18px; font-weight: bold; color: #2563eb; margin-bottom: 10px; border-bottom: 2px solid #2563eb; padding-bottom: 5px; }
            .info-row { margin: 8px 0; }
            .label { font-weight: bold; color: #1f2937; }
            .abstract-box { background: #f9fafb; padding: 15px; border-radius: 5px; border: 1px solid #e5e7eb; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>New Guest Submission</h1>
                <p style='margin: 10px 0 0 0;'>" . SITE_NAME . "</p>
            </div>
            <div class='content'>
                <p style='background: #fef3c7; padding: 15px; border-radius: 5px; border-left: 4px solid #f59e0b;'>
                    <strong>Action Required:</strong> A new manuscript has been submitted via the guest submission form. 
                    " . ($submissionId ? "View and manage this submission at: <a href='" . SITE_URL . "/guest/admin/view/{$submissionId}'>Submission #{$submissionId}</a>" : "Please log in to OJS and manually enter this submission using the \"Submit on behalf of\" feature.") . "
                </p>
                
                <div class='section'>
                    <div class='section-title'>Submission Details</div>
                    <div class='info-row'><span class='label'>Article Type:</span> " . htmlspecialchars($articleType) . "</div>
                    <div class='info-row'><span class='label'>Manuscript Title:</span> " . htmlspecialchars($manuscriptTitle) . "</div>
                    <div class='info-row'><span class='label'>Keywords:</span> " . htmlspecialchars($keywords) . "</div>
                </div>
                
                <div class='section'>
                    <div class='section-title'>Abstract</div>
                    <div class='abstract-box'>" . nl2br(htmlspecialchars($abstract)) . "</div>
                </div>
                
                <div class='section'>
                    <div class='section-title'>Author Information</div>
                    {$authorListHtml}
                </div>
                
                <div class='section'>
                    <div class='section-title'>Manuscript File</div>
                    <p>The manuscript file is attached to this email: <strong>" . htmlspecialchars($fileName) . "</strong></p>
                </div>
                
                <div class='footer'>
                    <p><strong>Next Steps:</strong></p>
                    <ol>
                        <li>Log in to your OJS admin account at " . SITE_URL . "</li>
                        <li>Go to Submissions → New Submission</li>
                        <li>Use the \"Submit on behalf of\" feature</li>
                        <li>Copy and paste the author information, title, abstract, and keywords from this email</li>
                        <li>Upload the attached manuscript file</li>
                        <li>Complete the 5-step submission process</li>
                        <li>Assign to a Section Editor or begin the review process</li>
                    </ol>
                    <p style='margin-top: 15px;'>Submission received: " . date('F j, Y, g:i a') . "</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    // Author confirmation email
    $authorEmailSubject = "Submission Confirmation - " . SITE_NAME;
    $authorEmailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: white; padding: 30px; border: 1px solid #e5e7eb; border-radius: 0 0 8px 8px; }
            .success-icon { text-align: center; font-size: 48px; color: #059669; margin-bottom: 20px; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>Thank You for Your Submission</h1>
            </div>
            <div class='content'>
                <div class='success-icon'>✓</div>
                <p>Dear " . htmlspecialchars($submittingAuthor['title'] . ' ' . $submittingAuthor['name'] . ' ' . $submittingAuthor['surname']) . ",</p>
                
                <p>Thank you for submitting your manuscript to " . SITE_NAME . ". We have successfully received your submission:</p>
                
                <div style='background: #f9fafb; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #2563eb;'>
                    <p style='margin: 5px 0;'><strong>Title:</strong> " . htmlspecialchars($manuscriptTitle) . "</p>
                    <p style='margin: 5px 0;'><strong>Article Type:</strong> " . htmlspecialchars($articleType) . "</p>
                    <p style='margin: 5px 0;'><strong>Submitted:</strong> " . date('F j, Y, g:i a') . "</p>
                </div>
                
                <p>Your manuscript will be reviewed by our editorial team. You will receive further communication regarding the status of your submission.</p>
                
                <p>If you have any questions, please contact us at <a href='mailto:" . ADMIN_EMAIL . "'>" . ADMIN_EMAIL . "</a>.</p>
                
                <div class='footer'>
                    <p>" . SITE_NAME . "<br>
                    <a href='" . SITE_URL . "'>" . SITE_URL . "</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    // Send emails using PHPMailer with SMTP (same configuration as testmail/index.php)
    
    // Log submission attempt
    error_log('Attempting to send admin email to: ' . ADMIN_EMAIL);
    error_log('File attachment: ' . $fileTmpName . ' (exists: ' . (file_exists($fileTmpName) ? 'yes' : 'no') . ')');
    
    // Send admin email with attachment
    $adminEmailResult = sendEmailWithSMTP(
        ADMIN_EMAIL,
        $adminEmailSubject,
        $adminEmailBody,
        strip_tags($adminEmailBody), // Plain text version
        $fileTmpName, // Attachment path
        $fileName, // Attachment name
        $submittingAuthorEmail // Reply-To: submitting author's email
    );
    
    $adminEmailSent = $adminEmailResult['success'];
    error_log('Admin email sent (with attachment): ' . ($adminEmailSent ? 'YES' : 'NO'));
    
    // If failed with attachment, try without attachment
    if (!$adminEmailSent && $fileTmpName && file_exists($fileTmpName)) {
        error_log('Retrying admin email without attachment...');
        $adminEmailResult = sendEmailWithSMTP(
            ADMIN_EMAIL,
            $adminEmailSubject . ' (File attachment failed - see note in email)',
            $adminEmailBody . '<p><strong>Note:</strong> The manuscript file could not be attached to this email. Please contact the submitter at ' . htmlspecialchars($submittingAuthorEmail) . ' to request the file.</p>',
            strip_tags($adminEmailBody) . "\n\nNote: The manuscript file could not be attached. Please contact the submitter at " . $submittingAuthorEmail . " to request the file.",
            null, // No attachment
            null, // No attachment name
            $submittingAuthorEmail // Reply-To: submitting author's email
        );
        $adminEmailSent = $adminEmailResult['success'];
        error_log('Admin email sent (without attachment): ' . ($adminEmailSent ? 'YES' : 'NO'));
    }
    
    if (!$adminEmailSent) {
        error_log('Admin email error: ' . (isset($adminEmailResult['message']) ? $adminEmailResult['message'] : 'Unknown error'));
    }
    
    // Send author confirmation email
    error_log('Attempting to send author email to: ' . $submittingAuthorEmail);
    $authorEmailResult = sendEmailWithSMTP(
        $submittingAuthorEmail,
        $authorEmailSubject,
        $authorEmailBody,
        strip_tags($authorEmailBody), // Plain text version
        null, // No attachment
        null, // No attachment name
        ADMIN_EMAIL // Reply-To: admin email
    );
    
    $authorEmailSent = $authorEmailResult['success'];
    error_log('Author email sent: ' . ($authorEmailSent ? 'YES' : 'NO'));
    if (!$authorEmailSent) {
        error_log('Author email error: ' . $authorEmailResult['message']);
    }
    
    // Check if emails were sent successfully
    if ($adminEmailSent && $authorEmailSent) {
        sendResponse(true, 'Submission successful! A confirmation email has been sent to your email address.');
    } elseif ($adminEmailSent) {
        // Admin email sent but author email failed
        $errorMsg = 'Submission received! However, we could not send a confirmation email to your address.';
        if (isset($authorEmailResult['message'])) {
            $errorMsg .= ' Error: ' . $authorEmailResult['message'];
        }
        sendResponse(true, $errorMsg);
    } else {
        // Admin email failed - this is critical
        $errorMsg = 'Failed to send submission emails. ';
        if (isset($adminEmailResult['message'])) {
            $errorMsg .= 'Error: ' . $adminEmailResult['message'];
        } else {
            $errorMsg .= 'Please try again or contact the administrator.';
        }
        error_log('Failed to send submission emails for: ' . $manuscriptTitle);
        sendResponse(false, $errorMsg);
    }

} catch (Exception $e) {
    // Log the error
    error_log('Guest submission error: ' . $e->getMessage());
    sendResponse(false, 'An unexpected error occurred. Please try again later.');
}
?>

