<?php
/**
 * Interactive Booking Panel Template
 *
 * @package Readscope Booking
 * @var int $itemId
 * @var string $itemType
 * @var float $basePrice
 * @var int $maxGuests
 * @var string $currencySymbol
 */

use Readscope\Support\Money;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside a function; these variables are local.

if (function_exists('readscope_load_plugin_textdomain')) {
    readscope_load_plugin_textdomain();
}

$readscopeItemId         = isset($readscopeItemId) ? (int) $readscopeItemId : (isset($itemId) ? (int) $itemId : (int) get_the_ID());
$readscopePostType       = get_post_type($readscopeItemId);
$readscopeItemType       = isset($readscopeItemType) ? (string) $readscopeItemType : (($readscopePostType === 'readscope_room') ? 'room' : 'tour');
if ($readscopeItemType === 'hotel_room') {
    $readscopeItemType = 'room';
}
$readscopeCurrencySymbol = (string) apply_filters('readscope/currency_symbol', '$');

if ($readscopeItemType === 'tour') {
    $readscopeTour          = new \Readscope\Models\Tour($readscopeItemId);
    $readscopeBasePrice     = $readscopeTour->getActivePrice();
    $readscopeMinGuests     = $readscopeTour->getMinGuests();
    $readscopeMaxGuests     = $readscopeTour->getMaxGuests() > 0 ? $readscopeTour->getMaxGuests() : 20;
    $readscopeChildAgeLabel = $readscopeTour->getChildAgeLabel() ?: __('Age 2-11', 'the-readscope-tour-and-accommodation-booking');
    $readscopeInfantsFree   = $readscopeTour->isInfantsFree();
    $readscopeMaxRooms      = 1;
} else {
    $readscopeRoom          = new \Readscope\Models\Room($readscopeItemId);
    $readscopeBasePrice     = $readscopeRoom->getNightlyPrice();
    $readscopeMinGuests     = 1;
    $readscopeMaxGuests     = $readscopeRoom->getMaxGuests() > 0 ? $readscopeRoom->getMaxGuests() : 4;
    $readscopeChildAgeLabel = __('Age 2-11', 'the-readscope-tour-and-accommodation-booking');
    $readscopeInfantsFree   = true;
    $readscopeMaxRooms      = $readscopeRoom->getQuantity() > 0 ? $readscopeRoom->getQuantity() : 10;
}
?>

