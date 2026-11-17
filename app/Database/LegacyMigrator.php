<?php

declare(strict_types=1);

namespace Readscope\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class LegacyMigrator
 *
 * One-time, idempotent migration of data stored by versions 1.0.x, which used the "tourivo" name,
 * to the "readscope" names. Nothing is deleted: tables, options, meta, posts, terms, capabilities
 * and scheduled events are renamed in place so every value stays byte-identical.
 *
 * @package Readscope\Database
 */
class LegacyMigrator
{
    public const DONE_OPTION = 'readscope_legacy_migrated';
    public const LOCK_OPTION = 'readscope_legacy_migrating';

    /** @var string[] Database errors seen while running. */
    private static array $errors = [];

    /** Custom tables (suffix after the prefix). */
    private const TABLES = ['bookings', 'booking_items', 'inquiries', 'inventories', 'logs'];

    /** Options owned by the core plugin. */
    private const OPTIONS = [
        'db_version', 'flush_rewrite', 'installed_at', 'notification_email',
        'settings', 'version', 'destination_children',
    ];

    private const POST_TYPES = ['tour', 'hotel', 'room'];
    private const TAXONOMIES = ['destination', 'activity_type', 'amenity', 'tour_feature'];

    private const CAPABILITIES = ['manage_tourivo', 'manage_tourivo_bookings', 'manage_tourivo_checkin', 'manage_tourivo_hotels', 'manage_tourivo_settings', 'manage_tourivo_tours'];

    private const CRON_HOOKS = [
        'cleanup_expired_holds', 'daily_cleanup', 'daily_privacy_retention', 'daily_reminders',
        'expire_stale_bookings', 'process_email_delivery', 'process_webhook_delivery', 'sync_ical_feeds',
    ];

    /**
     * Run once, only when old data exists.
     */
    public static function maybeRun(): void
    {
        if (get_option(self::DONE_OPTION) !== false) {
            return;
        }

        if (!self::hasLegacyData()) {
            add_option(self::DONE_OPTION, 'none', '', true);
            return;
        }

        if (!self::acquireLock()) {
            return;
        }

        try {
            // Marked done only when every step worked; otherwise it retries on the next load.
            if (self::run()) {
                add_option(self::DONE_OPTION, defined('READSCOPE_VERSION') ? READSCOPE_VERSION : '1.1.0', '', true);
            }
        } finally {
            delete_option(self::LOCK_OPTION);
        }
    }

