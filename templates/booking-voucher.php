<?php
/**
 * Readscope Booking Printable Booking Voucher & Invoice Template
 *
 * @package Readscope Booking
 * @var object $readscopeBooking
 * @var array $readscopeItems
 * @var string $readscopeCurrencySymbol
 * @var string $readscopeSiteName
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside a function; these variables are local.

use Readscope\Support\Money;

$readscopeCurrency = !empty($readscopeCurrencySymbol) ? $readscopeCurrencySymbol : '$';
$readscopeSite     = !empty($readscopeSiteName) ? $readscopeSiteName : get_bloginfo('name');
$readscopeStatus   = ucfirst((string) $readscopeBooking->booking_status);
$readscopePayment  = ucfirst((string) $readscopeBooking->payment_status);
$readscopeMethod   = ucwords(str_replace('_', ' ', (string) $readscopeBooking->payment_method));
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_locale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        /* translators: 1: Booking Reference Code, 2: Site Name */
        echo esc_html(sprintf(__('Booking Voucher #%1$s — %2$s', 'the-readscope-tour-and-accommodation-booking'), $readscopeBooking->booking_code, $readscopeSite)); 
    ?></title>
    <?php
    // A standalone print page: print Readscope Booking's fonts (Anek Bangla) here instead of through wp_head().
    \Readscope\Support\Typography::enqueue();
    wp_print_styles(\Readscope\Support\Typography::HANDLE);
    ?>
    <?php \Readscope\Support\PortalAssets::enqueue('voucher'); wp_print_styles(\Readscope\Support\PortalAssets::handle('voucher')); ?>
</head>
<body>

    <div class="action-bar">
        <button class="btn btn-close" data-readscope-action="close">← <?php esc_html_e('Close', 'the-readscope-tour-and-accommodation-booking'); ?></button>
        <button class="btn btn-print" data-readscope-action="print">🖨️ <?php esc_html_e('Print Booking Voucher', 'the-readscope-tour-and-accommodation-booking'); ?></button>
    </div>

    <div class="voucher-container">
        <!-- Top Header -->
        <div class="voucher-top-bar">
            <div class="agency-brand">
                <h1><?php echo esc_html($readscopeSite); ?></h1>
                <p><?php esc_html_e('Official Travel & Accommodation Booking Confirmation Voucher', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>
            <div class="voucher-meta">
                <span class="voucher-badge-title"><?php esc_html_e('Booking Reference', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <div class="voucher-code">#<?php echo esc_html($readscopeBooking->booking_code); ?></div>
                <div class="voucher-date"><?php 
                    /* translators: %s: Date and time of booking */
                    echo esc_html(sprintf(__('Issued: %s', 'the-readscope-tour-and-accommodation-booking'), gmdate('M d, Y H:i', strtotime((string)$readscopeBooking->created_at)))); 
                ?></div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-block">
                <h3>👤 <?php esc_html_e('Traveler Details', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <p><strong><?php esc_html_e('Name:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($readscopeBooking->customer_name); ?></p>
                <p><strong><?php esc_html_e('Email:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($readscopeBooking->customer_email); ?></p>
                <?php if (!empty($readscopeBooking->customer_phone)) : ?>
                    <p><strong><?php esc_html_e('Phone:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($readscopeBooking->customer_phone); ?></p>
                <?php endif; ?>
            </div>

            <div class="info-block">
                <h3>📋 <?php esc_html_e('Reservation Status', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <p><strong><?php esc_html_e('Booking Status:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> 
                    <span class="status-pill status-<?php echo esc_attr(strtolower($readscopeStatus)); ?>">
                        <?php echo esc_html($readscopeStatus); ?>
                    </span>
                </p>
                <p><strong><?php esc_html_e('Payment Status:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> 
                    <span class="status-pill status-<?php echo esc_attr(strtolower($readscopePayment)); ?>">
                        <?php echo esc_html($readscopePayment); ?>
                    </span>
                </p>
                <p><strong><?php esc_html_e('Payment Method:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($readscopeMethod); ?></p>
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
                <?php if (!empty($readscopeItems)) : foreach ($readscopeItems as $readscopeItem) : 
                    $readscopeCheckIn  = !empty($readscopeItem->check_in) ? gmdate('M d, Y', strtotime((string)$readscopeItem->check_in)) : '—';
                    $readscopeCheckOut = !empty($readscopeItem->check_out) ? gmdate('M d, Y', strtotime((string)$readscopeItem->check_out)) : '';
                    $readscopeDates    = $readscopeCheckOut ? "{$readscopeCheckIn} → {$readscopeCheckOut}" : $readscopeCheckIn;
                    $readscopeTypeTag  = ($readscopeItem->item_type === 'room' || $readscopeItem->item_type === 'hotel_room') ? esc_html__('Hotel Room', 'the-readscope-tour-and-accommodation-booking') : esc_html__('Tour Package', 'the-readscope-tour-and-accommodation-booking');
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($readscopeItem->item_title); ?></strong>
                            <br><span class="item-type-tag"><?php echo esc_html($readscopeTypeTag); ?></span>
                        </td>
                        <td><?php echo esc_html($readscopeDates); ?></td>
                        <td style="text-align:center;"><?php echo esc_html((string)$readscopeItem->quantity); ?></td>
                        <td style="text-align:right; font-weight:600;">
                            <?php echo esc_html(Money::format((float) $readscopeItem->total_price, $readscopeCurrency)); ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- Financial Summary -->
        <div class="financial-summary">
            <div class="summary-box">
                <?php
                $subtotalAmt = (float) $readscopeBooking->total_amount - (float) ($readscopeBooking->tax_amount ?? 0) + (float) ($readscopeBooking->discount_amount ?? 0);
                if ((float) ($readscopeBooking->discount_amount ?? 0) > 0 || (float) ($readscopeBooking->tax_amount ?? 0) > 0) : ?>
                    <div class="summary-row">
                        <span><?php esc_html_e('Subtotal:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span><?php echo esc_html(Money::format($subtotalAmt, $readscopeCurrency)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ((float) ($readscopeBooking->discount_amount ?? 0) > 0) : ?>
                    <div class="summary-row" style="color: #15803d;">
                        <span><?php esc_html_e('Discount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span>-<?php echo esc_html(Money::format((float) $readscopeBooking->discount_amount, $readscopeCurrency)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ((float) ($readscopeBooking->tax_amount ?? 0) > 0) : ?>
                    <div class="summary-row">
                        <span><?php esc_html_e('Tax / VAT:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span><?php echo esc_html(Money::format((float) $readscopeBooking->tax_amount, $readscopeCurrency)); ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    <span><?php echo esc_html(Money::format((float) $readscopeBooking->total_amount, $readscopeCurrency)); ?></span>
                </div>
                <?php if ((float) ($readscopeBooking->due_amount ?? 0) > 0) : ?>
                    <div class="summary-row" style="color: #b91c1c; font-weight: 600;">
                        <span><?php esc_html_e('Amount Due:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span><?php echo esc_html(Money::format((float) $readscopeBooking->due_amount, $readscopeCurrency)); ?></span>
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

<?php wp_print_scripts(\Readscope\Support\PortalAssets::handle('voucher')); ?>
</body>
</html>
