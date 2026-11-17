<?php
/**
 * Room Meta Box View (Tabbed UI)
 *
 * @package Readscope Booking
 * @var WP_Post $post
 * @var int $parentHotelId
 * @var string $nightlyPrice
 * @var int $maxAdults
 * @var int $maxChildren
 * @var int $maxGuests
 * @var int $roomQuantity
 * @var string $roomSize
 * @var string $bedType
 * @var array<WP_Post> $hotels
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="readscope-metabox-wrapper">
    <div class="readscope-tabs-nav">
        <ul>
            <li class="active"><a href="#room-tab-pricing"><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('Pricing & Capacity', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <li><a href="#room-tab-specs"><span class="dashicons dashicons-admin-home"></span> <?php esc_html_e('Room Specs & Beds', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <?php do_action('readscope_room_metabox_tabs', $post); ?>
        </ul>
    </div>

    <div class="readscope-tabs-content">
        <!-- 1. Pricing & Capacity Tab -->
        <div id="room-tab-pricing" class="readscope-tab-pane active">
            <div class="readscope-form-group">
                <label for="_readscope_parent_hotel_id"><strong><?php esc_html_e('Select Parent Hotel / Property *', 'the-readscope-tour-and-accommodation-booking'); ?></strong></label>
                <select name="_readscope_parent_hotel_id" id="_readscope_parent_hotel_id" style="width: 100%; max-width: 480px;">
                    <option value=""><?php esc_html_e('-- Select Hotel --', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                    <?php foreach ($hotels as $readscopeHotelOption) : ?>
                        <option value="<?php echo esc_attr((string)$readscopeHotelOption->ID); ?>" <?php selected($parentHotelId, $readscopeHotelOption->ID); ?>>
                            <?php echo esc_html($readscopeHotelOption->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e('Select which hotel or resort property this room unit belongs to.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_nightly_price"><?php esc_html_e('Base Nightly Price ($) *', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" step="0.01" min="0" name="_readscope_nightly_price" id="_readscope_nightly_price" value="<?php echo esc_attr($nightlyPrice); ?>" placeholder="120.00">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_room_quantity"><?php esc_html_e('Total Units Available', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" min="1" name="_readscope_room_quantity" id="_readscope_room_quantity" value="<?php echo esc_attr((string)$roomQuantity); ?>">
                        <p class="description"><?php esc_html_e('Inventory capacity for this room type.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    </div>
                </div>
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_max_adults"><?php esc_html_e('Max Adults', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" min="1" name="_readscope_max_adults" id="_readscope_max_adults" value="<?php echo esc_attr((string)$maxAdults); ?>">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_max_children"><?php esc_html_e('Max Children', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" min="0" name="_readscope_max_children" id="_readscope_max_children" value="<?php echo esc_attr((string)$maxChildren); ?>">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_max_guests"><?php esc_html_e('Total Max Guests', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" min="1" name="_readscope_max_guests" id="_readscope_max_guests" value="<?php echo esc_attr((string)$maxGuests); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Room Specs & Beds Tab -->
        <div id="room-tab-specs" class="readscope-tab-pane">
            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_bed_type"><?php esc_html_e('Bed Configuration', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_bed_type" id="_readscope_bed_type" value="<?php echo esc_attr($bedType); ?>" placeholder="e.g. 1 King Bed or 2 Single Beds">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_room_size"><?php esc_html_e('Room Dimensions / Size', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_room_size" id="_readscope_room_size" value="<?php echo esc_attr($roomSize); ?>" placeholder="e.g. 35 m² / 376 ft²">
                    </div>
                </div>
            </div>
        </div>

        <?php do_action('readscope_room_metabox_panes', $post); ?>
    </div>
</div>
