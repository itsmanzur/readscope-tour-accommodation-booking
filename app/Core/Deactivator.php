<?php

declare(strict_types=1);

namespace Readscope\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Deactivator
 *
 * Runs upon plugin deactivation: clears scheduled cron jobs, flushes rewrite rules.
 *
 * @package Readscope\Core
 */
class Deactivator
{
    /**
     * Every WP-Cron / Action Scheduler hook Readscope Booking schedules (including legacy ones).
     *
     * @var string[]
     */
    public const SCHEDULED_HOOKS = [
        'readscope_cleanup_expired_holds',
        'readscope_expire_stale_bookings',
        'readscope_daily_reminders',
        'readscope_process_email_delivery',
        'readscope_process_webhook_delivery',
        'readscope_daily_privacy_retention',
        'readscope_daily_cleanup',
        'readscope_sync_ical_feeds',
    ];

    /**
     * Run the deactivation process.
     *
     * @return void
     */
    public static function deactivate(): void
    {
        self::unscheduleAll();

        // Fire deactivation action hook
        do_action('readscope/deactivated');

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Remove every scheduled Readscope Booking event, whatever its arguments.
     *
     * wp_clear_scheduled_hook() only matches events scheduled with the same (empty)
     * args, so queued email/webhook deliveries would survive it. wp_unschedule_hook()
     * removes all of them; queued Action Scheduler actions are removed as well.
     *
     * @return void
     */
    public static function unscheduleAll(): void
    {
        foreach (self::SCHEDULED_HOOKS as $hook) {
            wp_unschedule_hook($hook);
            if (function_exists('as_unschedule_all_actions')) {
                as_unschedule_all_actions($hook);
            }
        }
    }
}
