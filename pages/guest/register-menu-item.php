<?php
/**
 * Force register the Guest Submission navigation menu item type
 */

// Bootstrap OJS
if (!defined('INDEX_FILE_LOCATION')) {
    require_once(__DIR__ . '/../../config.inc.php');
    require_once(__DIR__ . '/../../lib/pkp/includes/bootstrap.php');
}

use APP\core\Application;
use APP\services\NavigationMenuService;
use PKP\cache\CacheManager;
use PKP\core\Core;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Guest Submission Menu Item</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e3a8a;
            margin-top: 0;
        }
        .success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .info {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .steps {
            margin: 20px 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Register Guest Submission Menu Item</h1>
        
        <?php
        try {
            // Clear all caches
            $cacheManager = CacheManager::getManager();
            $cacheManager->flush(null, CACHE_TYPE_FILE);
            $cacheManager->flush(null, CACHE_TYPE_OBJECT);
            
            echo '<div class="success">✅ All caches cleared successfully</div>';
            
            // Get the navigation menu service
            $navigationMenuService = new NavigationMenuService();
            
            // Get all menu item types
            $menuItemTypes = $navigationMenuService->getMenuItemTypes();
            
            echo '<div class="info"><strong>📋 Registered Menu Item Types:</strong><br><br>';
            
            $guestSubmissionFound = false;
            foreach ($menuItemTypes as $type => $config) {
                echo "• <code>$type</code>: " . htmlspecialchars($config['title']) . "<br>";
                if ($type === NavigationMenuService::NMI_TYPE_GUEST_SUBMISSION) {
                    $guestSubmissionFound = true;
                }
            }
            echo '</div>';
            
            if ($guestSubmissionFound) {
                echo '<div class="success">';
                echo '<strong>✅ Guest Submission menu item type is registered!</strong><br><br>';
                echo 'The <code>NMI_TYPE_GUEST_SUBMISSION</code> type is now available.';
                echo '</div>';
                
                echo '<div class="info">';
                echo '<strong>📝 Next Steps:</strong>';
                echo '<ol class="steps">';
                echo '<li>Go to <strong>Settings → Website → Setup → Navigation Menus</strong></li>';
                echo '<li>Click <strong>"Primary Navigation Menu"</strong> to edit</li>';
                echo '<li>Look for <strong>"Submit your article"</strong> in the <strong>Unassigned Menu Items</strong> list</li>';
                echo '<li>If you don\'t see it, click <strong>"Add Item"</strong> and select <strong>"Submit your article"</strong> from the dropdown</li>';
                echo '<li>Drag it to <strong>Assigned Menu Items</strong></li>';
                echo '<li>Click <strong>Save</strong></li>';
                echo '<li>Refresh your journal homepage</li>';
                echo '</ol>';
                echo '</div>';
            } else {
                echo '<div class="error">';
                echo '<strong>❌ Guest Submission menu item type NOT found!</strong><br><br>';
                echo 'Please check that <code>classes/services/NavigationMenuService.php</code> has been uploaded correctly.';
                echo '</div>';
            }
            
            // Try to manually add the menu item to the primary navigation
            $request = Application::get()->getRequest();
            $context = $request->getContext();
            
            if ($context) {
                echo '<div class="info">';
                echo '<strong>🔍 Checking existing navigation menus...</strong><br><br>';
                
                $navigationMenuDao = DAORegistry::getDAO('NavigationMenuDAO');
                $navigationMenus = $navigationMenuDao->getByContextId($context->getId())->toArray();
                
                echo 'Found ' . count($navigationMenus) . ' navigation menu(s) for this journal.<br>';
                
                foreach ($navigationMenus as $menu) {
                    echo '• Menu: <code>' . htmlspecialchars($menu->getTitle()) . '</code> (Area: ' . htmlspecialchars($menu->getAreaName()) . ')<br>';
                }
                
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
            <strong>Alternative Method:</strong> If the menu item still doesn't appear, you can add it as a <strong>Custom Link</strong>:
            <ol class="steps" style="font-size: 14px;">
                <li>In Navigation Menus, click "Add Item"</li>
                <li>Select "Custom Page" or "Remote URL"</li>
                <li>Title: <code>Submit your article</code></li>
                <li>URL: <code><?php echo htmlspecialchars(Application::get()->getRequest()->url(null, 'guest', 'form')); ?></code></li>
                <li>Save and add to Primary Navigation Menu</li>
            </ol>
        </div>
    </div>
</body>
</html>

