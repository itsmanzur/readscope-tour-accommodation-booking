<?php

declare(strict_types=1);

namespace Readscope\Shortcodes;

use Readscope\Models\Hotel;
use Readscope\Models\Tour;
use Readscope\PostTypes\HotelPostType;
use Readscope\PostTypes\TourPostType;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WishlistShortcode
 *
 * Shortcode: [readscope_wishlist columns="3"]
 *
 * @package Readscope\Shortcodes
 */
class WishlistShortcode
{
    public static function register(): void
    {
        add_shortcode('readscope_wishlist', [self::class, 'render']);
    }

    /**
     * Render Wishlist frontend container.
     *
     * @param array<string, mixed>|string $atts
     * @return string
     */
    public static function render(array|string $atts = []): string
    {
        $attributes = shortcode_atts([
            'columns' => 3,
        ], (array) $atts, 'readscope_wishlist');

        $cols = max(1, min(4, (int) $attributes['columns']));

        ob_start();
        ?>
        <div class="readscope-wishlist-wrap" data-columns="<?php echo esc_attr((string) $cols); ?>">
            <div class="readscope-wishlist-header">
                <h2>❤️ <?php esc_html_e('My Saved Trips & Stays', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                <p class="readscope-wishlist-count-label">
                    <span id="readscope-wishlist-total-count">0</span> <?php esc_html_e('items saved', 'the-readscope-tour-and-accommodation-booking'); ?>
                </p>
            </div>

            <!-- Loading Spinner -->
            <div id="readscope-wishlist-loading" class="readscope-loading-state" style="text-align: center; padding: 40px 0;">
                <span class="dashicons dashicons-update spin" style="font-size: 28px; width: 28px; height: 28px; color: #0284c7;"></span>
                <p style="margin-top: 10px; color: #64748b;"><?php esc_html_e('Loading your favorites...', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>

            <!-- Wishlist Grid -->
            <div id="readscope-wishlist-grid" class="readscope-grid" style="--trv-grid-cols: <?php echo esc_attr((string) $cols); ?>; display: none;"></div>

            <!-- Empty State -->
            <div id="readscope-wishlist-empty" class="readscope-empty-state" style="display: none; text-align: center; padding: 60px 20px;">
                <div style="font-size: var(--trv-text-display); margin-bottom: 16px;">🏖️</div>
                <h3><?php esc_html_e('Your Wishlist is Empty', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <p style="color: #64748b; max-width: 480px; margin: 0 auto 20px auto;">
                    <?php esc_html_e('Explore our exciting tour packages and luxury hotels, and click the heart icon to save your favorite itineraries here!', 'the-readscope-tour-and-accommodation-booking'); ?>
                </p>
                <a href="<?php echo esc_url(get_post_type_archive_link(TourPostType::POST_TYPE) ?: home_url('/')); ?>" class="readscope-btn readscope-btn-primary">
                    🔍 <?php esc_html_e('Explore Tours & Stays', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean() ?: '';
    }

    /**
     * AJAX handler to render cards for saved item IDs.
     *
     * @param array<int> $itemIds
     * @return string HTML cards
     */
    public static function renderCardsHtml(array $itemIds): string
    {
        if (empty($itemIds)) {
            return '';
        }

        $query = new \WP_Query([
            'post_type'      => [TourPostType::POST_TYPE, HotelPostType::POST_TYPE],
            'post__in'       => array_map('intval', $itemIds),
            'posts_per_page' => -1,
            'orderby'        => 'post__in',
        ]);

        if (!$query->have_posts()) {
            return '';
        }

        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post();
            if ($post->post_type === TourPostType::POST_TYPE) {
                $tour = new Tour($post);
                readscope_get_template('cards/tour-card.php', ['tour' => $tour]);
            } elseif ($post->post_type === HotelPostType::POST_TYPE) {
                $hotel = new Hotel($post);
                readscope_get_template('cards/hotel-card.php', ['hotel' => $hotel]);
            }
        }
        wp_reset_postdata();

        return ob_get_clean() ?: '';
    }
}
