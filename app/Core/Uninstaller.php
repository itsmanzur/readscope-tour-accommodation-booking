<?php

declare(strict_types=1);

namespace Readscope\Core;

use Readscope\Database\Schema;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Uninstaller
 *
 * Runs when the plugin is deleted via WordPress admin, if erase on uninstall is enabled.
 *
 * @package Readscope\Core
 */
class Uninstaller
{
    /**
     * Run the uninstallation process.
     *
     * @return void
     */
    public static function uninstall(): void
    {
        global $wpdb;

        // 1. Remove custom capabilities
        $adminRole = get_role('administrator');
        if ($adminRole) {
            $capabilities = [
                'manage_readscope',
                'manage_readscope_bookings',
                'manage_readscope_tours',
                'manage_readscope_hotels',
                'manage_readscope_settings',
                'manage_readscope_checkin',
            ];
            foreach ($capabilities as $cap) {
                $adminRole->remove_cap($cap);
            }
        }

        // 2. Clear scheduled hooks (all args) and queued Action Scheduler actions
        Deactivator::unscheduleAll();

        $settings = get_option('readscope_settings', []);
        $eraseDataOnDelete = !empty($settings['erase_data_on_uninstall']);

        // Only purge tables and options if user explicitly requested full cleanup
        if ($eraseDataOnDelete) {
            Schema::dropTables();
            delete_option('readscope_settings');
            delete_option('readscope_installed_at');
            delete_option('readscope_version');
            delete_option('readscope_db_version');
            delete_option('readscope_flush_rewrite');
            delete_option('readscope_notification_email');

            // Purge rate-limiting, hold sessions, and cached transients
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query(
                "DELETE FROM {$wpdb->options} 
                 WHERE option_name LIKE '_transient_readscope_%' 
                    OR option_name LIKE '_transient_timeout_readscope_%'"
            );

            // Purge rate-limit counter rows
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like('readscope_rl_') . '%'
                )
            );

            // Purge checkout hold records
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like('readscope_hold_') . '%'
                )
            );

            // Purge Readscope Booking user meta
            $wpdb->query(
                "DELETE FROM {$wpdb->usermeta} 
                 WHERE meta_key IN ('_readscope_wishlist', 'readscope_dismiss_welcome_notice')"
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        }

        do_action('readscope/uninstalled');
    }
}