<div class="readscope-booking-panel" id="readscope-booking-panel-<?php echo esc_attr((string) $readscopeItemId); ?>" data-item-id="<?php echo esc_attr((string) $readscopeItemId); ?>" data-item-type="<?php echo esc_attr($readscopeItemType); ?>" data-unit-price="<?php echo esc_attr((string) $readscopeBasePrice); ?>" data-currency="<?php echo esc_attr($readscopeCurrencySymbol); ?>" data-min-guests="<?php echo esc_attr((string) $readscopeMinGuests); ?>">
    <div class="panel-header">
        <div class="panel-price-box">
            <span class="price-label"><?php esc_html_e('Price:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="price-amount" id="readscope-live-price"><?php echo esc_html(Money::format($readscopeBasePrice)); ?></span>
            <span class="price-suffix"><?php echo ($readscopeItemType === 'tour') ? esc_html__('/ person', 'the-readscope-tour-and-accommodation-booking') : esc_html__('/ night', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        </div>
        <div class="availability-status" id="readscope-avail-status">
            <span class="status-badge status-check"><?php esc_html_e('Instant Confirmation', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        </div>
    </div>

    <form class="readscope-booking-form" id="readscope-booking-form">
        <!-- Anti-Spam Honeypot -->
        <div style="display:none !important; visibility:hidden !important; position:absolute; left:-9999px;">
            <input type="text" name="readscope_hp_check" value="" tabindex="-1" autocomplete="off">
        </div>

        <!-- Date Selection -->
        <div class="form-section">
            <label class="section-label"><?php echo ($readscopeItemType === 'tour') ? esc_html__('Select Tour Date *', 'the-readscope-tour-and-accommodation-booking') : esc_html__('Check-in & check-out *', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <p class="readscope-cal-summary" aria-live="polite"></p>
            <div class="readscope-cal" data-today="<?php echo esc_attr(wp_date('Y-m-d')); ?>">
                <div class="readscope-cal-nav">
                    <button type="button" class="readscope-cal-prev" aria-label="<?php esc_attr_e('Previous month', 'the-readscope-tour-and-accommodation-booking'); ?>">&#8249;</button>
                    <span class="readscope-cal-title"></span>
                    <button type="button" class="readscope-cal-next" aria-label="<?php esc_attr_e('Next month', 'the-readscope-tour-and-accommodation-booking'); ?>">&#8250;</button>
                </div>
                <div class="readscope-cal-weekdays"></div>
                <div class="readscope-cal-grid"></div>
                <p class="readscope-cal-note" aria-live="polite"></p>
            </div>
            <div class="readscope-date-fields">
                <input type="date" name="check_in" id="readscope-check-in" min="<?php echo esc_attr(wp_date('Y-m-d')); ?>" value="<?php echo esc_attr(wp_date('Y-m-d', strtotime('+1 day'))); ?>" required>
                <?php if ($readscopeItemType === 'room') : ?>
                    <input type="date" name="check_out" id="readscope-check-out" min="<?php echo esc_attr(wp_date('Y-m-d', strtotime('+1 day'))); ?>" value="<?php echo esc_attr(wp_date('Y-m-d', strtotime('+2 days'))); ?>" required>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($readscopeItemType === 'room') : ?>
            <!-- Room Units Counter -->
            <div class="form-section">
                <label class="section-label"><?php esc_html_e('Number of Rooms *', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                <div class="guest-counter-row">
                    <div class="guest-counter-item" style="width: 100%;">
                        <div class="guest-type">
                            <strong><?php esc_html_e('Rooms', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                            <small><?php
                            echo esc_html(
                                sprintf(
                                    /* translators: %d: Maximum rooms available */
                                    __('Up to %d available', 'the-readscope-tour-and-accommodation-booking'),
                                    $readscopeMaxRooms
                                )
                            );
                            ?></small>
                        </div>
                        <div class="counter-controls">
                            <button type="button" class="counter-btn minus-btn" data-target="rooms-count">-</button>
                            <input type="number" name="rooms" id="rooms-count" value="1" min="1" max="<?php echo esc_attr((string) $readscopeMaxRooms); ?>" readonly>
                            <button type="button" class="counter-btn plus-btn" data-target="rooms-count">+</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Guest Counters -->
        <div class="form-section">
            <label class="section-label"><?php esc_html_e('Guests / Travelers', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <div class="guest-counter-row">
                <div class="guest-counter-item">
                    <div class="guest-type">
                        <strong><?php esc_html_e('Adults', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                        <small><?php esc_html_e('Age 12+', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                    </div>
                    <div class="counter-controls">
                        <button type="button" class="counter-btn minus-btn" data-target="adults-count">-</button>
                        <input type="number" name="adults" id="adults-count" value="<?php echo esc_attr((string) max(1, $readscopeMinGuests)); ?>" min="1" max="<?php echo esc_attr((string) $readscopeMaxGuests); ?>" readonly>
                        <button type="button" class="counter-btn plus-btn" data-target="adults-count">+</button>
                    </div>
                </div>

                <div class="guest-counter-item">
                    <div class="guest-type">
                        <strong><?php esc_html_e('Children', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                        <small><?php echo esc_html($readscopeChildAgeLabel); ?></small>
                    </div>
                    <div class="counter-controls">
                        <button type="button" class="counter-btn minus-btn" data-target="children-count">-</button>
                        <input type="number" name="children" id="children-count" value="0" min="0" max="<?php echo esc_attr((string) $readscopeMaxGuests); ?>" readonly>
                        <button type="button" class="counter-btn plus-btn" data-target="children-count">+</button>
                    </div>
                </div>

                <div class="guest-counter-item">
                    <div class="guest-type">
                        <strong><?php esc_html_e('Infants', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                        <small><?php echo $readscopeInfantsFree ? esc_html__('Under 2 (Free)', 'the-readscope-tour-and-accommodation-booking') : esc_html__('Under 2', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                    </div>
                    <div class="counter-controls">
                        <button type="button" class="counter-btn minus-btn" data-target="infants-count">-</button>
                        <input type="number" name="infants" id="infants-count" value="0" min="0" max="<?php echo esc_attr((string) $readscopeMaxGuests); ?>" readonly>
                        <button type="button" class="counter-btn plus-btn" data-target="infants-count">+</button>
                    </div>
                </div>
            </div>
        </div>

        <?php do_action('readscope_booking_panel_after_guests', $readscopeItemId, $readscopeItemType); ?>

        <!-- Live Price Breakdown -->
        <div class="price-breakdown-box" id="readscope-price-breakdown" aria-live="polite">
            <div class="breakdown-items" id="readscope-breakdown-items">
                <div class="breakdown-row">
                    <span class="breakdown-desc" id="breakdown-calc-label"><?php echo esc_html(Money::format($readscopeBasePrice)); ?> &times; 1 <?php echo ($readscopeItemType === 'room') ? esc_html__('Night', 'the-readscope-tour-and-accommodation-booking') : esc_html__('Guest', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    <span class="breakdown-val" id="breakdown-total-val"><?php echo esc_html(Money::format($readscopeBasePrice)); ?></span>
                </div>
            </div>
            <div class="breakdown-row" id="breakdown-subtotal-row" style="display:none;">
                <span class="breakdown-desc"><?php esc_html_e('Subtotal', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="breakdown-val" id="breakdown-subtotal-val"><?php echo esc_html(Money::format($readscopeBasePrice)); ?></span>
            </div>
            <div class="breakdown-row" id="breakdown-discount-row" style="display:none; color:#15803d;">
                <span class="breakdown-desc" id="breakdown-discount-label"><?php esc_html_e('Discount', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="breakdown-val" id="breakdown-discount-val">-<?php echo esc_html(Money::format(0)); ?></span>
            </div>
            <div class="breakdown-row" id="breakdown-tax-row" style="display:none;">
                <span class="breakdown-desc" id="breakdown-tax-label"><?php esc_html_e('Tax', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="breakdown-val" id="breakdown-tax-val"><?php echo esc_html(Money::format(0)); ?></span>
            </div>
            <div class="breakdown-row total-row">
                <span><strong><?php esc_html_e('Total Amount', 'the-readscope-tour-and-accommodation-booking'); ?></strong></span>
                <span class="total-amount-val" id="live-grand-total"><strong><?php echo esc_html(Money::format($readscopeBasePrice)); ?></strong></span>
            </div>
        </div>

        <?php do_action('readscope_booking_panel_after_total', $readscopeItemId, $readscopeItemType); ?>

        <!-- Checkout Customer Details Accordion -->
        <div class="checkout-fields-box readscope-checkout-step" id="readscope-customer-details" hidden>
            <h4 class="checkout-fields-title"><?php esc_html_e('Lead Traveler Information', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
            <div class="field-row">
                <input type="text" name="customer_name" placeholder="<?php esc_attr_e('Full Name *', 'the-readscope-tour-and-accommodation-booking'); ?>" required>
            </div>
            <div class="field-row">
                <input type="email" name="customer_email" placeholder="<?php esc_attr_e('Email Address *', 'the-readscope-tour-and-accommodation-booking'); ?>" required>
            </div>
            <div class="field-row">
                <input type="tel" name="customer_phone" placeholder="<?php esc_attr_e('Phone / WhatsApp *', 'the-readscope-tour-and-accommodation-booking'); ?>" required>
            </div>
            <div class="field-row">
                <textarea name="customer_notes" rows="2" placeholder="<?php esc_attr_e('Special requests or notes...', 'the-readscope-tour-and-accommodation-booking'); ?>"></textarea>
            </div>
            <?php do_action('readscope_booking_panel_checkout_fields', $readscopeItemId, $readscopeItemType); ?>
            <?php if (\Readscope\Config\Config::get('require_consent', false)) : ?>
                <div class="field-row readscope-consent-row">
                    <input type="checkbox" name="consent" id="readscope-booking-consent-<?php echo esc_attr((string) $readscopeItemId); ?>" value="1" required>
                    <label for="readscope-booking-consent-<?php echo esc_attr((string) $readscopeItemId); ?>">
                        <?php echo wp_kses_post(\Readscope\Support\Privacy::getRenderedConsentLabel()); ?> <span class="required">*</span>
                    </label>
                </div>
            <?php endif; ?>

            <?php
            $paymentService = \Readscope\Common\Container::getInstance()->has(\Readscope\Services\PaymentService::class)
                ? \Readscope\Common\Container::getInstance()->get(\Readscope\Services\PaymentService::class)
                : new \Readscope\Services\PaymentService();
            $readscopePaymentOptions = [];
            foreach ($paymentService->getOfflinePaymentMethods() as $mId => $method) {
                $readscopePaymentOptions[] = [
                    'value'       => (string) $mId,
                    'title'       => (string) $method['title'],
                    'description' => (string) $method['description'],
                    'data'        => [],
                ];
            }
            /**
             * Payment choices on the booking panel. Add-ons that take payment themselves add their own entries
             * (value, title, description, and data-* attributes for their script).
             *
             * @param array<int, array{value: string, title: string, description: string, data: array<string, string>}> $readscopePaymentOptions
             */
            $readscopePaymentOptions = (array) apply_filters('readscope/booking/panel_payment_options', $readscopePaymentOptions, $readscopeItemId, $readscopeItemType);
            ?>
            <?php if (!empty($readscopePaymentOptions)) : ?>
                <fieldset class="field-row readscope-payment-options">
                    <legend class="readscope-payment-title"><?php esc_html_e('Payment Method', 'the-readscope-tour-and-accommodation-booking'); ?></legend>
                    <?php foreach (array_values($readscopePaymentOptions) as $readscopeIndex => $readscopeOption) : ?>
                        <label class="readscope-payment-option">
                            <input type="radio" name="payment_method" value="<?php echo esc_attr((string) ($readscopeOption['value'] ?? '')); ?>" <?php checked($readscopeIndex, 0); ?>
                                <?php foreach ((array) ($readscopeOption['data'] ?? []) as $readscopeDataKey => $readscopeDataValue) : ?>
                                    data-<?php echo esc_attr(sanitize_key((string) $readscopeDataKey)); ?>="<?php echo esc_attr((string) $readscopeDataValue); ?>"
                                <?php endforeach; ?>>
                            <span class="readscope-payment-text">
                                <strong><?php echo esc_html((string) ($readscopeOption['title'] ?? '')); ?></strong>
                                <?php if (!empty($readscopeOption['description'])) : ?>
                                    <small><?php echo esc_html((string) $readscopeOption['description']); ?></small>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                    <?php do_action('readscope_booking_panel_after_payment_options', $readscopeItemId, $readscopeItemType); ?>
                </fieldset>
            <?php endif; ?>
        </div>

        <?php do_action('readscope_booking_panel_before_submit', $readscopeItemId, $readscopeItemType); ?>

        <!-- Action Submit -->
        <button type="submit" class="readscope-btn readscope-btn-primary readscope-btn-block readscope-checkout-step" id="readscope-submit-btn" hidden>
            <span class="dashicons dashicons-lock"></span> <?php esc_html_e('Book Now (Pay Offline)', 'the-readscope-tour-and-accommodation-booking'); ?>
        </button>

        <!-- Trip Inquiry CTA -->
        <div class="readscope-inquiry-cta">
            <button type="button" class="readscope-btn-inquiry" id="readscope-open-inquiry-modal">
                💬 <?php esc_html_e('Have questions? Ask a Travel Specialist', 'the-readscope-tour-and-accommodation-booking'); ?>
            </button>
        </div>

        <div class="panel-alert" id="readscope-panel-alert" style="display: none;"></div>
    </form>

    <!-- Trip Inquiry Modal -->
    <div id="readscope-inquiry-modal" class="readscope-modal-wrap" style="display: none;">
        <div class="readscope-modal-overlay"></div>
        <div class="readscope-modal-content">
            <div class="modal-header">
                <h3>💬 <?php esc_html_e('Ask an Expert / Custom Trip Inquiry', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <button type="button" class="close-inquiry-modal">&times;</button>
            </div>
            <form id="readscope-inquiry-form">
                <!-- Anti-Spam Honeypot -->
                <div style="display:none !important; visibility:hidden !important; position:absolute; left:-9999px;">
                    <input type="text" name="readscope_inq_hp" value="" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="action" value="readscope_submit_inquiry">
                <input type="hidden" name="item_id" value="<?php echo esc_attr((string) $readscopeItemId); ?>">
                <input type="hidden" name="item_type" value="<?php echo esc_attr($readscopeItemType); ?>">
                <div class="modal-body">
                    <p class="modal-subtitle"><?php esc_html_e('Have special requests or group questions? Send your inquiry directly to our travel specialists.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    <div class="inquiry-field-row">
                        <input type="text" name="customer_name" placeholder="<?php esc_attr_e('Your Full Name *', 'the-readscope-tour-and-accommodation-booking'); ?>" required>
                    </div>
                    <div class="inquiry-field-row">
                        <input type="email" name="customer_email" placeholder="<?php esc_attr_e('Your Email Address *', 'the-readscope-tour-and-accommodation-booking'); ?>" required>
                    </div>
                    <div class="inquiry-field-row">
                        <input type="tel" name="customer_phone" placeholder="<?php esc_attr_e('Phone / WhatsApp Number', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    </div>
                    <div class="inquiry-field-row-grid">
                        <div>
                            <label><small><?php esc_html_e('Expected Travel Date', 'the-readscope-tour-and-accommodation-booking'); ?></small></label>
                            <input type="date" name="travel_date" min="<?php echo esc_attr(wp_date('Y-m-d')); ?>">
                        </div>
                        <div>
                            <label><small><?php esc_html_e('Travelers Count', 'the-readscope-tour-and-accommodation-booking'); ?></small></label>
                            <input type="number" name="guests" min="1" max="50" value="1">
                        </div>
                    </div>
                    <div class="inquiry-field-row">
                        <textarea name="message" rows="3" placeholder="<?php esc_attr_e('Tell us about your questions or custom trip preferences...', 'the-readscope-tour-and-accommodation-booking'); ?>" required></textarea>
                    </div>
                    <?php if (\Readscope\Config\Config::get('require_consent', false)) : ?>
                        <div class="inquiry-field-row readscope-consent-row">
                            <input type="checkbox" name="consent" id="readscope-inq-consent-<?php echo esc_attr((string) $readscopeItemId); ?>" value="1" required>
                            <label for="readscope-inq-consent-<?php echo esc_attr((string) $readscopeItemId); ?>">
                                <?php echo wp_kses_post(\Readscope\Support\Privacy::getRenderedConsentLabel()); ?> <span class="required">*</span>
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="inquiry-alert" id="readscope-inquiry-alert" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="readscope-btn readscope-btn-primary readscope-btn-block" id="readscope-inquiry-submit-btn">
                        ✈️ <?php esc_html_e('Send Inquiry to Specialists', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
