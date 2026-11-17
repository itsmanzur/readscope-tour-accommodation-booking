<?php

declare(strict_types=1);

namespace Readscope\PostTypes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RoomPostType
 *
 * Registers the 'readscope_room' custom post type.
 *
 * @package Readscope\PostTypes
 */
class RoomPostType
{
    public const POST_TYPE = 'readscope_room';

    /**
     * Register post type.
     *
     * @return void
     */
    public static function register(): void
    {
        $labels = [
            'name'                  => _x('Rooms & Units', 'Post type general name', 'the-readscope-tour-and-accommodation-booking'),
            'singular_name'         => _x('Room', 'Post type singular name', 'the-readscope-tour-and-accommodation-booking'),
            'menu_name'             => _x('Rooms', 'Admin Menu text', 'the-readscope-tour-and-accommodation-booking'),
            'name_admin_bar'        => _x('Room', 'Add New on Toolbar', 'the-readscope-tour-and-accommodation-booking'),
            'add_new'               => __('Add New Room', 'the-readscope-tour-and-accommodation-booking'),
            'add_new_item'          => __('Add New Room', 'the-readscope-tour-and-accommodation-booking'),
            'new_item'              => __('New Room', 'the-readscope-tour-and-accommodation-booking'),
            'edit_item'             => __('Edit Room', 'the-readscope-tour-and-accommodation-booking'),
            'view_item'             => __('View Room', 'the-readscope-tour-and-accommodation-booking'),
            'all_items'             => __('All Rooms', 'the-readscope-tour-and-accommodation-booking'),
            'search_items'          => __('Search Rooms', 'the-readscope-tour-and-accommodation-booking'),
            'not_found'             => __('No rooms found.', 'the-readscope-tour-and-accommodation-booking'),
            'not_found_in_trash'    => __('No rooms found in Trash.', 'the-readscope-tour-and-accommodation-booking'),
            'featured_image'        => _x('Room Photo', 'Overrides the "Featured Image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'set_featured_image'    => _x('Set room photo', 'Overrides the "Set featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'remove_featured_image' => _x('Remove room photo', 'Overrides the "Remove featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
            'use_featured_image'    => _x('Use as room photo', 'Overrides the "Use as featured image" phrase', 'the-readscope-tour-and-accommodation-booking'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'readscope',
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'exclude_from_search'=> true,
            'menu_position'      => 28,
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'show_in_rest'       => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }
}
