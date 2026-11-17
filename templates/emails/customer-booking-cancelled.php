<?php
/**
 * Customer: Booking Cancelled Email Template Part
 *
 * @package Readscope Booking
 * @var array  $booking
 * @var string $siteName
 * @var string $lookupUrl
 * @var string $additionalContent
 */

if (!defined('ABSPATH')) {
    exit;
}

$readscopeBooking = isset($booking) && is_array($booking) ? $booking : [];
$readscopeLookup  = isset($lookupUrl) && is_string($lookupUrl) ? $lookupUrl : '';
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
    <?php esc_html_e('Your reservation has been cancelled. Below are the details of the cancelled booking for your reference:', 'the-readscope-tour-and-accommodation-booking'); ?>
</p>

<div class="readscope-warning-box" style="background: #fef2f2; border-color: #fecaca; color: #991b1b;">
    <strong>❌ <?php esc_html_e('Booking Status: Cancelled', 'the-readscope-tour-and-accommodation-booking'); ?></strong><br>
    <?php esc_html_e('This reservation has been cancelled and any reserved seats or rooms have been released.', 'the-readscope-tour-and-accommodation-booking'); ?>
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
            <span class="readscope-info-label"><?php esc_html_e('Scheduled Check-in:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeDate((string) $readscopeBooking['check_in'])); ?></span>
        </div>
    <?php endif; ?>
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value"><?php echo esc_html($readscopeBooking['total_amount'] ?? '$0.00'); ?></span>
    </div>
</div>

<?php if (!empty($readscopeExtra)) : ?>
    <div style="margin: 20px 0; font-size: 14px; color: #334155;">
        <?php echo wp_kses_post($readscopeExtra); ?>
    </div>
<?php endif; ?>

<p style="color: #64748b; font-size: 14px; margin-top: 20px;">
    <?php esc_html_e('If you have any questions or believe this cancellation was made in error, please feel free to reply directly to this email.', 'the-readscope-tour-and-accommodation-booking'); ?>
</p>
