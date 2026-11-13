<?php
/**
 * Readscope Booking Confirmation & Thank You Page Template
 *
 * @package Readscope Booking
 * @var object $booking
 * @var array<object> $items
 * @var string $voucherUrl
 * @var string $icsUrl
 * @var string $lookupUrl
 * @var string $offlinePaymentInstructions
 * @var bool   $enableDataLayer
 * @var string $primaryColor
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside a function; these variables are local.

$brandColor    = !empty($primaryColor) ? $primaryColor : '#0d9488';
$bookingStatus = (string) $booking->booking_status;
$paymentStatus = (string) $booking->payment_status;

$statusBadgeBg = ($bookingStatus === 'confirmed' || $bookingStatus === 'completed') ? '#dcfce7' : '#fef3c7';
$statusBadgeColor = ($bookingStatus === 'confirmed' || $bookingStatus === 'completed') ? '#15803d' : '#b45309';

$paymentBadgeBg = ($paymentStatus === 'paid') ? '#dcfce7' : '#f1f5f9';
$paymentBadgeColor = ($paymentStatus === 'paid') ? '#15803d' : '#475569';

\Tourivo\Support\PortalAssets::enqueue(
    'thank-you',
    [
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'i18n'      => [
            'submitReference' => __('Submit Reference', 'the-readscope-tour-and-accommodation-booking'),
        ],
        'dataLayer' => !empty($enableDataLayer) ? [
            'code'     => (string) $booking->booking_code,
            'value'    => (float) $booking->total_amount,
            'currency' => (string) $booking->currency,
        ] : null,
    ]
);
?>

<!-- Robots & Referrer Protection -->
<meta name="robots" content="noindex,nofollow">
<meta name="referrer" content="no-referrer">

<div class="tourivo-thankyou-wrapper" style="--trv-primary: <?php echo esc_attr($brandColor); ?>;">
    <!-- Header Banner -->
    <div class="tourivo-thankyou-header">
        <div class="tourivo-thankyou-icon" aria-hidden="true">✓</div>
        <h1 class="tourivo-thankyou-title"><?php esc_html_e('Thank You! Your Reservation is Placed', 'the-readscope-tour-and-accommodation-booking'); ?></h1>
        <p class="tourivo-thankyou-subtitle">
            <?php esc_html_e('A confirmation summary has been dispatched to your email address.', 'the-readscope-tour-and-accommodation-booking'); ?>
        </p>
    </div>

    <!-- Booking Overview Bar -->
    <div class="tourivo-thankyou-overview-bar">
        <div>
            <span class="tourivo-overview-label"><?php esc_html_e('Booking Reference', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <strong class="tourivo-overview-code">#<?php echo esc_html($booking->booking_code); ?></strong>
        </div>
        <div>
            <span class="tourivo-overview-label"><?php esc_html_e('Reservation Status', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="tourivo-badge" style="background: <?php echo esc_attr($statusBadgeBg); ?>; color: <?php echo esc_attr($statusBadgeColor); ?>;">
                <?php echo esc_html(ucfirst($bookingStatus)); ?>
            </span>
        </div>
        <div>
            <span class="tourivo-overview-label"><?php esc_html_e('Payment Status', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="tourivo-badge" style="background: <?php echo esc_attr($paymentBadgeBg); ?>; color: <?php echo esc_attr($paymentBadgeColor); ?>;">
                <?php echo esc_html(strtoupper((string) $booking->payment_method) . ' &bull; ' . ucfirst($paymentStatus)); ?>
            </span>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="tourivo-thankyou-grid">
        <!-- Left: Reservation Details & Items -->
        <div class="tourivo-thankyou-card">
            <h2 class="tourivo-card-title"><?php esc_html_e('Reservation Summary', 'the-readscope-tour-and-accommodation-booking'); ?></h2>

            <div class="tourivo-thankyou-items">
                <?php if (!empty($items)) : foreach ($items as $item) : 
                    $checkInFormatted  = !empty($item->check_in) ? gmdate('M d, Y', strtotime((string) $item->check_in)) : '—';
                    $checkOutFormatted = !empty($item->check_out) ? gmdate('M d, Y', strtotime((string) $item->check_out)) : '';
                    $dateRange = $checkOutFormatted ? "{$checkInFormatted} → {$checkOutFormatted}" : $checkInFormatted;
                    $typeLabel = ($item->item_type === 'room' || $item->item_type === 'hotel_room') ? __('Hotel Stay', 'the-readscope-tour-and-accommodation-booking') : __('Tour Experience', 'the-readscope-tour-and-accommodation-booking');
                ?>
                    <div class="tourivo-item-card">
                        <div class="tourivo-item-head">
                            <div>
                                <h3 class="tourivo-item-title"><?php echo esc_html($item->item_title); ?></h3>
                                <span class="tourivo-item-type-badge"><?php echo esc_html($typeLabel); ?></span>
                            </div>
                            <div class="tourivo-item-price">
                                <?php echo esc_html(\Tourivo\Support\Money::format((float) $item->total_price)); ?>
                            </div>
                        </div>
                        <div class="tourivo-item-meta">
                            <span>📅 <strong><?php esc_html_e('Dates:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($dateRange); ?></span>
                            <?php if (!empty($item->time_slot) && $item->time_slot !== 'all_day') : ?>
                                <span>⏰ <strong><?php esc_html_e('Time Slot:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($item->time_slot); ?></span>
                            <?php endif; ?>
                            <span>👥 <strong><?php esc_html_e('Party:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php
                                $partyParts = [];
                                if (!empty($item->adults_count)) {
                                    /* translators: %d: Number of adults */
                                    $partyParts[] = sprintf(_n('%d Adult', '%d Adults', (int) $item->adults_count, 'the-readscope-tour-and-accommodation-booking'), (int) $item->adults_count);
                                }
                                if (!empty($item->children_count)) {
                                    /* translators: %d: Number of children */
                                    $partyParts[] = sprintf(_n('%d Child', '%d Children', (int) $item->children_count, 'the-readscope-tour-and-accommodation-booking'), (int) $item->children_count);
                                }
                                if (!empty($item->infants_count)) {
                                    /* translators: %d: Number of infants */
                                    $partyParts[] = sprintf(_n('%d Infant', '%d Infants', (int) $item->infants_count, 'the-readscope-tour-and-accommodation-booking'), (int) $item->infants_count);
                                }
                                echo esc_html(!empty($partyParts) ? implode(', ', $partyParts) : sprintf(/* translators: %d: number of travelers */ __('%d Travelers', 'the-readscope-tour-and-accommodation-booking'), (int) $item->quantity));
                            ?></span>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- Customer Details -->
            <div class="tourivo-customer-details">
                <h3 class="tourivo-section-subtitle"><?php esc_html_e('Primary Booker Information', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <p>👤 <strong><?php esc_html_e('Name:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($booking->customer_name); ?></p>
                <p>✉️ <strong><?php esc_html_e('Email:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($booking->customer_email); ?></p>
                <?php if (!empty($booking->customer_phone)) : ?>
                    <p>📞 <strong><?php esc_html_e('Phone:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($booking->customer_phone); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Pricing Breakdown & Payment Instructions -->
        <div>
            <!-- Financial Breakdown Card -->
            <div class="tourivo-thankyou-card">
                <h2 class="tourivo-card-title"><?php esc_html_e('Payment Summary', 'the-readscope-tour-and-accommodation-booking'); ?></h2>

                <div class="tourivo-price-breakdown">
                    <?php if ((float) ($booking->tax_amount ?? 0) > 0 || (float) ($booking->discount_amount ?? 0) > 0) : ?>
                        <?php 
                        $subtotal = (float) $booking->total_amount - (float) ($booking->tax_amount ?? 0) + (float) ($booking->discount_amount ?? 0);
                        ?>
                        <div class="tourivo-breakdown-row">
                            <span><?php esc_html_e('Subtotal:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                            <span><?php echo esc_html(\Tourivo\Support\Money::format($subtotal)); ?></span>
                        </div>
                        <?php if ((float) ($booking->discount_amount ?? 0) > 0) : ?>
                            <div class="tourivo-breakdown-row discount">
                                <span><?php esc_html_e('Discount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <span>-<?php echo esc_html(\Tourivo\Support\Money::format((float) $booking->discount_amount)); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ((float) ($booking->tax_amount ?? 0) > 0) : ?>
                            <div class="tourivo-breakdown-row">
                                <span><?php esc_html_e('Tax / VAT:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <span>+<?php echo esc_html(\Tourivo\Support\Money::format((float) $booking->tax_amount)); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="tourivo-breakdown-row total">
                        <span><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <strong><?php echo esc_html(\Tourivo\Support\Money::format((float) $booking->total_amount)); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Offline Payment Details & Transaction Reference Submission -->
            <?php
            $isPaid = ($paymentStatus === 'paid');
            $paymentMethod = (string) ($booking->payment_method ?? 'offline');
            $paymentService = \Tourivo\Common\Container::getInstance()->has(\Tourivo\Services\PaymentService::class)
                ? \Tourivo\Common\Container::getInstance()->get(\Tourivo\Services\PaymentService::class)
                : new \Tourivo\Services\PaymentService();
            ?>
            <?php if (!$isPaid) : ?>
                <div class="tourivo-offline-instructions-card">
                    <?php if ($paymentMethod === 'bank_transfer' || empty($paymentMethod) || $paymentMethod === 'offline') : ?>
                        <?php echo wp_kses_post($paymentService->getBankDetailsHtml()); ?>
                    <?php endif; ?>

                    <?php if ($paymentMethod === 'cash_on_arrival') : ?>
                        <div class="tourivo-cash-card">
                            <h4 style="margin: 0 0 8px; color: #1e293b;">💵 <?php esc_html_e('Cash on Arrival / Pay at Check-in', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                            <p style="margin: 0; color: #475569; font-size: var(--trv-text-sm); line-height: 1.5;">
                                <?php
                                $cashNote = (string) \Tourivo\Config\Config::get('cash_on_arrival_instructions', '');
                                echo !empty($cashNote)
                                    ? nl2br(esc_html($cashNote))
                                    : esc_html__('Your payment will be collected in cash or card upon arrival at our reception / tour counter.', 'the-readscope-tour-and-accommodation-booking');
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Transaction Reference Submission Form -->
                    <div class="tourivo-submit-reference-box" style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed #cbd5e1;">
                        <h4 style="margin: 0 0 6px; font-size: var(--trv-text-sm); color: #0f172a;">📝 <?php esc_html_e('Already completed your transfer or deposit?', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                        <p style="margin: 0 0 12px; font-size: var(--trv-text-sm); color: #64748b;">
                            <?php esc_html_e('Submit your deposit slip, bank transaction reference, or mobile banking TrxID below for fast verification.', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>

                        <?php if (!empty($booking->transaction_id)) : ?>
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px 14px; font-size: var(--trv-text-sm); color: #166534;">
                                <strong>✓ <?php esc_html_e('Submitted Reference:', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                <code><?php echo esc_html($booking->transaction_id); ?></code>
                                <span style="font-size: var(--trv-text-xs); margin-left: 6px; color: #15803d;"><?php esc_html_e('(Under review by accounts team)', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                            </div>
                        <?php else : ?>
                            <form id="tourivo-submit-ref-form" style="display: flex; flex-direction: column; gap: 8px;">
                                <input type="hidden" name="action" value="tourivo_submit_payment_reference">
                                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('tourivo_frontend_nonce')); ?>">
                                <input type="hidden" name="booking_code" value="<?php echo esc_attr($booking->booking_code); ?>">
                                <?php if (!empty($token)) : ?>
                                    <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="customer_email" value="<?php echo esc_attr($booking->customer_email); ?>">

                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <input type="text" name="transaction_id" id="tourivo-ref-input" required placeholder="<?php esc_attr_e('Transaction Reference / Slip # / TrxID *', 'the-readscope-tour-and-accommodation-booking'); ?>" style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: var(--trv-text-sm);">
                                    <button type="submit" id="tourivo-submit-ref-btn" class="button button-primary" style="background: var(--trv-primary, #0d9488); border-color: var(--trv-primary, #0d9488); color: #fff; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                                        <?php esc_html_e('Submit Reference', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    </button>
                                </div>
                                <div id="tourivo-ref-alert" style="display: none; padding: 8px 12px; border-radius: 6px; font-size: var(--trv-text-sm); margin-top: 4px;"></div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons Card -->
            <div class="tourivo-thankyou-card tourivo-actions-card">
                <a href="<?php echo esc_url($voucherUrl); ?>" target="_blank" rel="noopener" class="tourivo-btn tourivo-btn-voucher">
                    🖨️ <?php esc_html_e('Print Booking Voucher', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>

                <a href="<?php echo esc_url($icsUrl); ?>" class="tourivo-btn tourivo-btn-calendar">
                    📅 <?php esc_html_e('Add to Calendar (.ics)', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>

                <a href="<?php echo esc_url($lookupUrl); ?>" class="tourivo-btn tourivo-btn-track">
                    🔍 <?php esc_html_e('Track My Booking', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
                <?php do_action('tourivo/thankyou_actions', $booking); ?>
            </div>
        </div>
    </div>

    <!-- What Happens Next Guide -->
    <div class="tourivo-next-steps-section">
        <h2 class="tourivo-next-steps-title"><?php esc_html_e('What Happens Next?', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
        <div class="tourivo-steps-grid">
            <div class="tourivo-step-box">
                <div class="tourivo-step-num">1</div>
                <h4><?php esc_html_e('Check Your Email', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                <p><?php esc_html_e('A full breakdown with printable access and reference details has been delivered to your inbox.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>
            <div class="tourivo-step-box">
                <div class="tourivo-step-num">2</div>
                <h4><?php esc_html_e('Save Your Voucher', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                <p><?php esc_html_e('Download or print your official voucher to present upon arrival or departure check-in.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>
            <div class="tourivo-step-box">
                <div class="tourivo-step-num">3</div>
                <h4><?php esc_html_e('Need Help or Changes?', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                <p><?php esc_html_e('Use our online lookup portal anytime to check live status or submit trip inquiries.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            </div>
        </div>
    </div>
</div>
