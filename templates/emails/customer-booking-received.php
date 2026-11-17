<?php
/**
 * Customer: Booking Received (Pending) Email Template Part
 *
 * @package Readscope Booking
 * @var array  $booking
 * @var string $siteName
 * @var string $voucherUrl
 * @var string $lookupUrl
 * @var string $offlinePaymentInstructions
 * @var string $additionalContent
 */

if (!defined('ABSPATH')) {
    exit;
}

$readscopeBooking = isset($booking) && is_array($booking) ? $booking : [];
$readscopeLookup  = isset($lookupUrl) && is_string($lookupUrl) ? $lookupUrl : '';
$readscopePayment = isset($offlinePaymentInstructions) && is_string($offlinePaymentInstructions) ? $offlinePaymentInstructions : '';
$readscopeExtra   = isset($additionalContent) && is_string($additionalContent) ? $additionalContent : '';
$readscopeDate    = static function (string $value): string {
    if ($value === '') {
        return '';
    }

    return function_exists('readscope_format_date') ? readscope_format_date($value) : $value;
};
?>
<p style="font-size: 15px; margin-top: 0;">
    <?php
    echo esc_html(
        sprintf(
            /* translators: %s: Customer Name */
            __('Hello %s,', 'the-readscope-tour-and-accommodation-booking'),
            $readscopeBooking['customer_name'] ?? __('Traveler', 'the-readscope-tour-and-accommodation-booking')
        )
    );
    ?>
</p>
<p style="color: #475569; font-size: 14px;">
    <?php esc_html_e('Thank you for choosing us! We have received your booking request. Our team is reviewing the availability and will confirm your reservation shortly.', 'the-readscope-tour-and-accommodation-booking'); ?>
</p>

<div class="readscope-warning-box">
    <strong>⏳ <?php esc_html_e('Status: Pending Confirmation', 'the-readscope-tour-and-accommodation-booking'); ?></strong><br>
    <?php esc_html_e('Your reservation is currently on hold while we review your itinerary.', 'the-readscope-tour-and-accommodation-booking'); ?>
</div>

<div class="readscope-info-card">
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Booking Reference:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value" style="color: #0284c7;"><?php echo esc_html($readscopeBooking['booking_code'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></span>
    </div>
    <?php if (!empty($readscopeBooking['item_title'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Trip / Stay:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeBooking['item_title']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeBooking['check_in'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Check-in Date:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeDate((string) $readscopeBooking['check_in'])); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeBooking['check_out'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Check-out Date:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeDate((string) $readscopeBooking['check_out'])); ?></span>
        </div>
    <?php endif; ?>
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Travelers / Guests:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value">
            <?php echo esc_html((string)($readscopeBooking['adults'] ?? 1)); ?> <?php esc_html_e('Adult(s)', 'the-readscope-tour-and-accommodation-booking'); ?>
            <?php if (!empty($readscopeBooking['children'])) : ?>
                , <?php echo esc_html((string)$readscopeBooking['children']); ?> <?php esc_html_e('Child(ren)', 'the-readscope-tour-and-accommodation-booking'); ?>
            <?php endif; ?>
            <?php if (!empty($readscopeBooking['infants'])) : ?>
                , <?php echo esc_html((string)$readscopeBooking['infants']); ?> <?php esc_html_e('Infant(s)', 'the-readscope-tour-and-accommodation-booking'); ?>
            <?php endif; ?>
        </span>
    </div>
    <?php if (!empty($readscopeBooking['raw_discount']) && (float)$readscopeBooking['raw_discount'] > 0) : ?>
        <div class="readscope-info-row" style="color: #15803d;">
            <span class="readscope-info-label"><?php esc_html_e('Discount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value">-<?php echo esc_html($readscopeBooking['discount_amount']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeBooking['raw_tax']) && (float)$readscopeBooking['raw_tax'] > 0) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Tax / VAT:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeBooking['tax_amount']); ?></span>
        </div>
    <?php endif; ?>
    <div class="readscope-info-row" style="font-size: 15px;">
        <span class="readscope-info-label"><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value" style="color: #0f172a;"><?php echo esc_html($readscopeBooking['total_amount'] ?? '$0.00'); ?></span>
    </div>
</div>

<?php if (!empty($readscopePayment) && (empty($readscopeBooking['payment_status']) || $readscopeBooking['payment_status'] !== 'paid')) : ?>
    <div class="readscope-info-card" style="background: #eff6ff; border-color: #bfdbfe;">
        <h4 style="margin: 0 0 6px 0; color: #1e40af;"><?php esc_html_e('Payment Instructions', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
        <div style="font-size: 14px; color: #1e3a8a; line-height: 1.5;">
            <?php echo nl2br(esc_html($readscopePayment)); ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($readscopeExtra)) : ?>
    <div style="margin: 20px 0; font-size: 14px; color: #334155;">
        <?php echo wp_kses_post($readscopeExtra); ?>
    </div>
<?php endif; ?>

<?php if (!empty($readscopeLookup)) : ?>
    <div style="text-align: center; margin-top: 24px;">
        <a href="<?php echo esc_url($readscopeLookup); ?>" class="readscope-btn">
            🔍 <?php esc_html_e('Track Your Booking Online', 'the-readscope-tour-and-accommodation-booking'); ?>
        </a>
    </div>
<?php endif; ?>
