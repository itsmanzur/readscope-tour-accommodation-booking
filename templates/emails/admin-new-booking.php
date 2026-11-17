<?php
/**
 * Admin: New Booking Notification Email Template Part
 *
 * @package Readscope Booking
 * @var array  $booking
 * @var string $siteName
 * @var string $additionalContent
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside a function; these variables are local.

$readscopeBooking = isset($booking) && is_array($booking) ? $booking : [];
$readscopeExtra   = isset($additionalContent) && is_string($additionalContent) ? $additionalContent : '';
$adminBookingsUrl = admin_url('admin.php?page=readscope-bookings');
$readscopeDate = static function (string $value): string {
    if ($value === '') {
        return '';
    }

    return function_exists('readscope_format_date') ? readscope_format_date($value) : $value;
};
$readscopeStatus = static function (string $value): string {
    $labels = [
        'pending'   => __('Pending', 'the-readscope-tour-and-accommodation-booking'),
        'confirmed' => __('Confirmed', 'the-readscope-tour-and-accommodation-booking'),
        'cancelled' => __('Cancelled', 'the-readscope-tour-and-accommodation-booking'),
        'paid'      => __('Paid', 'the-readscope-tour-and-accommodation-booking'),
        'unpaid'    => __('Unpaid', 'the-readscope-tour-and-accommodation-booking'),
        'partial'   => __('Partial', 'the-readscope-tour-and-accommodation-booking'),
        'refunded'  => __('Refunded', 'the-readscope-tour-and-accommodation-booking'),
    ];

    return $labels[strtolower($value)] ?? $value;
};
?>
<p style="font-size: 15px; margin-top: 0;">
    <strong><?php esc_html_e('Hello Administrator,', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
</p>
<p style="color: #475569; font-size: 14px;">
    <?php esc_html_e('A new reservation has just been submitted on your website. Here are the booking details:', 'the-readscope-tour-and-accommodation-booking'); ?>
</p>

<div class="readscope-info-card">
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Booking Code:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value" style="color: #0284c7;"><?php echo esc_html($readscopeBooking['booking_code'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></span>
    </div>
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Customer Name:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value"><?php echo esc_html($readscopeBooking['customer_name'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></span>
    </div>
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Customer Email:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value"><a href="mailto:<?php echo esc_attr($readscopeBooking['customer_email'] ?? ''); ?>"><?php echo esc_html($readscopeBooking['customer_email'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></a></span>
    </div>
    <?php if (!empty($readscopeBooking['customer_phone'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Customer Phone:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeBooking['customer_phone']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeBooking['item_title'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Item / Tour:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
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
        <span class="readscope-info-label"><?php esc_html_e('Travelers:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value">
            <?php echo esc_html((string) ($readscopeBooking['adults'] ?? 1)); ?> <?php esc_html_e('Adult(s)', 'the-readscope-tour-and-accommodation-booking'); ?>
            <?php if (!empty($readscopeBooking['children'])) : ?>
                , <?php echo esc_html((string) $readscopeBooking['children']); ?> <?php esc_html_e('Child(ren)', 'the-readscope-tour-and-accommodation-booking'); ?>
            <?php endif; ?>
            <?php if (!empty($readscopeBooking['infants'])) : ?>
                , <?php echo esc_html((string) $readscopeBooking['infants']); ?> <?php esc_html_e('Infant(s)', 'the-readscope-tour-and-accommodation-booking'); ?>
            <?php endif; ?>
        </span>
    </div>
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Booking Status:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value"><?php echo esc_html($readscopeStatus((string) ($readscopeBooking['booking_status'] ?? 'pending'))); ?></span>
    </div>
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Payment Status:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value"><?php echo esc_html($readscopeStatus((string) ($readscopeBooking['payment_status'] ?? 'pending'))); ?></span>
    </div>
    <div class="readscope-info-row" style="font-size: 15px;">
        <span class="readscope-info-label"><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value" style="color: #0f172a;"><?php echo esc_html($readscopeBooking['total_amount'] ?? '$0.00'); ?></span>
    </div>
    <?php if (!empty($readscopeBooking['customer_notes'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Customer Notes:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value" style="font-weight: normal;"><?php echo esc_html($readscopeBooking['customer_notes']); ?></span>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($readscopeExtra)) : ?>
    <div style="margin: 20px 0; font-size: 14px; color: #334155;">
        <?php echo wp_kses_post($readscopeExtra); ?>
    </div>
<?php endif; ?>

<div style="text-align: center; margin-top: 24px;">
    <a href="<?php echo esc_url($adminBookingsUrl); ?>" class="readscope-btn">
        📋 <?php esc_html_e('Manage Bookings in Admin', 'the-readscope-tour-and-accommodation-booking'); ?>
    </a>
</div>
