<?php
/**
 * Readscope Booking Printable Booking Voucher & Invoice Template
 *
 * @package Readscope Booking
 * @var object $tourivoBooking
 * @var array $tourivoItems
 * @var string $tourivoCurrencySymbol
 * @var string $tourivoSiteName
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside a function; these variables are local.

use Tourivo\Support\Money;

$tourivoCurrency = !empty($tourivoCurrencySymbol) ? $tourivoCurrencySymbol : '$';
$tourivoSite     = !empty($tourivoSiteName) ? $tourivoSiteName : get_bloginfo('name');
$tourivoStatus   = ucfirst((string) $tourivoBooking->booking_status);
$tourivoPayment  = ucfirst((string) $tourivoBooking->payment_status);
$tourivoMethod   = ucwords(str_replace('_', ' ', (string) $tourivoBooking->payment_method));
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_locale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        /* translators: 1: Booking Reference Code, 2: Site Name */
        echo esc_html(sprintf(__('Booking Voucher #%1$s — %2$s', 'the-readscope-tour-and-accommodation-booking'), $tourivoBooking->booking_code, $tourivoSite)); 
    ?></title>
    <?php
    // A standalone print page: print Readscope Booking's fonts (Anek Bangla) here instead of through wp_head().
    \Tourivo\Support\Typography::enqueue();
    wp_print_styles(\Tourivo\Support\Typography::HANDLE);
    ?>
    <?php \Tourivo\Support\PortalAssets::enqueue('voucher'); wp_print_styles(\Tourivo\Support\PortalAssets::handle('voucher')); ?>
