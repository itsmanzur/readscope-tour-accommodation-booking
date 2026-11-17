<?php

declare(strict_types=1);

namespace Readscope\Blocks;

use Readscope\Shortcodes\BookingLookupShortcode;
use Readscope\Shortcodes\BookingPanelShortcode;
use Readscope\Shortcodes\CurrencySwitcherShortcode;
use Readscope\Shortcodes\FilterSearchShortcode;
use Readscope\Shortcodes\HotelGridShortcode;
use Readscope\Shortcodes\SearchBarShortcode;
use Readscope\Shortcodes\TourGridShortcode;
use Readscope\Shortcodes\WishlistShortcode;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BlockManager
 *
 * Registers server-rendered Gutenberg Core Blocks and Readscope Booking custom block category.
 *
 * @package Readscope\Blocks
 */
class BlockManager
{
    /**
     * Register Readscope Booking custom block category.
     *
     * @param array<int, array<string, mixed>> $categories
     * @return array<int, array<string, mixed>>
     */
    public static function registerBlockCategory(array $categories): array
    {
        return array_merge([
            [
                'slug'  => 'readscope',
                'title' => __('Readscope Travel Engine', 'the-readscope-tour-and-accommodation-booking'),
                'icon'  => 'palmtree',
            ],
        ], $categories);
    }

    /**
     * Register all Readscope Booking Gutenberg dynamic blocks.
     *
     * @return void
     */
    public static function registerBlocks(): void
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register custom category filter for WordPress 5.8+
        add_filter('block_categories_all', [self::class, 'registerBlockCategory']);

        // 1. Tour Grid Block
        register_block_type('readscope/tour-grid', [
            'api_version'     => 2,
            'title'           => __('Readscope Tour Grid', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'palmtree',
            'description'     => __('Display tours in a modern responsive grid layout.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [TourGridShortcode::class, 'render'],
            'attributes'      => [
                'count'       => ['type' => 'number', 'default' => 6],
                'columns'     => ['type' => 'number', 'default' => 3],
                'destination' => ['type' => 'string', 'default' => ''],
                'activity'    => ['type' => 'string', 'default' => ''],
            ],
        ]);

        // 2. Hotel Grid Block
        register_block_type('readscope/hotel-grid', [
            'api_version'     => 2,
            'title'           => __('Readscope Hotel Grid', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'building',
            'description'     => __('Display hotels and resorts in a responsive grid.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [HotelGridShortcode::class, 'render'],
            'attributes'      => [
                'count'       => ['type' => 'number', 'default' => 6],
                'columns'     => ['type' => 'number', 'default' => 3],
                'destination' => ['type' => 'string', 'default' => ''],
            ],
        ]);

        // 3. Search Bar Block
        register_block_type('readscope/search-bar', [
            'api_version'     => 2,
            'title'           => __('Readscope Search Bar', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'search',
            'description'     => __('Destination, date and traveler search filter bar.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [SearchBarShortcode::class, 'render'],
        ]);

        // 4. Live Filter Search Grid Block
        register_block_type('readscope/filter-search', [
            'api_version'     => 2,
            'title'           => __('Readscope Filter & Search Grid', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'filter',
            'description'     => __('Live AJAX filtering by destination, price slider, and duration.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [FilterSearchShortcode::class, 'render'],
            'attributes'      => [
                'type'    => ['type' => 'string', 'default' => 'tour'],
                'columns' => ['type' => 'number', 'default' => 3],
            ],
        ]);

        // 5. Booking Panel Block
        register_block_type('readscope/booking-panel', [
            'api_version'     => 2,
            'title'           => __('Readscope Booking Panel', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'calendar-alt',
            'description'     => __('Interactive booking panel with instant pricing and checkout.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [BookingPanelShortcode::class, 'render'],
            'attributes'      => [
                'item_id'   => ['type' => 'number', 'default' => 0],
                'item_type' => ['type' => 'string', 'default' => 'tour'],
            ],
        ]);

        // 6. Track Booking Portal Block
        register_block_type('readscope/booking-lookup', [
            'api_version'     => 2,
            'title'           => __('Readscope Track My Booking', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'tickets-alt',
            'description'     => __('Self-service portal for travelers to check reservation status and download vouchers.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [BookingLookupShortcode::class, 'render'],
        ]);

        // 7. Traveler Wishlist Block
        register_block_type('readscope/wishlist', [
            'api_version'     => 2,
            'title'           => __('Readscope Traveler Wishlist', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'heart',
            'description'     => __('Display saved favorite tours and hotels for the traveler.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [WishlistShortcode::class, 'render'],
            'attributes'      => [
                'columns' => ['type' => 'number', 'default' => 3],
            ],
        ]);

        // 8. Customer Account / My Bookings Block
        register_block_type('readscope/my-bookings', [
            'api_version'     => 2,
            'title'           => __('Readscope My Bookings Portal', 'the-readscope-tour-and-accommodation-booking'),
            'category'        => 'readscope',
            'icon'            => 'admin-users',
            'description'     => __('Display traveler dashboard with upcoming trips, completed itineraries, and self-management actions.', 'the-readscope-tour-and-accommodation-booking'),
            'render_callback' => [\Readscope\Shortcodes\MyBookingsShortcode::class, 'render'],
            'attributes'      => [
                'per_page'   => ['type' => 'number', 'default' => 10],
                'show_stats' => ['type' => 'string', 'default' => 'yes'],
            ],
        ]);
    }
}
