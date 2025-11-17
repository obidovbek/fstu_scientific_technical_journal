<?php
/**
 * Simple log viewer for guest submission debugging
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== EMAIL ERROR LOG ===\n\n";

$emailLogFile = __DIR__ . '/../../logs/email-errors.log';
if (file_exists($emailLogFile)) {
    echo file_get_contents($emailLogFile);
    echo "\n";
} else {
    echo "No email error log found at: $emailLogFile\n\n";
}

echo "\n=== PHP ERROR LOG (last 50 lines) ===\n\n";

// Try to find PHP error log
$possibleLogs = [
    ini_get('error_log'),
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log',
    __DIR__ . '/../../logs/error.log',
    __DIR__ . '/../../error.log'
];

$found = false;
foreach ($possibleLogs as $logPath) {
    if ($logPath && file_exists($logPath) && is_readable($logPath)) {
        echo "Reading from: $logPath\n\n";
        $lines = file($logPath);
        $lastLines = array_slice($lines, -50);
        
        // Filter for relevant lines
        foreach ($lastLines as $line) {
            if (stripos($line, 'PHPMailer') !== false || 
                stripos($line, 'guest') !== false || 
                stripos($line, 'email') !== false ||
                stripos($line, 'SMTP') !== false) {
                echo $line;
            }
        }
        $found = true;
        break;
    }
}

if (!$found) {
    echo "Could not find readable PHP error log.\n";
    echo "Tried locations:\n";
    foreach ($possibleLogs as $logPath) {
        if ($logPath) {
            echo "  - $logPath " . (file_exists($logPath) ? '(exists but not readable)' : '(not found)') . "\n";
        }
    }
}

echo "\n=== END OF LOGS ===\n";
?>