</head>
<body>

    <div class="action-bar">
        <button class="btn btn-close" data-tourivo-action="close">← <?php esc_html_e('Close', 'the-readscope-tour-and-accommodation-booking'); ?></button>
        <button class="btn btn-print" data-tourivo-action="print">🖨️ <?php esc_html_e('Print Booking Voucher', 'the-readscope-tour-and-accommodation-booking'); ?></button>
    </div>

    <div class="voucher-container">
        <!-- Top Header -->
        <div class="voucher-top-bar">
            <div class="agency-brand">
                <h1><?php echo esc_html($tourivoSite); ?></h1>
                <p><?php esc_html_e('Official Travel & Accommodation Booking Confirmation Voucher', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>
            <div class="voucher-meta">
                <span class="voucher-badge-title"><?php esc_html_e('Booking Reference', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <div class="voucher-code">#<?php echo esc_html($tourivoBooking->booking_code); ?></div>
                <div class="voucher-date"><?php 
                    /* translators: %s: Date and time of booking */
                    echo esc_html(sprintf(__('Issued: %s', 'the-readscope-tour-and-accommodation-booking'), gmdate('M d, Y H:i', strtotime((string)$tourivoBooking->created_at)))); 
                ?></div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-block">
                <h3>👤 <?php esc_html_e('Traveler Details', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <p><strong><?php esc_html_e('Name:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($tourivoBooking->customer_name); ?></p>
                <p><strong><?php esc_html_e('Email:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($tourivoBooking->customer_email); ?></p>
                <?php if (!empty($tourivoBooking->customer_phone)) : ?>
                    <p><strong><?php esc_html_e('Phone:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($tourivoBooking->customer_phone); ?></p>
                <?php endif; ?>
            </div>

            <div class="info-block">
                <h3>📋 <?php esc_html_e('Reservation Status', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <p><strong><?php esc_html_e('Booking Status:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> 
                    <span class="status-pill status-<?php echo esc_attr(strtolower($tourivoStatus)); ?>">
                        <?php echo esc_html($tourivoStatus); ?>
                    </span>
                </p>
                <p><strong><?php esc_html_e('Payment Status:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> 
                    <span class="status-pill status-<?php echo esc_attr(strtolower($tourivoPayment)); ?>">
                        <?php echo esc_html($tourivoPayment); ?>
                    </span>
                </p>
                <p><strong><?php esc_html_e('Payment Method:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($tourivoMethod); ?></p>
            </div>
        </div>

        <!-- Booked Line Items -->
        <table class="table-items">
            <thead>
                <tr>
                    <th><?php esc_html_e('Trip / Stay Description', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                    <th><?php esc_html_e('Travel / Check-in Dates', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                    <th style="text-align:center;"><?php esc_html_e('Qty / Pax', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                    <th style="text-align:right;"><?php esc_html_e('Total', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tourivoItems)) : foreach ($tourivoItems as $tourivoItem) : 
                    $tourivoCheckIn  = !empty($tourivoItem->check_in) ? gmdate('M d, Y', strtotime((string)$tourivoItem->check_in)) : '—';
                    $tourivoCheckOut = !empty($tourivoItem->check_out) ? gmdate('M d, Y', strtotime((string)$tourivoItem->check_out)) : '';
                    $tourivoDates    = $tourivoCheckOut ? "{$tourivoCheckIn} → {$tourivoCheckOut}" : $tourivoCheckIn;
                    $tourivoTypeTag  = ($tourivoItem->item_type === 'room' || $tourivoItem->item_type === 'hotel_room') ? esc_html__('Hotel Room', 'the-readscope-tour-and-accommodation-booking') : esc_html__('Tour Package', 'the-readscope-tour-and-accommodation-booking');
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($tourivoItem->item_title); ?></strong>
                            <br><span class="item-type-tag"><?php echo esc_html($tourivoTypeTag); ?></span>
                        </td>
                        <td><?php echo esc_html($tourivoDates); ?></td>
                        <td style="text-align:center;"><?php echo esc_html((string)$tourivoItem->quantity); ?></td>
                        <td style="text-align:right; font-weight:600;">
                            <?php echo esc_html(Money::format((float) $tourivoItem->total_price, $tourivoCurrency)); ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- Financial Summary -->
        <div class="financial-summary">
            <div class="summary-box">
                <?php
                $subtotalAmt = (float) $tourivoBooking->total_amount - (float) ($tourivoBooking->tax_amount ?? 0) + (float) ($tourivoBooking->discount_amount ?? 0);
                if ((float) ($tourivoBooking->discount_amount ?? 0) > 0 || (float) ($tourivoBooking->tax_amount ?? 0) > 0) : ?>
                    <div class="summary-row">
                        <span><?php esc_html_e('Subtotal:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span><?php echo esc_html(Money::format($subtotalAmt, $tourivoCurrency)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ((float) ($tourivoBooking->discount_amount ?? 0) > 0) : ?>
                    <div class="summary-row" style="color: #15803d;">
                        <span><?php esc_html_e('Discount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span>-<?php echo esc_html(Money::format((float) $tourivoBooking->discount_amount, $tourivoCurrency)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ((float) ($tourivoBooking->tax_amount ?? 0) > 0) : ?>
                    <div class="summary-row">
                        <span><?php esc_html_e('Tax / VAT:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span><?php echo esc_html(Money::format((float) $tourivoBooking->tax_amount, $tourivoCurrency)); ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    <span><?php echo esc_html(Money::format((float) $tourivoBooking->total_amount, $tourivoCurrency)); ?></span>
                </div>
                <?php if ((float) ($tourivoBooking->due_amount ?? 0) > 0) : ?>
                    <div class="summary-row" style="color: #b91c1c; font-weight: 600;">
                        <span><?php esc_html_e('Amount Due:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span><?php echo esc_html(Money::format((float) $tourivoBooking->due_amount, $tourivoCurrency)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Voucher Footer & Instructions -->
        <div class="voucher-footer">
            <p><strong><?php esc_html_e('Important Instructions for Travelers:', 'the-readscope-tour-and-accommodation-booking'); ?></strong></p>
            <p>• <?php esc_html_e('Please present this voucher along with a valid government-issued photo ID during hotel check-in or tour boarding.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            <p>• <?php esc_html_e('For inquiries, date changes, or support, please contact our team with your Booking Reference Code.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            <p style="margin-top: 8px;"><em><?php esc_html_e('Thank you for traveling with us!', 'the-readscope-tour-and-accommodation-booking'); ?></em></p>
        </div>
    </div>

<?php wp_print_scripts(\Tourivo\Support\PortalAssets::handle('voucher')); ?>
</body>
</html>
