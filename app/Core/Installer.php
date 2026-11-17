<?php

declare(strict_types=1);

namespace Readscope\Core;

use Readscope\Config\Config;
use Readscope\Database\Schema;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Installer
 *
 * Runs upon plugin activation: migrations, default settings, capability provisioning.
 *
 * @package Readscope\Core
 */
class Installer
{
    /**
     * Run the installation / activation process.
     *
     * @return void
     */
    public static function activate(bool $networkWide = false): void
    {
        if ($networkWide && function_exists('is_multisite') && is_multisite()) {
            foreach (get_sites(['fields' => 'ids', 'number' => 0]) as $blogId) {
                switch_to_blog((int) $blogId);
                self::activateSite();
                restore_current_blog();
            }

            return;
        }

        self::activateSite();
    }

    /**
     * Provision a site that is created after a network-wide activation (tables, defaults, capabilities).
     *
     * @param \WP_Site $site
     * @return void
     */
    public static function onNewSite($site): void
    {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!is_plugin_active_for_network(plugin_basename(READSCOPE_PLUGIN_FILE))) {
            return;
        }

        switch_to_blog((int) $site->blog_id);
        self::activateSite();
        restore_current_blog();
    }

    /**
     * Run the installation for the current site.
     *
     * @return void
     */
    public static function activateSite(): void
    {
        // 1. Run custom table migrations
        Schema::migrate();

        // 2. Set default options if not existing
        if (!get_option('readscope_settings')) {
            update_option('readscope_settings', Config::getDefaults());
        }

        // 3. Set plugin installed timestamp & version
        if (!get_option('readscope_installed_at')) {
            update_option('readscope_installed_at', current_time('mysql'));
        }
        update_option('readscope_version', READSCOPE_VERSION);

        // 4. Add custom user capabilities to administrator
        self::addCapabilities();

        // 5. Fire activation action hook for add-ons (like Readscope Booking Pro)
        do_action('readscope/activated');

        // Flag rewrite rules to be flushed safely on next init hook
        update_option('readscope_flush_rewrite', 1);
    }

    /**
     * Add custom Readscope Booking capabilities to Administrator role.
     *
     * @return void
     */
    public static function addCapabilities(): void
    {
        $adminRole = get_role('administrator');
        if (!$adminRole) {
            return;
        }

        $capabilities = [
            'manage_readscope',
            'manage_readscope_bookings',
            'manage_readscope_tours',
            'manage_readscope_hotels',
            'manage_readscope_settings',
            'manage_readscope_checkin',
        ];

        foreach ($capabilities as $cap) {
            $adminRole->add_cap($cap);
        }
    }
}
