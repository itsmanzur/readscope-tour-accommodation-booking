<?php

declare(strict_types=1);

namespace Readscope\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TourPostType
 *
 * Registers the 'readscope_tour' custom post type and its taxonomies.
 *
 * @package Readscope\PostTypes
 */
class TourPostType
{
    public const POST_TYPE = 'readscope_tour';
    public const TAX_DESTINATION = 'readscope_destination';
    public const TAX_ACTIVITY = 'readscope_activity_type';
    public const TAX_FEATURE = 'readscope_tour_feature';

    /**
     * Register post type and taxonomies.
     *
     * @return void
     */
    public static function register(): void
    {
        self::registerTaxonomies();
        self::registerPostType();
    }

    /**
     * Register Custom Post Type.
     *
     * @return void
     */
    protected static function registerPostType(): void
    {
        $labels = [
            'name'                  => _x('Tours & Packages', 'Post type general name', 'the-readscope-tour-and-accommodation-booking'),
            'singular_name'         => _x('Tour', 'Post type singular name', 'the-readscope-tour-and-accommodation-booking'),
            'menu_name'             => _x('Tours', 'Admin Menu text', 'the-readscope-tour-and-accommodation-booking'),
            'name_admin_bar'        => _x('Tour', 'Add New on Toolbar', 'the-readscope-tour-and-accommodation-booking'),
            'add_new'               => __('Add New Tour', 'the-readscope-tour-and-accommodation-booking'),
            'add_new_item'          => __('Add New Tour', 'the-readscope-tour-and-accommodation-booking'),
            'new_item'              => __('New Tour', 'the-readscope-tour-and-accommodation-booking'),
            'edit_item'             => __('Edit Tour', 'the-readscope-tour-and-accommodation-booking'),
            'view_item'             => __('View Tour', 'the-readscope-tour-and-accommodation-booking'),
            'all_items'             => __('All Tours', 'the-readscope-tour-and-accommodation-booking'),
            'search_items'          => __('Search Tours', 'the-readscope-tour-and-accommodation-booking'),
            'not_found'             => __('No tours found.', 'the-readscope-tour-and-accommodation-booking'),
            'not_found_in_trash'    => __('No tours found in Trash.', 'the-readscope-tour-and-accommodation-booking'),
            'featured_image'        => _x('Tour Cover Image', 'Overrides the "Featured Image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'set_featured_image'    => _x('Set tour cover image', 'Overrides the "Set featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'remove_featured_image' => _x('Remove tour cover image', 'Overrides the "Remove featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'use_featured_image'    => _x('Use as tour cover image', 'Overrides the "Use as featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'readscope',
            'query_var'          => true,
            'rewrite'            => ['slug' => 'tours', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => 'tours',
            'hierarchical'       => false,
            'menu_position'      => 26,
            'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields'],
            'show_in_rest'       => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Register Taxonomies for Tours.
     *
     * @return void
     */
    protected static function registerTaxonomies(): void
    {
        // 1. Destination (Hierarchical)
        register_taxonomy(self::TAX_DESTINATION, [self::POST_TYPE, HotelPostType::POST_TYPE], [
            'hierarchical'      => true,
            'labels'            => [
                'name'              => _x('Destinations', 'taxonomy general name', 'the-readscope-tour-and-accommodation-booking'),
                'singular_name'     => _x('Destination', 'taxonomy singular name', 'the-readscope-tour-and-accommodation-booking'),
                'search_items'      => __('Search Destinations', 'the-readscope-tour-and-accommodation-booking'),
                'all_items'         => __('All Destinations', 'the-readscope-tour-and-accommodation-booking'),
                'parent_item'       => __('Parent Destination', 'the-readscope-tour-and-accommodation-booking'),
                'parent_item_colon' => __('Parent Destination:', 'the-readscope-tour-and-accommodation-booking'),
                'edit_item'         => __('Edit Destination', 'the-readscope-tour-and-accommodation-booking'),
                'update_item'       => __('Update Destination', 'the-readscope-tour-and-accommodation-booking'),
                'add_new_item'      => __('Add New Destination', 'the-readscope-tour-and-accommodation-booking'),
                'new_item_name'     => __('New Destination Name', 'the-readscope-tour-and-accommodation-booking'),
                'menu_name'         => __('Destinations', 'the-readscope-tour-and-accommodation-booking'),
            ],
            'show_ui'           => true,
            'show_in_menu'      => 'readscope',
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'destination', 'with_front' => false],
            'show_in_rest'      => true,
        ]);

        // 2. Activity Type (Non-hierarchical)
        register_taxonomy(self::TAX_ACTIVITY, [self::POST_TYPE], [
            'hierarchical'      => false,
            'labels'            => [
                'name'          => _x('Activities & Categories', 'taxonomy general name', 'the-readscope-tour-and-accommodation-booking'),
                'singular_name' => _x('Activity', 'taxonomy singular name', 'the-readscope-tour-and-accommodation-booking'),
                'search_items'  => __('Search Activities', 'the-readscope-tour-and-accommodation-booking'),
                'all_items'     => __('All Activities', 'the-readscope-tour-and-accommodation-booking'),
                'edit_item'     => __('Edit Activity', 'the-readscope-tour-and-accommodation-booking'),
                'update_item'   => __('Update Activity', 'the-readscope-tour-and-accommodation-booking'),
                'add_new_item'  => __('Add New Activity', 'the-readscope-tour-and-accommodation-booking'),
                'menu_name'     => __('Activities', 'the-readscope-tour-and-accommodation-booking'),
            ],
            'show_ui'           => true,
            'show_in_menu'      => 'readscope',
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'activity', 'with_front' => false],
            'show_in_rest'      => true,
        ]);

        // 3. Tour Features (Instant Confirmation, Free Cancellation, etc.)
        register_taxonomy(self::TAX_FEATURE, [self::POST_TYPE], [
            'hierarchical'      => false,
            'labels'            => [
                'name'          => _x('Tour Features', 'taxonomy general name', 'the-readscope-tour-and-accommodation-booking'),
                'singular_name' => _x('Feature', 'taxonomy singular name', 'the-readscope-tour-and-accommodation-booking'),
                'menu_name'     => __('Tour Features', 'the-readscope-tour-and-accommodation-booking'),
            ],
            'show_ui'           => true,
            'show_in_menu'      => 'readscope',
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'tour-feature', 'with_front' => false],
            'show_in_rest'      => true,
        ]);
    }
}
