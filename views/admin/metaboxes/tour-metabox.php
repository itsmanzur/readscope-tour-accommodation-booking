<?php
/**
 * Tour Meta Box View (Tabbed UI)
 *
 * @package Readscope Booking
 * @var WP_Post $post
 * @var string $tourType
 * @var string $basePrice
 * @var string $salePrice
 * @var string $duration
 * @var int $minGuests
 * @var int $maxGuests
 * @var string $badge
 * @var string $pickupLocation
 * @var string $dropoffLocation
 * @var string $latitude
 * @var string $longitude
 * @var array $itinerary
 * @var array $inclusions
 * @var array $exclusions
 * @var array $faqs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="readscope-metabox-wrapper">
    <div class="readscope-tabs-nav">
        <ul>
            <li class="active"><a href="#tab-pricing"><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e('Pricing & General', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <li><a href="#tab-itinerary"><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e('Itinerary Builder', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <li><a href="#tab-inclusions"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Inclusions & Exclusions', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <li><a href="#tab-location"><span class="dashicons dashicons-location"></span> <?php esc_html_e('Location & Map', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <li><a href="#tab-faqs"><span class="dashicons dashicons-format-chat"></span> <?php esc_html_e('FAQs & Badge', 'the-readscope-tour-and-accommodation-booking'); ?></a></li>
            <?php do_action('readscope_tour_metabox_tabs', $post); ?>
        </ul>
    </div>

    <div class="readscope-tabs-content">
        <!-- 1. Pricing & General Tab -->
        <div id="tab-pricing" class="readscope-tab-pane active">
            <div class="readscope-form-group">
                <label for="_readscope_tour_type"><?php esc_html_e('Tour Type', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                <select name="_readscope_tour_type" id="_readscope_tour_type">
                    <option value="single_day" <?php selected($tourType, 'single_day'); ?>><?php esc_html_e('Single Day Tour', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                    <option value="multi_day" <?php selected($tourType, 'multi_day'); ?>><?php esc_html_e('Multi-Day Tour', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                    <option value="hourly" <?php selected($tourType, 'hourly'); ?>><?php esc_html_e('Hourly / Time Slot Activity', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                    <option value="fixed_departure" <?php selected($tourType, 'fixed_departure'); ?>><?php esc_html_e('Fixed Departure Tour', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                </select>
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_base_price"><?php esc_html_e('Base Price ($)', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" step="0.01" min="0" name="_readscope_base_price" id="_readscope_base_price" value="<?php echo esc_attr($basePrice); ?>" placeholder="199.00">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_sale_price"><?php esc_html_e('Sale / Offer Price ($)', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" step="0.01" min="0" name="_readscope_sale_price" id="_readscope_sale_price" value="<?php echo esc_attr($salePrice); ?>" placeholder="149.00">
                    </div>
                </div>
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_duration"><?php esc_html_e('Trip Duration Text', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_duration" id="_readscope_duration" value="<?php echo esc_attr($duration); ?>" placeholder="<?php esc_attr_e('e.g. 3 Days / 2 Nights', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_min_guests"><?php esc_html_e('Min Guests', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" min="1" name="_readscope_min_guests" id="_readscope_min_guests" value="<?php echo esc_attr($minGuests); ?>">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_max_guests"><?php esc_html_e('Max Group Size', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" min="1" name="_readscope_max_guests" id="_readscope_max_guests" value="<?php echo esc_attr($maxGuests); ?>">
                        <p class="description"><?php esc_html_e('Maximum travelers in a single booking.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_daily_capacity"><?php esc_html_e('Seats per Departure', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" min="0" name="_readscope_daily_capacity" id="_readscope_daily_capacity" value="<?php echo esc_attr($dailyCapacity); ?>" placeholder="<?php esc_attr_e('Same as group size', 'the-readscope-tour-and-accommodation-booking'); ?>">
                        <p class="description"><?php esc_html_e('Total seats sold per date across all bookings. Leave empty to use the group size.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    </div>
                </div>
            </div>

            <div class="readscope-form-group" style="margin-top: 8px;">
                <label for="_readscope_allow_free_booking">
                    <input type="checkbox" name="_readscope_allow_free_booking" id="_readscope_allow_free_booking" value="1" <?php checked($allowFree); ?>>
                    <?php esc_html_e('Free / no-charge tour (allow bookings with a total of 0)', 'the-readscope-tour-and-accommodation-booking'); ?>
                </label>
            </div>

            <hr style="border:0; border-top:1px solid #e5e7eb; margin: 20px 0;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:600;"><?php esc_html_e('Child & Infant Pricing', 'the-readscope-tour-and-accommodation-booking'); ?></h4>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_child_price_type"><?php esc_html_e('Child Price Rule', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <select name="_readscope_child_price_type" id="_readscope_child_price_type">
                            <option value="full" <?php selected($childPriceType, 'full'); ?>><?php esc_html_e('Full Adult Price (100%)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                            <option value="percent" <?php selected($childPriceType, 'percent'); ?>><?php esc_html_e('Percentage of Adult Price (%)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                            <option value="fixed" <?php selected($childPriceType, 'fixed'); ?>><?php esc_html_e('Fixed Price per Child', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                            <option value="free" <?php selected($childPriceType, 'free'); ?>><?php esc_html_e('Free for Children', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_child_price_value"><?php esc_html_e('Child Value (% or Fixed $)', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="number" step="0.01" min="0" name="_readscope_child_price_value" id="_readscope_child_price_value" value="<?php echo esc_attr($childPriceValue); ?>" placeholder="e.g. 50 (for 50%) or 35.00">
                    </div>
                </div>
            </div>

            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_child_age_label"><?php esc_html_e('Child Age Range Label', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_child_age_label" id="_readscope_child_age_label" value="<?php echo esc_attr($childAgeLabel); ?>" placeholder="<?php esc_attr_e('e.g. Age 2-11', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_infants_free"><?php esc_html_e('Infants Free of Charge?', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <select name="_readscope_infants_free" id="_readscope_infants_free">
                            <option value="yes" <?php selected($infantsFree, 'yes'); ?>><?php esc_html_e('Yes (Free / No Seat Fee)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                            <option value="no" <?php selected($infantsFree, 'no'); ?>><?php esc_html_e('No (Charge Adult Price)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Itinerary Builder Tab -->
        <div id="tab-itinerary" class="readscope-tab-pane">
            <p class="description"><?php esc_html_e('Add day-by-day plan and activities for this tour.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            <div id="readscope-itinerary-repeater" class="readscope-repeater-container">
                <?php if (!empty($itinerary)) : ?>
                    <?php foreach ($itinerary as $readscopeItineraryIndex => $readscopeItineraryItem) : ?>
                        <div class="readscope-repeater-row">
                            <div class="row-header">
                                <strong><?php
                                echo esc_html(
                                    sprintf(
                                        /* translators: %s: Day number or stage title */
                                        __('Day / Stage: %s', 'the-readscope-tour-and-accommodation-booking'),
                                        $readscopeItineraryItem['day'] ?? ($readscopeItineraryIndex + 1)
                                    )
                                );
                                ?></strong>
                                <button type="button" class="button remove-row-btn">&times;</button>
                            </div>
                            <div class="row-body">
                                <div class="readscope-row">
                                    <div class="readscope-col" style="flex: 0 0 100px;">
                                        <label><?php esc_html_e('Day #', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                                        <input type="text" name="_readscope_itinerary[<?php echo esc_attr((string)$readscopeItineraryIndex); ?>][day]" value="<?php echo esc_attr($readscopeItineraryItem['day'] ?? ($readscopeItineraryIndex + 1)); ?>">
                                    </div>
                                    <div class="readscope-col">
                                        <label><?php esc_html_e('Day Title', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                                        <input type="text" name="_readscope_itinerary[<?php echo esc_attr((string)$readscopeItineraryIndex); ?>][title]" value="<?php echo esc_attr($readscopeItineraryItem['title'] ?? ''); ?>" placeholder="<?php esc_attr_e('e.g. Arrival in Bali & Beach Walk', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                    </div>
                                    <div class="readscope-col" style="flex: 0 0 160px;">
                                        <label><?php esc_html_e('Included Meals', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                                        <input type="text" name="_readscope_itinerary[<?php echo esc_attr((string)$readscopeItineraryIndex); ?>][meals]" value="<?php echo esc_attr($readscopeItineraryItem['meals'] ?? ''); ?>" placeholder="<?php esc_attr_e('Breakfast, Dinner', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                    </div>
                                </div>
                                <div class="readscope-form-group" style="margin-top: 10px;">
                                    <label><?php esc_html_e('Description & Details', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                                    <textarea rows="3" name="_readscope_itinerary[<?php echo esc_attr((string)$readscopeItineraryIndex); ?>][desc]"><?php echo esc_textarea($readscopeItineraryItem['desc'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="readscope-add-itinerary-btn" class="button button-primary">+ <?php esc_html_e('Add Day to Itinerary', 'the-readscope-tour-and-accommodation-booking'); ?></button>
        </div>

        <!-- 3. Inclusions & Exclusions Tab -->
        <div id="tab-inclusions" class="readscope-tab-pane">
            <div class="readscope-row">
                <div class="readscope-col">
                    <h3><?php esc_html_e('Included in Tour', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                    <div id="readscope-inclusions-list" class="readscope-list-repeater">
                        <?php if (!empty($inclusions)) : foreach ($inclusions as $readscopeIncItem) : ?>
                            <div class="list-item-row">
                                <span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span>
                                <input type="text" name="_readscope_inclusions[]" value="<?php echo esc_attr($readscopeIncItem); ?>">
                                <button type="button" class="button remove-list-item">&times;</button>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <button type="button" id="readscope-add-inclusion-btn" class="button">+ <?php esc_html_e('Add Included Item', 'the-readscope-tour-and-accommodation-booking'); ?></button>
                </div>

                <div class="readscope-col">
                    <h3><?php esc_html_e('Excluded from Tour', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                    <div id="readscope-exclusions-list" class="readscope-list-repeater">
                        <?php if (!empty($exclusions)) : foreach ($exclusions as $readscopeExcItem) : ?>
                            <div class="list-item-row">
                                <span class="dashicons dashicons-dismiss" style="color: #ef4444;"></span>
                                <input type="text" name="_readscope_exclusions[]" value="<?php echo esc_attr($readscopeExcItem); ?>">
                                <button type="button" class="button remove-list-item">&times;</button>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <button type="button" id="readscope-add-exclusion-btn" class="button">+ <?php esc_html_e('Add Excluded Item', 'the-readscope-tour-and-accommodation-booking'); ?></button>
                </div>
            </div>
        </div>

        <!-- 4. Location & Map Tab -->
        <div id="tab-location" class="readscope-tab-pane">
            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_pickup_location"><?php esc_html_e('Pickup Point / Meeting Place', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_pickup_location" id="_readscope_pickup_location" value="<?php echo esc_attr($pickupLocation); ?>" placeholder="<?php esc_attr_e('e.g. Airport Terminal 1 or Hotel Lobby', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_dropoff_location"><?php esc_html_e('Drop-off Location', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_dropoff_location" id="_readscope_dropoff_location" value="<?php echo esc_attr($dropoffLocation); ?>" placeholder="<?php esc_attr_e('e.g. Central City Station', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    </div>
                </div>
            </div>
            <div class="readscope-row">
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_latitude"><?php esc_html_e('Latitude (GPS)', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_latitude" id="_readscope_latitude" value="<?php echo esc_attr($latitude); ?>" placeholder="e.g. -8.409518">
                    </div>
                </div>
                <div class="readscope-col">
                    <div class="readscope-form-group">
                        <label for="_readscope_longitude"><?php esc_html_e('Longitude (GPS)', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="_readscope_longitude" id="_readscope_longitude" value="<?php echo esc_attr($longitude); ?>" placeholder="e.g. 115.188916">
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. FAQs & Badge Tab -->
        <div id="tab-faqs" class="readscope-tab-pane">
            <div class="readscope-form-group">
                <label for="_readscope_badge"><?php esc_html_e('Highlight Ribbon / Badge', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                <input type="text" name="_readscope_badge" id="_readscope_badge" value="<?php echo esc_attr($badge); ?>" placeholder="<?php esc_attr_e('e.g. Bestseller, 20% Off, Top Rated', 'the-readscope-tour-and-accommodation-booking'); ?>">
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

            <h3><?php esc_html_e('Frequently Asked Questions (FAQs)', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
            <div id="readscope-faqs-list" class="readscope-repeater-container">
                <?php if (!empty($faqs)) : foreach ($faqs as $readscopeFaqIdx => $readscopeFaqItem) : ?>
                    <div class="readscope-repeater-row">
                        <div class="row-header">
                            <strong><?php esc_html_e('Question & Answer', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                            <button type="button" class="button remove-row-btn">&times;</button>
                        </div>
                        <div class="row-body">
                            <div class="readscope-form-group">
                                <label><?php esc_html_e('Question', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                                <input type="text" name="_readscope_faqs[<?php echo esc_attr((string)$readscopeFaqIdx); ?>][question]" value="<?php echo esc_attr($readscopeFaqItem['question'] ?? ''); ?>">
                            </div>
                            <div class="readscope-form-group">
                                <label><?php esc_html_e('Answer', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                                <textarea rows="2" name="_readscope_faqs[<?php echo esc_attr((string)$readscopeFaqIdx); ?>][answer]"><?php echo esc_textarea($readscopeFaqItem['answer'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <button type="button" id="readscope-add-faq-btn" class="button">+ <?php esc_html_e('Add FAQ Question', 'the-readscope-tour-and-accommodation-booking'); ?></button>
        </div>

        <?php do_action('readscope_tour_metabox_panes', $post); ?>
    </div>
</div>
