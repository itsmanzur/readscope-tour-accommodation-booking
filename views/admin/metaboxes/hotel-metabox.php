<?php
/**
 * Hotel Meta Box View (Tabbed UI)
 *
 * @package Readscope Booking
 * @var WP_Post $post
 * @var int $starRating
 * @var string $address
 * @var string $city
 * @var string $postalCode
 * @var string $latitude
 * @var string $longitude
 * @var string $checkInTime
 * @var string $checkOutTime
 * @var string $phone
 * @var string $email
 * @var string $policy
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="readscope-metabox-wrapper">
    <div class="readscope-tabs-nav">
        <ul>
            <li class="active"><a href="#hotel-tab-general"><span class="dashicons dashicons-admin-home"></span> <?php esc_html_e('General & Rating', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <li><a href="#hotel-tab-location"><span class="dashicons dashicons-location"></span> <?php esc_html_e('Location & Contact', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <li><a href="#hotel-tab-policies"><span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Policies & Rules', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <?php do_action('readscope_hotel_metabox_tabs', $post); ?>
        </ul>
    </div>

    <div class="readscope-tabs-content">
        <!-- 1. General & Rating Tab -->
        <div id="hotel-tab-general" class="readscope-tab-pane active">
            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_star_rating"><?php esc_html_e('Star Rating *', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <select name="_readscope_star_rating" id="_readscope_star_rating">
                            <option value="1" <?php selected($starRating, 1); ?>>★ 1 Star</option>
                            <option value="2" <?php selected($starRating, 2); ?>>★★ 2 Stars</option>
                            <option value="3" <?php selected($starRating, 3); ?>>★★★ 3 Stars (Comfort)</option>
                            <option value="4" <?php selected($starRating, 4); ?>>★★★★ 4 Stars (First Class)</option>
                            <option value="5" <?php selected($starRating, 5); ?>>★★★★★ 5 Stars (Luxury Resort)</option>
                        </select>
                        <p class="description"><?php esc_html_e('Property official star rating category.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_city"><?php esc_html_e('City / Region', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_city" id="_readscope_city" value="<?php echo esc_attr($city); ?>" placeholder="e.g. Cox's Bazar / Bali / Paris">
                        <p class="description"><?php esc_html_e('City or neighborhood location.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    </div>
                </div>
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_check_in_time"><?php esc_html_e('Standard Check-in Time', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_check_in_time" id="_readscope_check_in_time" value="<?php echo esc_attr($checkInTime); ?>" placeholder="14:00">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_check_out_time"><?php esc_html_e('Standard Check-out Time', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_check_out_time" id="_readscope_check_out_time" value="<?php echo esc_attr($checkOutTime); ?>" placeholder="11:00">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Location & Contact Tab -->
        <div id="hotel-tab-location" class="readscope-tab-pane">
            <div class="readscope-form-group">
                <label for="_readscope_address"><?php esc_html_e('Full Street Address', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                <input type="text" name="_readscope_address" id="_readscope_address" value="<?php echo esc_attr($address); ?>" placeholder="e.g. Marine Drive Road, Kolatoli Beach">
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_phone"><?php esc_html_e('Contact Phone / WhatsApp', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_phone" id="_readscope_phone" value="<?php echo esc_attr($phone); ?>" placeholder="+880 1700-000000">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_email"><?php esc_html_e('Front Desk Contact Email', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="email" name="_readscope_email" id="_readscope_email" value="<?php echo esc_attr($email); ?>" placeholder="booking@hotel.com">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_postal_code"><?php esc_html_e('Postal / ZIP Code', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_postal_code" id="_readscope_postal_code" value="<?php echo esc_attr($postalCode); ?>" placeholder="4700">
                    </div>
                </div>
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_latitude"><?php esc_html_e('Map Latitude (GPS)', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_latitude" id="_readscope_latitude" value="<?php echo esc_attr($latitude); ?>" placeholder="21.4272">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_longitude"><?php esc_html_e('Map Longitude (GPS)', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_longitude" id="_readscope_longitude" value="<?php echo esc_attr($longitude); ?>" placeholder="91.9702">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Policies & Rules Tab -->
        <div id="hotel-tab-policies" class="readscope-tab-pane">
            <div class="readscope-form-group">
                <label for="_readscope_policy"><?php esc_html_e('Hotel Policies & House Rules', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                <textarea rows="6" name="_readscope_policy" id="_readscope_policy" placeholder="<?php esc_attr_e('e.g. Pets are not allowed. Valid Government ID card required at check-in. Cancellation allowed up to 48 hours before arrival.', 'the-readscope-tour-and-accommodation-booking'); ?>"><?php echo esc_textarea($policy); ?></textarea>
                <p class="description"><?php esc_html_e('These house rules will be displayed clearly to travelers before booking.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>
        </div>

        <?php do_action('readscope_hotel_metabox_panes', $post); ?>
    </div>
</div>
