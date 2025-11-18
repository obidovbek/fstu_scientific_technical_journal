<?php

/**
 * @defgroup pages_guest Guest Submission Pages
 */

/**
 * @file pages/guest/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_guest
 *
 * @brief Handle guest submission requests.
 *
 */

switch ($op) {
    case 'index':
    case 'form':
        define('HANDLER_CLASS', 'APP\pages\guest\GuestHandler');
        break;
    case 'testmail':
        // Direct access to testmail/index.php - bypass OJS routing
        $testmailPath = __DIR__ . '/testmail/index.php';
        if (file_exists($testmailPath)) {
            require_once($testmailPath);
            exit;
        }
        // Fall through to 404 if file doesn't exist
        break;
    case 'submit':
        // Direct access to handler - bypass OJS routing
        $handlerPath = __DIR__ . '/guest-submission-handler.php';
        if (file_exists($handlerPath)) {
            require_once($handlerPath);
            exit;
        }
        // Fall through to 404 if file doesn't exist
        break;
    case 'test-email':
        // Direct access to test email handler - bypass OJS routing
        $testEmailPath = __DIR__ . '/test-email-handler.php';
        if (file_exists($testEmailPath)) {
            require_once($testEmailPath);
            exit;
        }
        // Fall through to 404 if file doesn't exist
        break;
    case 'guest-submission.css':
        // Serve CSS file
        $cssPath = __DIR__ . '/guest-submission.css';
        if (file_exists($cssPath)) {
            header('Content-Type: text/css');
            readfile($cssPath);
            exit;
        }
        break;
    case 'guest-submission.js':
        // Serve JS file
        $jsPath = __DIR__ . '/guest-submission.js';
        if (file_exists($jsPath)) {
            header('Content-Type: application/javascript');
            readfile($jsPath);
            exit;
        }
        break;
    case 'check-logs':
        // View logs for debugging
        $logsPath = __DIR__ . '/check-logs.php';
        if (file_exists($logsPath)) {
            require_once($logsPath);
            exit;
        }
        break;
    case 'clear-cache':
        // Clear template cache
        $cachePath = __DIR__ . '/clear-cache.php';
        if (file_exists($cachePath)) {
            require_once($cachePath);
            exit;
        }
        break;
    case 'register-menu-item':
        // Register guest submission menu item
        $registerPath = __DIR__ . '/register-menu-item.php';
        if (file_exists($registerPath)) {
            require_once($registerPath);
            exit;
        }
        break;
    default:
        define('HANDLER_CLASS', 'APP\pages\guest\GuestHandler');
        break;
}

