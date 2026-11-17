<?php

declare(strict_types=1);

namespace Readscope\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HotelPostType
 *
 * Registers the 'readscope_hotel' custom post type and its taxonomies.
 *
 * @package Readscope\PostTypes
 */
class HotelPostType
{
    public const POST_TYPE = 'readscope_hotel';
    public const TAX_AMENITY = 'readscope_amenity';

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
            'name'                  => _x('Hotels & Stays', 'Post type general name', 'the-readscope-tour-and-accommodation-booking'),
            'singular_name'         => _x('Hotel', 'Post type singular name', 'the-readscope-tour-and-accommodation-booking'),
            'menu_name'             => _x('Hotels', 'Admin Menu text', 'the-readscope-tour-and-accommodation-booking'),
            'name_admin_bar'        => _x('Hotel', 'Add New on Toolbar', 'the-readscope-tour-and-accommodation-booking'),
            'add_new'               => __('Add New Hotel', 'the-readscope-tour-and-accommodation-booking'),
            'add_new_item'          => __('Add New Hotel', 'the-readscope-tour-and-accommodation-booking'),
            'new_item'              => __('New Hotel', 'the-readscope-tour-and-accommodation-booking'),
            'edit_item'             => __('Edit Hotel', 'the-readscope-tour-and-accommodation-booking'),
            'view_item'             => __('View Hotel', 'the-readscope-tour-and-accommodation-booking'),
            'all_items'             => __('All Hotels', 'the-readscope-tour-and-accommodation-booking'),
            'search_items'          => __('Search Hotels', 'the-readscope-tour-and-accommodation-booking'),
            'not_found'             => __('No hotels found.', 'the-readscope-tour-and-accommodation-booking'),
            'not_found_in_trash'    => __('No hotels found in Trash.', 'the-readscope-tour-and-accommodation-booking'),
            'featured_image'        => _x('Hotel Cover Image', 'Overrides the "Featured Image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'set_featured_image'    => _x('Set hotel cover image', 'Overrides the "Set featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'remove_featured_image' => _x('Remove hotel cover image', 'Overrides the "Remove featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'use_featured_image'    => _x('Use as hotel cover image', 'Overrides the "Use as featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'readscope',
            'query_var'          => true,
            'rewrite'            => ['slug' => 'hotels', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => 'hotels',
            'hierarchical'       => false,
            'menu_position'      => 27,
            'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields'],
            'show_in_rest'       => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Register Taxonomies for Hotels & Rooms.
     *
     * @return void
     */
    protected static function registerTaxonomies(): void
    {
        register_taxonomy(self::TAX_AMENITY, [self::POST_TYPE, RoomPostType::POST_TYPE], [
            'hierarchical'      => false,
            'labels'            => [
                'name'          => _x('Amenities', 'taxonomy general name', 'the-readscope-tour-and-accommodation-booking'),
                'singular_name' => _x('Amenity', 'taxonomy singular name', 'the-readscope-tour-and-accommodation-booking'),
                'search_items'  => __('Search Amenities', 'the-readscope-tour-and-accommodation-booking'),
                'all_items'     => __('All Amenities', 'the-readscope-tour-and-accommodation-booking'),
                'edit_item'     => __('Edit Amenity', 'the-readscope-tour-and-accommodation-booking'),
                'update_item'   => __('Update Amenity', 'the-readscope-tour-and-accommodation-booking'),
                'add_new_item'  => __('Add New Amenity', 'the-readscope-tour-and-accommodation-booking'),
                'menu_name'     => __('Amenities', 'the-readscope-tour-and-accommodation-booking'),
            ],
            'show_ui'           => true,
            'show_in_menu'      => 'readscope',
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'amenity', 'with_front' => false],
            'show_in_rest'      => true,
        ]);
    }
}
