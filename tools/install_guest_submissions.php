<?php

/**
 * @file tools/install_guest_submissions.php
 *
 * Install database schema for guest submissions management
 */

require(dirname(__FILE__) . '/bootstrap.php');

use APP\core\Application;
use PKP\db\DAORegistry;

class InstallGuestSubmissions extends \PKP\cliTool\CommandLineTool
{
    /**
     * @var \PKP\db\DBConnection
     */
    private $conn;

    /**
     * Constructor
     */
    public function __construct($argv = [])
    {
        parent::__construct($argv);
    }

    /**
     * Print command usage information.
     */
    public function usage()
    {
        echo "Install Guest Submissions Database Schema\n"
            . "Usage: {$this->scriptName}\n";
    }

    /**
     * Check if table exists
     */
    private function tableExists($tableName)
    {
        $result = $this->conn->query("SHOW TABLES LIKE '$tableName'");
        return $result && $result->numRows() > 0;
    }

    /**
     * Execute SQL statement
     */
    private function executeSQL($sql)
    {
        $result = $this->conn->execute($sql);
        if (!$result) {
            $error = $this->conn->errorMsg();
            throw new Exception("SQL Error: $error\nSQL: $sql");
        }
        return $result;
    }

    /**
     * Execute the installation
     */
    public function execute()
    {
        echo "Installing Guest Submissions database schema...\n\n";

        try {
            // Initialize OJS application
            $application = Application::get();
            $application->initialize();
            
            // Get database connection using OJS's database layer
            import('lib.pkp.classes.db.DBConnection');
            $this->conn = \DBConnection::getInstance();
            if (!$this->conn) {
                throw new Exception("Failed to get database connection. Make sure OJS is properly configured.");
            }
            
            // Test connection
            $testResult = $this->conn->query("SELECT 1");
            if (!$testResult) {
                throw new Exception("Database connection test failed: " . $this->conn->errorMsg());
            }
            echo "✓ Database connection established\n";

            // Create guest_submissions table
            if (!$this->tableExists('guest_submissions')) {
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
                
                $this->executeSQL($sql);
                echo "✓ Created table: guest_submissions\n";
            } else {
                echo "⚠ Table already exists: guest_submissions\n";
            }

            // Create guest_submission_authors table
            if (!$this->tableExists('guest_submission_authors')) {
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
                
                $this->executeSQL($sql);
                echo "✓ Created table: guest_submission_authors\n";
            } else {
                echo "⚠ Table already exists: guest_submission_authors\n";
            }

            // Create guest_submission_log table
            if (!$this->tableExists('guest_submission_log')) {
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
                
                $this->executeSQL($sql);
                echo "✓ Created table: guest_submission_log\n";
            } else {
                echo "⚠ Table already exists: guest_submission_log\n";
            }

            echo "\n✅ Installation complete!\n\n";
            echo "Next steps:\n";
            echo "1. Access the admin dashboard at: /itj/guest/admin\n";
            echo "2. View pending submissions\n";
            echo "3. Assign submissions to editors\n";
            echo "4. Make decisions and respond to authors\n\n";

        } catch (Exception $e) {
            echo "\n❌ Error: " . $e->getMessage() . "\n";
            if (isset($this->conn) && $this->conn->errorMsg()) {
                echo "Database Error: " . $this->conn->errorMsg() . "\n";
            }
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            return false;
        }

        return true;
    }
}

$tool = new InstallGuestSubmissions($argv ?? []);
$tool->execute();

