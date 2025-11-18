<?php
/**
 * Clear OJS Template Cache
 * Run this file to clear template cache after modifying templates
 */

// Load OJS configuration and bootstrap
if (!defined('INDEX_FILE_LOCATION')) {
    require_once(__DIR__ . '/../../config.inc.php');
    require_once(__DIR__ . '/../../lib/pkp/includes/bootstrap.php');
}

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\core\Core;
use PKP\cache\CacheManager;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Clear OJS Template Cache</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d1fae5; border-left: 4px solid #059669; padding: 15px; margin: 20px 0; }
        .info { background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; }
        pre { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 6px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Clear OJS Template Cache</h1>
    
    <?php
    try {
        $request = Application::get()->getRequest();
        $templateManager = TemplateManager::getManager($request);
        
        // Clear template cache
        $templateManager->clearTemplateCache();
        
        // Clear CSS cache
        $templateManager->clearCssCache();
        
        // Clear file cache
        $cacheManager = CacheManager::getManager();
        $cacheManager->flush();
        
        // Clear compiled templates directory
        $cacheDir = Core::getBaseDir() . '/cache';
        $compiledDir = $cacheDir . '/t_*';
        $files = glob($compiledDir);
        $deleted = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
                $deleted++;
            }
        }
        
        echo '<div class="success">';
        echo '<strong>✓ Cache Cleared Successfully!</strong><br>';
        echo 'Template cache, CSS cache, and compiled templates have been cleared.<br>';
        echo "Deleted {$deleted} compiled template files.";
        echo '</div>';
        
        echo '<div class="info">';
        echo '<strong>Next Steps:</strong><br>';
        echo '1. Refresh your OJS frontend page<br>';
        echo '2. The "Submit your article" menu item should now appear<br>';
        echo '3. If it still doesn\'t appear, check that the template file is in the correct location:<br>';
        echo '<code>templates/frontend/components/primaryNavMenu.tpl</code>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="error">';
        echo '<strong>✗ Error:</strong> ' . htmlspecialchars($e->getMessage());
        echo '</div>';
    }
    ?>
    
    <p><a href="javascript:location.reload()">Refresh this page</a> | <a href="<?php echo $request->getBaseUrl(); ?>">Go to OJS Home</a></p>
</body>
</html>

