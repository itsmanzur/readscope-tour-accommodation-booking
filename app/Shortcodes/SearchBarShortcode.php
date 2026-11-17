<?php

declare(strict_types=1);

namespace Readscope\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class SearchBarShortcode
 *
 * Shortcode: [readscope_search_bar]
 *
 * @package Readscope\Shortcodes
 */
class SearchBarShortcode
{
    public static function register(): void
    {
        add_shortcode('readscope_search_bar', [self::class, 'render']);
    }

    /**
     * Render search bar.
     *
     * @param array<string, mixed>|string $atts
     * @return string
     */
    public static function render(array|string $atts = []): string
    {
        ob_start();
        readscope_get_template('search-bar.php');

        return ob_get_clean() ?: '';
    }
}
