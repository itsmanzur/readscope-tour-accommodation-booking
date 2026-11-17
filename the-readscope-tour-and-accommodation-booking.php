<?php
/**
 * Plugin Name:       The Readscope Tour and Accommodation Booking
 * Description:       The Next-Gen Travel, Tour & Accommodation Booking Engine for WordPress.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            The Readscope
 * Author URI:        https://thereadscope.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       the-readscope-tour-and-accommodation-booking
 * Domain Path:       /languages
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// 1. Define Global Constants
define('READSCOPE_VERSION', '1.1.0');
// Integer that only grows when the public hooks / helpers add-ons rely on change. Readscope Booking Pro compares it, not the version.
define('READSCOPE_API_LEVEL', 1);
define('READSCOPE_MIN_PHP_VER', '8.0');
define('READSCOPE_PLUGIN_FILE', __FILE__);
define('READSCOPE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('READSCOPE_PLUGIN_URL', plugin_dir_url(__FILE__));

// 2. Minimum PHP Version Check
if (version_compare(PHP_VERSION, READSCOPE_MIN_PHP_VER, '<')) {
    add_action('admin_notices', static function () {
        $message = sprintf(
            /* translators: 1: Required PHP version, 2: Current PHP version */
            esc_html__('The Readscope Tour and Accommodation Booking requires PHP version %1$s or greater. You are running version %2$s. Please upgrade your PHP version.', 'the-readscope-tour-and-accommodation-booking'),
            READSCOPE_MIN_PHP_VER,
            PHP_VERSION
        );
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    });
    return;
}

// 3. PSR-4 Autoloading Setup
if (file_exists(READSCOPE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once READSCOPE_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Fallback PSR-4 Autoloader for Readscope\ namespace
    spl_autoload_register(static function (string $class) {
        $prefix = 'Readscope\\';
        $baseDir = READSCOPE_PLUGIN_DIR . 'app/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
}

// 4. Register Activation & Deactivation Hooks
register_activation_hook(READSCOPE_PLUGIN_FILE, ['Readscope\\Core\\Installer', 'activate']);
register_deactivation_hook(READSCOPE_PLUGIN_FILE, ['Readscope\\Core\\Deactivator', 'deactivate']);
add_action('wp_initialize_site', ['Readscope\\Core\\Installer', 'onNewSite'], 900);

// 5. Load Public Developer Functions & Template Engine API
require_once READSCOPE_PLUGIN_DIR . 'app/Support/functions.php';

// 6. Global Accessor Helper
if (!function_exists('readscope')) {
    /**
     * Get the main Readscope Booking plugin orchestrator instance.
     *
     * @return \Readscope\Core\Plugin
     */
    function readscope(): \Readscope\Core\Plugin
    {
        return \Readscope\Core\Plugin::instance();
    }
}

// 7. Multi-language Locale Filter & Textdomain Loader
add_filter(
    'plugin_locale',
    static function (string $locale, string $domain): string {
        if ('the-readscope-tour-and-accommodation-booking' !== $domain) {
            return $locale;
        }

        $lang = (string) \Readscope\Config\Config::get('plugin_language', 'default');
        if ('bn' === $lang) {
            return 'bn_BD';
        }
        if ('en' === $lang) {
            return 'en_US';
        }

        return $locale;
    },
    10,
    2
);

add_action('init', static function (): void {
    readscope_load_plugin_textdomain();
}, 1);

// 8. Bootstrap Plugin on plugins_loaded
add_action('plugins_loaded', static function () {
    // 1.0.x stored everything under the old "tourivo" name; move it over once before anything reads it.
    \Readscope\Database\LegacyMigrator::maybeRun();
    readscope()->boot();
}, 10);

// 9. Keep shortcodes saved by 1.0.x working: [tourivo_tours] behaves exactly like [readscope_tours].
add_action('init', static function (): void {
    global $shortcode_tags;

    foreach ($shortcode_tags as $tag => $callback) {
        if (0 === strpos((string) $tag, 'readscope_')) {
            $legacy = 'tourivo_' . substr((string) $tag, 10);
            if (!isset($shortcode_tags[$legacy])) {
                add_shortcode($legacy, $callback);
            }
        }
    }
}, 99);
