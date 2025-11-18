<?php

/**
 * @file tools/install_guest_submissions_standalone.php
 *
 * Standalone installer for guest submissions database schema
 * This version reads config directly and uses mysqli
 */

// Read config file
$configFile = dirname(__FILE__, 2) . '/config.inc.php';
if (!file_exists($configFile)) {
    die("Error: config.inc.php not found at: $configFile\n");
}

// Read config file content
$configContent = file_get_contents($configFile);

// Manual parsing for database section (more reliable than parse_ini_file with special chars)
$dbHost = '127.0.0.1';
$dbUser = 'ojs_user';
$dbPass = '';
$dbName = 'ojs_db';
$dbPort = 3306;

// Extract database section
if (preg_match('/\[database\](.*?)(?=\[|$)/s', $configContent, $matches)) {
    $dbSection = $matches[1];
    
    // Parse each line
    $lines = explode("\n", $dbSection);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and empty lines
        if (empty($line) || $line[0] === ';') {
            continue;
        }
        
        // Match key = value
        if (preg_match('/^(\w+)\s*=\s*(.+)$/i', $line, $m)) {
            $key = strtolower(trim($m[1]));
            $value = trim($m[2]);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            switch ($key) {
                case 'host':
                    $dbHost = $value;
                    break;
                case 'username':
                    $dbUser = $value;
                    break;
                case 'password':
                    $dbPass = $value;
                    break;
                case 'name':
                    $dbName = $value;
                    break;
                case 'port':
                    $dbPort = (int)$value;
                    break;
            }
        }
    }
}

echo "Installing Guest Submissions database schema...\n\n";
echo "Database: $dbName @ $dbHost\n";
echo "User: $dbUser\n";
echo "Password: " . (empty($dbPass) ? "(empty)" : str_repeat('*', min(strlen($dbPass), 10))) . "\n\n";

// Connect to database
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✓ Database connection established\n\n";

// Function to check if table exists
function tableExists($mysqli, $tableName) {
    $result = $mysqli->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

// Function to execute SQL
function executeSQL($mysqli, $sql, $tableName) {
    if ($mysqli->query($sql)) {
        echo "✓ Created table: $tableName\n";
        return true;
    } else {
        echo "❌ Error creating table $tableName: " . $mysqli->error . "\n";
        return false;
    }
}

$success = true;

// Create guest_submissions table
if (!tableExists($mysqli, 'guest_submissions')) {
    $sql = "CREATE TABLE `guest_submissions` (
        `submission_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `context_id` BIGINT UNSIGNED NOT NULL,
        `manuscript_title` VARCHAR(255) NOT NULL,
        `article_type` VARCHAR(100) NOT NULL,
        `abstract` TEXT,
        `keywords` VARCHAR(500),
        `manuscript_file_path` VARCHAR(500),
        `manuscript_file_name` VARCHAR(255),
        `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        `assigned_editor_id` BIGINT UNSIGNED,
        `decision` VARCHAR(50),
        `decision_notes` TEXT,
        `admin_notes` TEXT,
        `date_submitted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_assigned` TIMESTAMP NULL,
        `date_decided` TIMESTAMP NULL,
        PRIMARY KEY (`submission_id`),
        INDEX `idx_context_id` (`context_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_assigned_editor` (`assigned_editor_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!executeSQL($mysqli, $sql, 'guest_submissions')) {
        $success = false;
    }
} else {
    echo "⚠ Table already exists: guest_submissions\n";
}

// Create guest_submission_authors table
if (!tableExists($mysqli, 'guest_submission_authors')) {
    $sql = "CREATE TABLE `guest_submission_authors` (
        `author_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `submission_id` BIGINT UNSIGNED NOT NULL,
        `title` VARCHAR(50),
        `first_name` VARCHAR(100) NOT NULL,
        `last_name` VARCHAR(100) NOT NULL,
        `authorship` VARCHAR(100),
        `email` VARCHAR(255) NOT NULL,
        `address` VARCHAR(500),
        `affiliation` VARCHAR(500),
        `seq` INT NOT NULL DEFAULT 0,
        PRIMARY KEY (`author_id`),
        INDEX `idx_submission_id` (`submission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!executeSQL($mysqli, $sql, 'guest_submission_authors')) {
        $success = false;
    }
} else {
    echo "⚠ Table already exists: guest_submission_authors\n";
}

// Create guest_submission_log table
if (!tableExists($mysqli, 'guest_submission_log')) {
    $sql = "CREATE TABLE `guest_submission_log` (
        `log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `submission_id` BIGINT UNSIGNED NOT NULL,
        `user_id` BIGINT UNSIGNED,
        `action` VARCHAR(100) NOT NULL,
        `message` TEXT,
        `date_logged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        INDEX `idx_submission_id` (`submission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!executeSQL($mysqli, $sql, 'guest_submission_log')) {
        $success = false;
    }
} else {
    echo "⚠ Table already exists: guest_submission_log\n";
}

$mysqli->close();

if ($success) {
    echo "\n✅ Installation complete!\n\n";
    echo "Next steps:\n";
    echo "1. Access the admin dashboard at: /itj/guest/admin\n";
    echo "2. View pending submissions\n";
    echo "3. Assign submissions to editors\n";
    echo "4. Make decisions and respond to authors\n\n";
} else {
    echo "\n⚠ Installation completed with errors. Please review the messages above.\n";
    exit(1);
}

