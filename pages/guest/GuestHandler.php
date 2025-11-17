<?php

/**
 * @file pages/guest/GuestHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class GuestHandler
 *
 * @ingroup pages_guest
 *
 * @brief Handle guest submission form display.
 */

namespace APP\pages\guest;

use APP\handler\Handler;
use PKP\core\PKPRequest;

class GuestHandler extends Handler
{
    /**
     * @see PKPHandler::validate()
     * Override to allow access without context
     */
    public function validate($requiredContexts = null, $request = null)
    {
        // Allow access without requiring a journal context
        // This allows the page to work at site level
    }

    /**
     * Display the guest submission form
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request)
    {
        $this->form($args, $request);
    }

    /**
     * Display the guest submission form
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function form($args, $request)
    {
        // Read and output the HTML form directly
        $formPath = __DIR__ . '/guest-submission.html';
        $cssPath = __DIR__ . '/guest-submission.css';
        $jsPath = __DIR__ . '/guest-submission.js';
        
        if (file_exists($formPath)) {
            // Read the HTML file
            $html = file_get_contents($formPath);
            
            // Get the router to build URLs with journal context
            $router = $request->getRouter();
            
            // Update the form action to point to the correct handler URL
            // Use OJS URL building to include journal context
            $handlerUrl = $router->url($request, null, 'guest', 'submit');
            $html = str_replace(
                'id="guestSubmissionForm"',
                'id="guestSubmissionForm" action="' . htmlspecialchars($handlerUrl) . '"',
                $html
            );
            
            // Embed CSS directly in the HTML
            if (file_exists($cssPath)) {
                $cssContent = file_get_contents($cssPath);
                // Replace the link tag with embedded style tag
                $html = str_replace(
                    '<link rel="stylesheet" href="guest-submission.css">',
                    '<style>' . $cssContent . '</style>',
                    $html
                );
            }
            
            // Embed JS directly in the HTML
            if (file_exists($jsPath)) {
                $jsContent = file_get_contents($jsPath);
                // Replace the script tag with embedded script content
                $html = str_replace(
                    '<script src="guest-submission.js"></script>',
                    '<script>' . $jsContent . '</script>',
                    $html
                );
            }
            
            echo $html;
            exit;
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'Guest submission form not found.';
            exit;
        }
    }
}

