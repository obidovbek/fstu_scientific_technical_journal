<?php
/**
 * Debug page for guest admin - helps identify the issue
 */

// Load OJS
if (!defined('INDEX_FILE_LOCATION')) {
    require_once(__DIR__ . '/../../config.inc.php');
    require_once(__DIR__ . '/../../lib/pkp/includes/bootstrap.php');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Guest Admin Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Guest Admin Debug Information</h1>

    <div class="section">
        <h2>1. Class Loading</h2>
        <?php
        $classExists = class_exists('APP\guest\GuestSubmission');
        if ($classExists) {
            echo '<p class="success">✓ GuestSubmission class exists</p>';
        } else {
            echo '<p class="error">✗ GuestSubmission class NOT found</p>';
            echo '<p>File should be at: classes/guest/GuestSubmission.php</p>';
            echo '<p>File exists: ' . (file_exists(__DIR__ . '/../../classes/guest/GuestSubmission.php') ? 'YES' : 'NO') . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>2. Database Tables</h2>
        <?php
        try {
            $db = \DBConnection::getInstance();
            if ($db) {
                echo '<p class="success">✓ Database connection OK</p>';
                
                // Check tables
                $tables = ['guest_submissions', 'guest_submission_authors', 'guest_submission_log'];
                foreach ($tables as $table) {
                    $result = $db->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->numRows() > 0) {
                        echo '<p class="success">✓ Table exists: ' . $table . '</p>';
                    } else {
                        echo '<p class="error">✗ Table missing: ' . $table . '</p>';
                    }
                }
            } else {
                echo '<p class="error">✗ Database connection failed</p>';
            }
        } catch (\Exception $e) {
            echo '<p class="error">✗ Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>3. Handler Class</h2>
        <?php
        $handlerExists = class_exists('APP\pages\guest\GuestAdminHandler');
        if ($handlerExists) {
            echo '<p class="success">✓ GuestAdminHandler class exists</p>';
        } else {
            echo '<p class="error">✗ GuestAdminHandler class NOT found</p>';
            echo '<p>File should be at: pages/guest/GuestAdminHandler.php</p>';
            echo '<p>File exists: ' . (file_exists(__DIR__ . '/GuestAdminHandler.php') ? 'YES' : 'NO') . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Test Database Query</h2>
        <?php
        try {
            if (class_exists('APP\guest\GuestSubmission')) {
                $stats = \APP\guest\GuestSubmission::getStatistics();
                echo '<p class="success">✓ getStatistics() works</p>';
                echo '<pre>' . print_r($stats, true) . '</pre>';
                
                $submissions = \APP\guest\GuestSubmission::getAll();
                echo '<p class="success">✓ getAll() works - Found ' . count($submissions) . ' submissions</p>';
            } else {
                echo '<p class="error">✗ Cannot test - GuestSubmission class not found</p>';
            }
        } catch (\Exception $e) {
            echo '<p class="error">✗ Database query failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } catch (\Error $e) {
            echo '<p class="error">✗ Fatal error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
    </div>

    <div class="section">
        <h2>5. PHP Error Logs</h2>
        <p>Check these locations for error details:</p>
        <ul>
            <li>/var/log/php8.1-fpm.log</li>
            <li>/var/log/nginx/stj-fstu-error.log</li>
            <li><?php echo __DIR__ . '/../../logs/'; ?></li>
        </ul>
        <p>To view recent errors:</p>
        <pre>tail -n 50 /var/log/php8.1-fpm.log</pre>
    </div>

    <div class="section">
        <h2>6. Next Steps</h2>
        <ol>
            <li>If tables are missing, run: <code>php8.1 tools/install_guest_submissions_standalone.php</code></li>
            <li>Clear cache: <code>php tools/clearCache.php</code></li>
            <li>Check PHP error logs (see section 5)</li>
            <li>Verify you're logged in as admin/manager</li>
        </ol>
    </div>
</body>
</html>

