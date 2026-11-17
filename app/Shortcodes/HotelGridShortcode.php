<?php

declare(strict_types=1);

namespace Readscope\Shortcodes;

use Readscope\Models\Hotel;
use Readscope\PostTypes\HotelPostType;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HotelGridShortcode
 *
 * Shortcode: [readscope_hotels columns="3" count="6" destination="..."]
 *
 * @package Readscope\Shortcodes
 */
class HotelGridShortcode
{
    public static function register(): void
    {
        add_shortcode('readscope_hotels', [self::class, 'render']);
    }

    /**
     * Render hotel grid.
     *
     * @param array<string, mixed>|string $atts
     * @return string
     */
    public static function render(array|string $atts = []): string
    {
        $attributes = shortcode_atts([
            'count'       => 6,
            'columns'     => 3,
            'destination' => '',
            'orderby'     => 'date',
            'order'       => 'DESC',
        ], (array) $atts, 'readscope_hotels');

        $args = [
            'post_type'      => HotelPostType::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => (int) $attributes['count'],
            'orderby'        => sanitize_text_field((string) $attributes['orderby']),
            'order'          => sanitize_text_field((string) $attributes['order']),
        ];

        // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        if (!empty($attributes['destination'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'readscope_destination',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field((string) $attributes['destination']),
                ],
            ];
        }

        $query = new WP_Query($args);
        // phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_tax_query

        if (!$query->have_posts()) {
            return '<p class="readscope-no-items">' . esc_html__('No hotels found matching criteria.', 'the-readscope-tour-and-accommodation-booking') . '</p>';
        }

        $cols = max(1, min(4, (int) $attributes['columns']));

        ob_start();
        echo '<div class="readscope-grid" style="--trv-grid-cols: ' . esc_attr((string)$cols) . ';">';
        while ($query->have_posts()) {
            $query->the_post();
            $hotel = new Hotel(get_post());
            readscope_get_template('cards/hotel-card.php', ['hotel' => $hotel]);
        }
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean() ?: '';
    }
}
