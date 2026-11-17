<?php

declare(strict_types=1);

namespace Readscope\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public search filters (date, guests, destination) of the front-end search bar.
 *
 * Search URLs are plain GET links that visitors share and caches store, so they carry no nonce. The
 * parameters are registered as WordPress query vars and read from the parsed request (`$wp->query_vars`),
 * never from the request superglobals. Every value is sanitized and validated here, once.
 *
 * `readscope_destination` is also the taxonomy's own query var and stays in the main query. The date and
 * guest count are taken out of the main query after they are read: WordPress must keep treating
 * `/?readscope_date=...` as the front page, exactly as it did before these vars were registered.
 *
 * @package Readscope\Support
 */
final class PublicSearch
{
    public const VAR_DATE        = 'readscope_date';
    public const VAR_GUESTS      = 'readscope_guests';
    public const VAR_DESTINATION = 'readscope_destination';

    /**
     * Query vars the search bar sends.
     *
     * @var array<int, string>
     */
    public const QUERY_VARS = [self::VAR_DATE, self::VAR_GUESTS, self::VAR_DESTINATION];

    /**
     * Raw date / guests values taken from the parsed request.
     *
     * @var array<string, mixed>
     */
    private static array $captured = [];

    /**
     * Hook the query vars. Call once while the plugin boots (never at file scope).
     *
     * @return void
     */
    public static function register(): void
    {
        add_filter('query_vars', [self::class, 'addQueryVars']);
        add_action('parse_request', [self::class, 'capture']);
    }

    /**
     * Register the public query vars.
     *
     * @param mixed $vars Public query vars.
     * @return array<int, string>
     */
    public static function addQueryVars($vars): array
    {
        $vars = is_array($vars) ? $vars : [];

        return array_values(array_unique(array_merge($vars, self::QUERY_VARS)));
    }

    /**
     * Keep the date and guest count of the parsed request and take them out of the main query.
     *
     * @param mixed $wp The WP request object (`parse_request` passes it).
     * @return void
     */
    public static function capture($wp): void
    {
        if (!is_object($wp) || !isset($wp->query_vars) || !is_array($wp->query_vars)) {
            return;
        }

        foreach ([self::VAR_DATE, self::VAR_GUESTS] as $var) {
            if (array_key_exists($var, $wp->query_vars)) {
                self::$captured[$var] = $wp->query_vars[$var];
                unset($wp->query_vars[$var]);
            }
        }
    }

    /**
     * Searched day as Y-m-d, or '' when none (or not a valid date string) was sent.
     *
     * @return string
     */
    public static function date(): string
    {
        $date = self::scalar(self::VAR_DATE);
        if ($date === '') {
            return '';
        }

        $date = sanitize_text_field($date);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : '';
    }

    /**
     * Number of guests, between 1 and 50.
     *
     * @param int $fallback Value when the request has no guest count.
     * @return int
     */
    public static function guests(int $fallback = 1): int
    {
        if (!self::has(self::VAR_GUESTS)) {
            return max(1, min(50, $fallback));
        }

        return max(1, min(50, absint(self::scalar(self::VAR_GUESTS))));
    }

    /**
     * Destination slug, or '' when none was sent.
     *
     * @return string
     */
    public static function destination(): string
    {
        $slug = self::scalar(self::VAR_DESTINATION);

        return $slug === '' ? '' : sanitize_title($slug);
    }

    /**
     * Whether the request sent a date, even an empty or invalid one (the visitor used the search form).
     *
     * @return bool
     */
    public static function hasDate(): bool
    {
        return self::has(self::VAR_DATE);
    }

    /**
     * Whether the request sent a guest count.
     *
     * @return bool
     */
    public static function hasGuests(): bool
    {
        return self::has(self::VAR_GUESTS);
    }

    /**
     * Forget the captured values (tests, long-running processes).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$captured = [];
    }

    /**
     * Whether a query var is part of the parsed request.
     *
     * @param string $var Query var name.
     * @return bool
     */
    private static function has(string $var): bool
    {
        return self::raw($var) !== null;
    }

    /**
     * Raw value of a query var from the parsed request, null when it was not sent.
     *
     * @param string $var Query var name.
     * @return mixed
     */
    private static function raw(string $var)
    {
        if (array_key_exists($var, self::$captured)) {
            return self::$captured[$var];
        }

        global $wp;
        if (is_object($wp) && isset($wp->query_vars) && is_array($wp->query_vars) && array_key_exists($var, $wp->query_vars)) {
            return $wp->query_vars[$var];
        }

        return null;
    }

    /**
     * A query var as a plain string; arrays and other non-scalar input count as empty.
     *
     * @param string $var Query var name.
     * @return string
     */
    private static function scalar(string $var): string
    {
        $value = self::raw($var);
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? trim(wp_unslash((string) $value)) : '';
    }
}
