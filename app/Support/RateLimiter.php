<?php

declare(strict_types=1);

namespace Readscope\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RateLimiter
 *
 * Fixed-window request counter shared by every public Readscope Booking endpoint.
 *
 * Both back ends increment atomically, so a burst of parallel requests cannot slip past the limit:
 * - Persistent object cache: `wp_cache_add()` + `wp_cache_incr()` in the `readscope_rate_limit` group.
 * - Otherwise (or if the cache refuses to increment): one `INSERT ... ON DUPLICATE KEY UPDATE` on the options
 *   table. Each bucket is one row, option_name `readscope_rl_<md5(key)>`, option_value `<count>|<expires_ts>`,
 *   autoload `no`. The statement restarts the window (count 1, expiry now + window) when the stored expiry has
 *   passed and otherwise increments the count; the new count is read back from the same connection.
 *
 * Failure mode: when the database write fails, administrators (manage_options) are let through (fail open) so
 * they cannot lock themselves out, while everyone else, including anonymous booking and inquiry requests, is
 * treated as limited (fail closed).
 *
 * Expired rows are removed by purgeExpired() (daily cron, plus 1% of DB-path hits); the Uninstaller drops all
 * `readscope_rl_%` options.
 *
 * @package Readscope\Support
 */
class RateLimiter
{
    private const CACHE_GROUP = 'readscope_rate_limit';
    private const ROW_PREFIX  = 'readscope_rl_';

    /**
     * Clock override for tests (returns a unix timestamp). Leave null in production.
     *
     * @internal
     * @var (\Closure(): int)|null
     */
    public static ?\Closure $clock = null;

    /**
     * Register one attempt against $key and report whether it is still within the limit.
     *
     * @param string $key    Unique bucket key (e.g. 'readscope_rl_booking_' . md5($ip)).
     * @param int    $limit  Maximum attempts allowed per window.
     * @param int    $window Window length in seconds.
     * @return bool True when the attempt is allowed, false when the limit is exhausted.
     */
    public static function hit(string $key, int $limit, int $window): bool
    {
        if (function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache()) {
            wp_cache_add($key, 0, self::CACHE_GROUP, $window);
            $count = wp_cache_incr($key, 1, self::CACHE_GROUP);

            if ($count !== false) {
                return (int) $count <= $limit;
            }
            // Increment unsupported or the entry vanished: use the database counter.
        }

        $count = self::incrementInDatabase($key, $window);
        if ($count === null) {
            return self::failureResult();
        }

        if (wp_rand(1, 100) === 1) {
            self::purgeExpired();
        }

        return $count <= $limit;
    }

    /**
     * Clear a bucket (used after a successful, trusted action or in tests).
     */
    public static function reset(string $key): void
    {
        if (function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache()) {
            wp_cache_delete($key, self::CACHE_GROUP);
        }

        global $wpdb;
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s", self::rowName($key))); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Rows are written by raw SQL (atomic upsert) and never read through the options cache; table name from $wpdb->options.
    }

    /**
     * Delete rate-limit rows whose window has ended. Runs from the daily cron and opportunistically.
     *
     * @return int Number of rows removed.
     */
    public static function purgeExpired(): int
    {
        global $wpdb;

        $deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Housekeeping of rows that bypass the options cache.
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(SUBSTRING_INDEX(option_value, '|', -1) AS UNSIGNED) <= %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from $wpdb->options.
                $wpdb->esc_like(self::ROW_PREFIX) . '%',
                self::now()
            )
        );

        return is_int($deleted) ? $deleted : 0;
    }

    /**
     * Atomically count one hit in the options table.
     *
     * @return int|null The bucket's count after this hit, or null when the database write failed.
     */
    private static function incrementInDatabase(string $key, int $window): ?int
    {
        global $wpdb;

        $now     = self::now();
        $expires = $now + max(1, $window);

        if ($wpdb->query('SET @readscope_rl_c = 1') === false) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Session variable used to read the new count back atomically.
            return null;
        }

        $sql = $wpdb->prepare(
            "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')
             ON DUPLICATE KEY UPDATE option_value = CONCAT(
                 @readscope_rl_c := IF(CAST(SUBSTRING_INDEX(option_value, '|', -1) AS UNSIGNED) <= %d, 1, CAST(SUBSTRING_INDEX(option_value, '|', 1) AS UNSIGNED) + 1),
                 '|',
                 IF(CAST(SUBSTRING_INDEX(option_value, '|', -1) AS UNSIGNED) <= %d, %d, CAST(SUBSTRING_INDEX(option_value, '|', -1) AS UNSIGNED))
             )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from $wpdb->options; all values are bound below.
            self::rowName($key),
            '1|' . $expires,
            $now,
            $now,
            $expires
        );

        if ($wpdb->query($sql) === false) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Built by $wpdb->prepare() above; a single-statement atomic upsert cannot be expressed with the options API; the row is never read via get_option().
            return null;
        }

        $count = $wpdb->get_var('SELECT @readscope_rl_c'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reads the session variable set by the upsert.
        return is_numeric($count) ? (int) $count : null;
    }

    /**
     * Result when the counter cannot be written: administrators pass, everyone else is limited.
     */
    private static function failureResult(): bool
    {
        return function_exists('is_user_logged_in') && is_user_logged_in()
            && function_exists('current_user_can') && current_user_can('manage_options');
    }

    private static function rowName(string $key): string
    {
        return self::ROW_PREFIX . md5($key);
    }

    private static function now(): int
    {
        return self::$clock !== null ? (int) (self::$clock)() : time();
    }
}
