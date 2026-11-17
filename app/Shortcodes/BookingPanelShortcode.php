<?php

declare(strict_types=1);

namespace Readscope\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BookingPanelShortcode
 *
 * Shortcode: [readscope_booking_panel item_id="..." item_type="tour|hotel_room"]
 *
 * @package Readscope\Shortcodes
 */
class BookingPanelShortcode
{
    public static function register(): void
    {
        add_shortcode('readscope_booking_panel', [self::class, 'render']);
    }

    /**
     * Render shortcode output.
     *
     * @param array<string, mixed>|string $atts
     * @return string
     */
    public static function render(array|string $atts = []): string
    {
        $attributes = shortcode_atts([
            'item_id'   => get_the_ID(),
            'item_type' => 'tour',
        ], (array) $atts, 'readscope_booking_panel');

        $itemId   = (int) $attributes['item_id'];
        $itemType = sanitize_text_field((string) $attributes['item_type']);

        if ($itemId <= 0) {
            return '';
        }

        ob_start();
        readscope_get_template('booking-panel.php', [
            'readscopeItemId'   => $itemId,
            'readscopeItemType' => $itemType,
        ]);

        return ob_get_clean() ?: '';
    }
}