    private static function hasLegacyData(): bool
    {
        global $wpdb;

        foreach (['tourivo_db_version', 'tourivo_version', 'tourivo_settings'] as $option) {
            if (get_option($option) !== false) {
                return true;
            }
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (bool) $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->prefix . 'tourivo_bookings'));
    }

    /**
     * Run every step. Each step only renames, so a step that failed can safely run again later.
     *
     * @return bool True when no database error was seen.
     */
    public static function run(): bool
    {
        self::$errors = [];

        self::renameTables();
        self::renameOptions();
        self::renamePostTypesAndTaxonomies();
        self::renameMetaKeys();
        self::rewriteContent();
        self::renameMenuItems();
        self::renameCapabilities();
        self::renameCronEvents();
        self::renameActionSchedulerHooks();
        self::purgeTransients();

        update_option('readscope_flush_rewrite', 1);
        wp_cache_flush();

        if (self::$errors) {
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log('Readscope 1.0.x data migration will retry: ' . implode(' | ', array_unique(self::$errors))); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only when WP_DEBUG_LOG is on; tells the owner why the migration is retrying.
            }

            return false;
        }

        return true;
    }

    /**
     * Atomic lock: add_option() fails when the row exists (option_name is unique), so only one request wins.
     * A lock older than five minutes belongs to a request that died and is taken over once.
     */
    private static function acquireLock(): bool
    {
        if (add_option(self::LOCK_OPTION, time(), '', false)) {
            return true;
        }

        if ((int) get_option(self::LOCK_OPTION) < time() - 300) {
            delete_option(self::LOCK_OPTION);

            return (bool) add_option(self::LOCK_OPTION, time(), '', false);
        }

        return false;
    }

    private static function sql(string $query): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared -- One-time rename of this plugin's own data; callers pass prepared SQL or fixed table names.
        if ($wpdb->query($query) === false) {
            self::$errors[] = (string) $wpdb->last_error;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    private static function update(string $table, array $data, array $where): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time rename of this plugin's own rows; the object cache is flushed afterwards.
        if ($wpdb->update($table, $data, $where) === false) {
            self::$errors[] = (string) $wpdb->last_error;
        }
    }

    /**
     * @param array<string, mixed> $where
     */
    private static function delete(string $table, array $where): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time rename of this plugin's own rows; the object cache is flushed afterwards.
        if ($wpdb->delete($table, $where) === false) {
            self::$errors[] = (string) $wpdb->last_error;
        }
    }

    /** Navigation menu entries that point at a renamed post type or taxonomy. */
    private static function renameMenuItems(): void
    {
        global $wpdb;

        self::sql($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = CONCAT('readscope_', SUBSTRING(meta_value, 9)) WHERE meta_key = '_menu_item_object' AND meta_value LIKE %s", $wpdb->esc_like('tourivo_') . '%'));
    }

    private static function tableExists(string $table): bool
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (bool) $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    }

    private static function renameTables(): void
    {
        global $wpdb;

        foreach (self::TABLES as $suffix) {
            $old = $wpdb->prefix . 'tourivo_' . $suffix;
            $new = $wpdb->prefix . 'readscope_' . $suffix;

            if (!self::tableExists($old)) {
                continue;
            }

            if (self::tableExists($new)) {
                // An empty table may have been created by activation before the migration ran. Replace it.
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $rows = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$new}`");
                if ($rows > 0) {
                    continue; // Both hold data: never overwrite anything.
                }
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                self::sql("DROP TABLE `{$new}`");
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            self::sql("RENAME TABLE `{$old}` TO `{$new}`");
        }
    }

    private static function renameOptions(): void
    {
        global $wpdb;

        foreach (self::OPTIONS as $name) {
            $old = 'tourivo_' . $name;
            $new = 'readscope_' . $name;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", $old));
            if (!(int) $exists) {
                continue;
            }

            // A fresh activation may already have written a default; the old value is the user's data.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            self::delete($wpdb->options, ['option_name' => $new]);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            self::update($wpdb->options, ['option_name' => $new], ['option_name' => $old]);
        }

        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    private static function renamePostTypesAndTaxonomies(): void
    {
        global $wpdb;

        foreach (self::POST_TYPES as $type) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            self::update($wpdb->posts, ['post_type' => 'readscope_' . $type], ['post_type' => 'tourivo_' . $type]);
        }

        foreach (self::TAXONOMIES as $taxonomy) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            self::update($wpdb->term_taxonomy, ['taxonomy' => 'readscope_' . $taxonomy], ['taxonomy' => 'tourivo_' . $taxonomy]);
        }
    }

    private static function renameMetaKeys(): void
    {
        global $wpdb;

        $tables = [
            [$wpdb->postmeta, 'meta_key'],
            [$wpdb->usermeta, 'meta_key'],
            [$wpdb->termmeta, 'meta_key'],
            [$wpdb->commentmeta, 'meta_key'],
        ];

        foreach ($tables as [$table, $column]) {
            // "_tourivo_x" -> "_readscope_x" (9 characters are replaced) and "tourivo_x" -> "readscope_x" (8).
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            self::sql($wpdb->prepare("UPDATE {$table} SET {$column} = CONCAT('_readscope_', SUBSTRING({$column}, 10)) WHERE {$column} LIKE %s", $wpdb->esc_like('_tourivo_') . '%'));
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            self::sql($wpdb->prepare("UPDATE {$table} SET {$column} = CONCAT('readscope_', SUBSTRING({$column}, 9)) WHERE {$column} LIKE %s", $wpdb->esc_like('tourivo_') . '%'));
        }
    }

    /**
     * Shortcodes, block comments and Elementor widget names saved inside content.
     * These are plain-text (never serialized) values, so a direct string replace is safe.
     */
    private static function rewriteContent(): void
    {
        global $wpdb;

        $pairs = [
            ['[tourivo_', '[readscope_'],
            ['<!-- wp:tourivo/', '<!-- wp:readscope/'],
        ];

        foreach ($pairs as [$from, $to]) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            self::sql($wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s", $from, $to, '%' . $wpdb->esc_like($from) . '%'));
        }

        // Elementor keeps the HTML it rendered with the old widget and CSS names (_elementor_element_cache) and the old
        // stylesheet reference (_elementor_css). Drop both for pages that use our widgets; Elementor rebuilds them on the next view.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time lookup of pages that embed this plugin's widgets.
        $elementorPosts = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE %s", '%' . $wpdb->esc_like('tourivo_') . '%'));
        foreach ((array) $elementorPosts as $postId) {
            foreach (['_elementor_element_cache', '_elementor_css', '_elementor_page_assets'] as $cacheKey) {
                delete_post_meta((int) $postId, $cacheKey);
            }
        }

        // Elementor stores page layouts as JSON text.
        $json = [
            ['"widgetType":"tourivo_', '"widgetType":"readscope_'],
            ['[tourivo_', '[readscope_'],
        ];
        foreach ($json as [$from, $to]) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            self::sql($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key = '_elementor_data' AND meta_value LIKE %s", $from, $to, '%' . $wpdb->esc_like($from) . '%'));
        }
    }

    private static function renameCapabilities(): void
    {
        $roles = wp_roles();

        foreach ($roles->role_objects as $role) {
            foreach (self::CAPABILITIES as $old) {
                if (!array_key_exists($old, $role->capabilities)) {
                    continue;
                }
                $granted = (bool) $role->capabilities[$old];
                $role->add_cap(str_replace('tourivo', 'readscope', $old), $granted);
                $role->remove_cap($old);
            }
        }
    }

    private static function renameCronEvents(): void
    {
        $cron = _get_cron_array();
        if (!is_array($cron)) {
            return;
        }

        $changed = false;
        $oldHooks = array_map(static fn (string $h): string => 'tourivo_' . $h, self::CRON_HOOKS);

        foreach ($cron as $timestamp => $hooks) {
            if (!is_array($hooks)) {
                continue;
            }
            foreach ($hooks as $hook => $events) {
                // Custom recurrence names ("tourivo_five_minutes") were renamed too.
                foreach ($events as $key => $event) {
                    if (is_array($event) && isset($event['schedule']) && is_string($event['schedule']) && 0 === strpos($event['schedule'], 'tourivo_')) {
                        $events[$key]['schedule'] = 'readscope_' . substr($event['schedule'], 8);
                        $cron[$timestamp][$hook][$key]['schedule'] = $events[$key]['schedule'];
                        $changed = true;
                    }
                }
                if (!in_array($hook, $oldHooks, true)) {
                    continue;
                }
                $newHook = 'readscope_' . substr((string) $hook, 8);
                $cron[$timestamp][$newHook] = isset($cron[$timestamp][$newHook])
                    ? array_merge($cron[$timestamp][$newHook], $events)
                    : $events;
                unset($cron[$timestamp][$hook]);
                $changed = true;
            }
        }

        if ($changed) {
            _set_cron_array($cron);
        }
    }

    private static function renameActionSchedulerHooks(): void
    {
        global $wpdb;

        $actions = $wpdb->prefix . 'actionscheduler_actions';
        if (self::tableExists($actions)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            self::sql($wpdb->prepare("UPDATE {$actions} SET hook = CONCAT('readscope_', SUBSTRING(hook, 9)) WHERE hook LIKE %s", $wpdb->esc_like('tourivo_') . '%'));
        }

        $groups = $wpdb->prefix . 'actionscheduler_groups';
        if (self::tableExists($groups)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            self::sql($wpdb->prepare("UPDATE {$groups} SET slug = CONCAT('readscope', SUBSTRING(slug, 8)) WHERE slug LIKE %s", $wpdb->esc_like('tourivo') . '%'));
        }
    }

    /** Caches only; they rebuild on demand. */
    private static function purgeTransients(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        self::sql($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like('_transient_tourivo_') . '%', $wpdb->esc_like('_transient_timeout_tourivo_') . '%'));

        // 1.0.x rate-limit buckets ("tourivo_rl_<md5>") are orphaned now: the new buckets use another prefix.
        self::sql($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like('tourivo_rl_') . '%'));
    }
}
