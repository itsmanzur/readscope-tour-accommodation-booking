<?php

declare(strict_types=1);

namespace Readscope\Admin\MetaBoxes;

use Readscope\Common\Abstracts\MetaBox;
use Readscope\PostTypes\HotelPostType;
use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HotelMetaBox
 *
 * Handles Hotel settings metabox: Star rating, Address, Check-in/out times, Policies.
 *
 * @package Readscope\Admin\MetaBoxes
 */
class HotelMetaBox extends MetaBox
{
    protected string $id = 'readscope_hotel_settings';
    protected string $title = 'Hotel & Property Settings';
    protected string|array $postTypes = HotelPostType::POST_TYPE;
    protected string $context = 'normal';
    protected string $priority = 'high';

    public function getTitle(): string
    {
        return __('Hotel & Property Settings', 'the-readscope-tour-and-accommodation-booking');
    }

    public function render(WP_Post $post): void
    {
        $starRating   = get_post_meta($post->ID, '_readscope_star_rating', true) ?: '3';
        $address      = get_post_meta($post->ID, '_readscope_address', true) ?: '';
        $city         = get_post_meta($post->ID, '_readscope_city', true) ?: '';
        $postalCode   = get_post_meta($post->ID, '_readscope_postal_code', true) ?: '';
        $latitude     = get_post_meta($post->ID, '_readscope_latitude', true) ?: '';
        $longitude    = get_post_meta($post->ID, '_readscope_longitude', true) ?: '';
        $checkInTime  = get_post_meta($post->ID, '_readscope_check_in_time', true) ?: '14:00';
        $checkOutTime = get_post_meta($post->ID, '_readscope_check_out_time', true) ?: '11:00';
        $phone        = get_post_meta($post->ID, '_readscope_phone', true) ?: '';
        $email        = get_post_meta($post->ID, '_readscope_email', true) ?: '';
        $policy       = get_post_meta($post->ID, '_readscope_policy', true) ?: '';

        $this->loadView('admin/metaboxes/hotel-metabox.php', [
            'post'         => $post,
            'starRating'   => $starRating,
            'address'      => $address,
            'city'         => $city,
            'postalCode'   => $postalCode,
            'latitude'     => $latitude,
            'longitude'    => $longitude,
            'checkInTime'  => $checkInTime,
            'checkOutTime' => $checkOutTime,
            'phone'        => $phone,
            'email'        => $email,
            'policy'       => $policy,
        ]);
    }

    public function save(int $postId, WP_Post $post): void
    {
        // Nonce is verified by parent handleSave(), but documented here for PHPCS
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- MetaBox::handleSave() verifies the nonce and edit_post capability before save() runs.
        if (empty($_POST)) {
            return;
        }

        $fields = [
            '_readscope_star_rating'   => 'absint',
            '_readscope_address'       => 'sanitize_text_field',
            '_readscope_city'          => 'sanitize_text_field',
            '_readscope_postal_code'   => 'sanitize_text_field',
            '_readscope_latitude'      => 'sanitize_text_field',
            '_readscope_longitude'     => 'sanitize_text_field',
            '_readscope_check_in_time' => 'sanitize_text_field',
            '_readscope_check_out_time'=> 'sanitize_text_field',
            '_readscope_phone'         => 'sanitize_text_field',
            '_readscope_email'         => 'sanitize_email',
            '_readscope_policy'        => 'sanitize_textarea_field',
        ];

        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce and capability verified in MetaBox::handleSave(); each field is sanitized by its type below.
        foreach ($fields as $field => $sanitizer) {
            if (isset($_POST[$field])) {
                $val = wp_unslash($_POST[$field]);
                $cleanVal = $sanitizer($val);
                update_post_meta($postId, $field, $cleanVal);
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    }
}
