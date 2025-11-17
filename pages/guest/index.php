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
    default:
        define('HANDLER_CLASS', 'APP\pages\guest\GuestHandler');
        break;
}

