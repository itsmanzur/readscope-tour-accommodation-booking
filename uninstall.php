<?php
/**
 * Readscope Booking Uninstall Handler
 *
 * @package Readscope Booking
 */

declare(strict_types=1);

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load autoloader if not present
if (!class_exists('Readscope\\Core\\Uninstaller')) {
    $readscopeAutoloader = plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    if (file_exists($readscopeAutoloader)) {
        require_once $readscopeAutoloader;
    } else {
        spl_autoload_register(static function (string $class) {
            $prefix = 'Readscope\\';
            $baseDir = plugin_dir_path(__FILE__) . 'app/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}

if (is_multisite()) {
    // Network delete: clean every site, not just the main one.
    foreach (get_sites(['fields' => 'ids', 'number' => 0]) as $readscopeBlogId) {
        switch_to_blog((int) $readscopeBlogId);
        \Readscope\Core\Uninstaller::uninstall();
        restore_current_blog();
    }
} else {
    \Readscope\Core\Uninstaller::uninstall();
}
