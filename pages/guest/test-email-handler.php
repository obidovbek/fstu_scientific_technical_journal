<?php
/**
 * Test Email Handler for Guest Submission
 * Sends a test email using the same SMTP configuration as the submission handler
 */

// Load OJS configuration and bootstrap (PHPMailer is already included)
if (!defined('INDEX_FILE_LOCATION')) {
    require_once(__DIR__ . '/../../config.inc.php');
    require_once(__DIR__ . '/../../lib/pkp/includes/bootstrap.php');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set JSON response header
header('Content-Type: application/json');

define('SITE_NAME', 'International Technology Journal');
define('ADMIN_EMAIL', 'stj_admin@fstu.uz');

/**
 * Send test email using PHPMailer with SMTP
 * Uses the same configuration as testmail/index.php (which works)
 */
function sendTestEmail($to) {
    // SMTP configuration - matching testmail/index.php exactly (local, not global)
    $smtpConfig = [
        'host' => 'fstu.uz',
        'port' => 465,
        'username' => 'stj_info@fstu.uz',
        'password' => '7san3_9I3'
    ];
    
    // Email options - hardcoded to avoid global variable issues (like testmail/index.php)
    $fromEmail = 'stj_info@fstu.uz';
    $toEmail = $to;
    $emailSubject = 'Test Email - Guest Submission Form';
    $emailText = "Test Email - Guest Submission Form\n\nThis is a test email sent from the Guest Submission Form.\n\nIf you received this email, the SMTP configuration is working correctly.\n\nSent: " . date('F j, Y, g:i a');
    $emailHtml = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #e5e7eb; border-radius: 0 0 8px 8px; }
                .success-icon { text-align: center; font-size: 48px; color: #059669; margin-bottom: 20px; }
                .info-box { background: #f9fafb; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #2563eb; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1 style="margin: 0;">Test Email</h1>
                    <p style="margin: 10px 0 0 0;">' . SITE_NAME . '</p>
                </div>
                <div class="content">
                    <div class="success-icon">✓</div>
                    <h2>Email Configuration Test</h2>
                    <p>This is a test email sent from the Guest Submission Form.</p>
                    <div class="info-box">
                        <p><strong>SMTP Server:</strong> ' . htmlspecialchars($smtpConfig['host']) . '</p>
                        <p><strong>Port:</strong> ' . htmlspecialchars($smtpConfig['port']) . '</p>
                        <p><strong>From:</strong> stj_info@fstu.uz</p>
                        <p><strong>To:</strong> ' . htmlspecialchars($toEmail) . '</p>
                        <p><strong>Sent:</strong> ' . date('F j, Y, g:i a') . '</p>
                    </div>
                    <p>If you received this email, the SMTP configuration is working correctly and the guest submission form should be able to send emails.</p>
                    <p style="margin-top: 20px; color: #6b7280; font-size: 14px;">
                        ' . SITE_NAME . '<br>
                        <a href="https://stj.fstu.uz/itj">https://stj.fstu.uz/itj</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';
    
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
        $mail->addAddress($toEmail);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $emailSubject;
        $mail->Body = $emailHtml;
        $mail->AltBody = $emailText;
        
        // Send email (PHPMailer handles connection internally)
        $mail->send();
        
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        $errorInfo = isset($mail) ? $mail->ErrorInfo : 'PHPMailer not initialized';
        
        error_log('Test Email Error: ' . $errorMsg);
        error_log('Test Email ErrorInfo: ' . $errorInfo);
        
        // Log to file
        $logFile = __DIR__ . '/../../logs/email-errors.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Test Email to $toEmail - Error: " . $errorMsg . " | ErrorInfo: " . $errorInfo . "\n", FILE_APPEND);
        
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
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Check for JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        error_log('Raw input: ' . $rawInput);
        // Try to get email from POST as fallback
        $to = isset($_POST['to']) ? trim($_POST['to']) : (isset($_GET['to']) ? trim($_GET['to']) : 'obidov.bekzod94@gmail.com');
    } else {
        // Get recipient email
        $to = isset($input['to']) ? trim($input['to']) : 'obidov.bekzod94@gmail.com';
    }
    
    // Validate email
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Invalid email address: ' . htmlspecialchars($to));
    }
    
    // Log the attempt
    error_log('Test email handler called - To: ' . $to);
    
    // Send test email
    $emailResult = sendTestEmail($to);
    
    if ($emailResult['success']) {
        error_log('Test email sent successfully to: ' . $to);
        sendResponse(true, 'Test email sent successfully to ' . $to . '. Please check your inbox (and spam folder).');
    } else {
        $errorMsg = 'Failed to send test email. ';
        if (isset($emailResult['message'])) {
            $errorMsg .= 'Error: ' . $emailResult['message'];
        } else {
            $errorMsg .= 'Please check the server logs for details.';
        }
        error_log('Test email failed: ' . $errorMsg);
        sendResponse(false, $errorMsg);
    }
    
} catch (Exception $e) {
    error_log('Test email handler error: ' . $e->getMessage());
    sendResponse(false, 'An unexpected error occurred: ' . $e->getMessage());
}
?>

