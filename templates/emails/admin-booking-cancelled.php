<?php
/**
 * Admin: Booking Cancelled Notification Email Template Part
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

$tourivoBooking   = isset($booking) && is_array($booking) ? $booking : [];
$tourivoExtra     = isset($additionalContent) && is_string($additionalContent) ? $additionalContent : '';
$adminBookingsUrl = admin_url('admin.php?page=tourivo-bookings');
$tourivoDate = static function (string $value): string {
    if ($value === '') {
        return '';
    }

    return function_exists('tourivo_format_date') ? tourivo_format_date($value) : $value;
};
?>
<p style="font-size: 15px; margin-top: 0;">
    <strong><?php esc_html_e('Hello Administrator,', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
</p>
<p style="color: #475569; font-size: 14px;">
    <?php esc_html_e('A booking has been marked as Cancelled. The reserved inventory capacity has been automatically released back to the calendar.', 'the-readscope-tour-and-accommodation-booking'); ?>
</p>

<div class="tourivo-warning-box" style="background: #fef2f2; border-color: #fecaca; color: #991b1b;">
    <strong>❌ <?php esc_html_e('Booking Cancelled', 'the-readscope-tour-and-accommodation-booking'); ?></strong><br>
    <?php
    echo esc_html(
        sprintf(
            /* translators: 1: Booking Code, 2: Customer Name */
            __('Reservation #%1$s by %2$s is now cancelled.', 'the-readscope-tour-and-accommodation-booking'),
            $tourivoBooking['booking_code'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking'),
            $tourivoBooking['customer_name'] ?? __('Customer', 'the-readscope-tour-and-accommodation-booking')
        )
    );
    ?>
</div>

<div class="tourivo-info-card">
    <div class="tourivo-info-row">
        <span class="tourivo-info-label"><?php esc_html_e('Booking Code:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="tourivo-info-value" style="color: #0284c7;"><?php echo esc_html($tourivoBooking['booking_code'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></span>
    </div>
    <div class="tourivo-info-row">
        <span class="tourivo-info-label"><?php esc_html_e('Customer Name:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="tourivo-info-value"><?php echo esc_html($tourivoBooking['customer_name'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></span>
    </div>
    <div class="tourivo-info-row">
        <span class="tourivo-info-label"><?php esc_html_e('Customer Email:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="tourivo-info-value"><a href="mailto:<?php echo esc_attr($tourivoBooking['customer_email'] ?? ''); ?>"><?php echo esc_html($tourivoBooking['customer_email'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></a></span>
    </div>
    <?php if (!empty($tourivoBooking['item_title'])) : ?>
        <div class="tourivo-info-row">
            <span class="tourivo-info-label"><?php esc_html_e('Item / Tour:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="tourivo-info-value"><?php echo esc_html($tourivoBooking['item_title']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($tourivoBooking['check_in'])) : ?>
        <div class="tourivo-info-row">
            <span class="tourivo-info-label"><?php esc_html_e('Scheduled Check-in:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="tourivo-info-value"><?php echo esc_html($tourivoDate((string) $tourivoBooking['check_in'])); ?></span>
        </div>
    <?php endif; ?>
    <div class="tourivo-info-row">
        <span class="tourivo-info-label"><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="tourivo-info-value"><?php echo esc_html($tourivoBooking['total_amount'] ?? '$0.00'); ?></span>
    </div>
</div>

<?php if (!empty($tourivoExtra)) : ?>
    <div style="margin: 20px 0; font-size: 14px; color: #334155;">
        <?php echo wp_kses_post($tourivoExtra); ?>
    </div>
<?php endif; ?>

<div style="text-align: center; margin-top: 24px;">
    <a href="<?php echo esc_url($adminBookingsUrl); ?>" class="tourivo-btn">
        📋 <?php esc_html_e('Open Bookings Panel', 'the-readscope-tour-and-accommodation-booking'); ?>
    </a>
</div>
