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
use APP\template\TemplateManager;
use PKP\core\PKPRequest;

class GuestHandler extends Handler
{
    /**
     * @see PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        // No authorization required - public access
        return parent::authorize($request, $args, $roleAssignments);
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
     * Display the guest submission form using OJS template system
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function form($args, $request)
    {
        // Setup template
        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);
        
        // Display the guest submission template
        $templateMgr->display('frontend/pages/guestSubmission.tpl');
    }
}

