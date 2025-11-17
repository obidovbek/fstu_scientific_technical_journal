<?php

// Load OJS configuration and bootstrap (PHPMailer is already included)
if (!defined('INDEX_FILE_LOCATION')) {
    require_once(__DIR__ . '/../../config.inc.php');
    require_once(__DIR__ . '/../../lib/pkp/includes/bootstrap.php');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// SMTP configuration
$smtpConfig = [
    'host' => 'fstu.uz',
    'port' => 465,
    'secure' => 'ssl', // 'ssl' for port 465, 'tls' for port 587
    'username' => 'stj_info@fstu.uz',
    'password' => '7san3_9I3'
];

// Email options - automatically send to this address
$mailOptions = [
    'from' => 'stj_info@fstu.uz',
    'to' => 'obidov.bekzod94@gmail.com',
    'subject' => 'Test Email from SMTP',
    'text' => 'This is a test email sent from PHP using SMTP.',
    'html' => '<h1>Test Email</h1><p>This is a test email sent from PHP using SMTP.</p>'
];

/**
 * Function to send email
 */
function sendEmail($to = null, $subject = null, $text = null, $html = null) {
    // SMTP configuration - matching Node.js exactly
    $smtpConfig = [
        'host' => 'fstu.uz',
        'port' => 465,
        'username' => 'stj_info@fstu.uz',
        'password' => '7san3_9I3'
    ];
    
    // Email options - hardcoded to avoid global variable issues
    $fromEmail = 'stj_info@fstu.uz';
    $toEmail = $to ?? 'obidov.bekzod94@gmail.com';
    $emailSubject = $subject ?? 'Test Email from SMTP';
    $emailText = $text ?? 'This is a test email sent from PHP using SMTP.';
    $emailHtml = $html ?? '<h1>Test Email</h1><p>This is a test email sent from PHP using SMTP.</p>';
    
    try {
        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Enable verbose debug output for troubleshooting
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            echo "DEBUG: " . htmlspecialchars($str) . "\n";
        };
        
        // Server settings - matching Node.js configuration exactly
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
        
        // Timeout - matching Node.js (10 seconds)
        $mail->Timeout = 10;
        
        // SSL options - matching Node.js tls: { rejectUnauthorized: false }
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Set email details
        $mail->setFrom($fromEmail, 'SMTP Test');
        $mail->addAddress($toEmail);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $emailSubject;
        $mail->Body = $emailHtml;
        $mail->AltBody = $emailText;
        
        // Send email (PHPMailer handles connection internally)
        echo "Sending email...\n";
        echo "From: " . $fromEmail . "\n";
        echo "To: " . $toEmail . "\n";
        echo "Subject: " . $emailSubject . "\n\n";
        
        $mail->send();
        
        echo "✓ Email sent successfully!\n";
        echo "Message ID: " . $mail->getLastMessageID() . "\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "✗ Error sending email: " . $e->getMessage() . "\n";
        echo "\nDetailed error information:\n";
        echo "Error info: " . $mail->ErrorInfo . "\n";
        
        // Provide helpful suggestions
        $errorMsg = $e->getMessage();
        
        if (strpos($errorMsg, 'Connection refused') !== false || 
            strpos($errorMsg, 'Connection timed out') !== false ||
            strpos($errorMsg, 'SMTP connect() failed') !== false) {
            echo "\n💡 Suggestions:\n";
            echo "  - Check if the SMTP server hostname is correct (try: mail.fstu.uz)\n";
            echo "  - Verify the port number (465 for SSL)\n";
            echo "  - Check your internet connection\n";
            echo "  - Verify firewall settings\n";
        } else if (strpos($errorMsg, 'SMTP authentication') !== false ||
                   strpos($errorMsg, 'Invalid') !== false && strpos($errorMsg, 'credential') !== false) {
            echo "\n💡 Suggestions:\n";
            echo "  - Verify username and password are correct\n";
            echo "  - Check if the account requires app-specific password\n";
        } else if (strpos($errorMsg, '535') !== false || strpos($errorMsg, '534') !== false) {
            echo "\n💡 Suggestions:\n";
            echo "  - Authentication failed. Check username and password\n";
        }
        
        return false;
    }
}

// Automatically send test email
?>
<!DOCTYPE html>
<html>
<head>
    <title>SMTP Email Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2563eb;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
        }
        .info {
            background: #dbeafe;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background: #d1fae5;
            border-left: 4px solid #059669;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        pre {
            background: #1f2937;
            color: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 SMTP Email Test</h1>
        
        <div class="info">
            <strong>SMTP Configuration:</strong><br>
            Server: <code><?php echo htmlspecialchars($smtpConfig['host']); ?></code><br>
            Port: <code><?php echo htmlspecialchars($smtpConfig['port']); ?></code><br>
            Username: <code><?php echo htmlspecialchars($smtpConfig['username']); ?></code><br>
            Secure: <code><?php echo $smtpConfig['secure'] === 'ssl' ? 'Yes (SSL)' : 'No'; ?></code><br>
            To: <code><?php echo htmlspecialchars($mailOptions['to']); ?></code>
        </div>

        <?php
        // Capture output from sendEmail function
        ob_start();
        $result = sendEmail();
        $output = ob_get_clean();
        
        if ($result) {
            echo '<div class="success">';
            echo '<strong>✓ Email Sent Successfully!</strong><br>';
            echo 'A test email has been sent to: <code>' . htmlspecialchars($mailOptions['to']) . '</code><br>';
            echo 'Check your inbox (and spam folder) for the test email.';
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<strong>✗ Email Failed</strong><br>';
            echo 'Failed to send test email. See details below.';
            echo '</div>';
        }
        
        if ($output) {
            echo '<h3>📋 Detailed Output:</h3>';
            echo '<pre>' . htmlspecialchars($output) . '</pre>';
        }
        ?>
    </div>
</body>
</html>

