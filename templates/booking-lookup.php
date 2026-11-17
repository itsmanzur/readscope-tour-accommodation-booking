<?php
/**
 * Readscope Booking Lookup & Tracking Template
 *
 * @package Readscope Booking
 * @var string $readscopeLookupTitle
 * @var string $readscopeLookupDesc
 * @var string $readscopeNonce
 */

if (!defined('ABSPATH')) {
    exit;
}

$readscopeTitle = !empty($readscopeLookupTitle) ? $readscopeLookupTitle : __('Track Your Booking', 'the-readscope-tour-and-accommodation-booking');
$readscopeDesc  = !empty($readscopeLookupDesc) ? $readscopeLookupDesc : __('Enter your booking reference code and email to check your reservation status, download your voucher, or request cancellation.', 'the-readscope-tour-and-accommodation-booking');
$readscopeLookupToken = !empty($readscopeNonce) ? $readscopeNonce : wp_create_nonce('readscope_lookup_nonce');

\Readscope\Support\PortalAssets::enqueue(
    'lookup',
    [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'i18n'    => [
            'pleaseEnterBothYour'          => __('Please enter both your Booking Reference Code and Email Address.', 'the-readscope-tour-and-accommodation-booking'),
            'couldNotRetrieveReservation'  => __('Could not retrieve reservation details.', 'the-readscope-tour-and-accommodation-booking'),
            'connectionErrorPleaseTry'     => __('Connection error. Please try again.', 'the-readscope-tour-and-accommodation-booking'),
            'processing'                   => __('Processing...', 'the-readscope-tour-and-accommodation-booking'),
            'confirmCancellation'          => __('Confirm Cancellation', 'the-readscope-tour-and-accommodation-booking'),
            'requestSubmittedSuccessfully' => __('Request submitted successfully.', 'the-readscope-tour-and-accommodation-booking'),
            'failedToSubmitCancellation'   => __('Failed to submit cancellation request.', 'the-readscope-tour-and-accommodation-booking'),
            'bookedOn'                     => __('Booked on:', 'the-readscope-tour-and-accommodation-booking'),
        ],
    ]
);
?>

<div class="readscope-lookup-wrapper" id="readscope-lookup-portal">
    <div class="readscope-lookup-card">
        <div class="readscope-lookup-header">
            <div class="readscope-lookup-icon" aria-hidden="true">🎫</div>
            <h2 class="readscope-lookup-title"><?php echo esc_html($readscopeTitle); ?></h2>
            <p class="readscope-lookup-desc"><?php echo esc_html($readscopeDesc); ?></p>
        </div>

        <form class="readscope-lookup-form" id="readscopeLookupForm">
            <input type="hidden" name="nonce" id="readscope_lookup_nonce" value="<?php echo esc_attr($readscopeLookupToken); ?>">
            <!-- Honeypot anti-spam -->
            <input type="text" name="readscope_hp_check" id="readscope_hp_check" style="display:none;" tabindex="-1" autocomplete="off">

            <div class="readscope-lookup-grid">
                <div class="readscope-lookup-field">
                    <label for="readscope_lookup_code"><strong><?php esc_html_e('Booking Reference Code', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="readscope_lookup_code" name="booking_code" class="readscope-lookup-input" placeholder="<?php esc_attr_e('e.g. TRV-A1B2C3D4', 'the-readscope-tour-and-accommodation-booking'); ?>" required autocomplete="off">
                </div>

                <div class="readscope-lookup-field">
                    <label for="readscope_lookup_email"><strong><?php esc_html_e('Customer Email Address', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <span style="color:#ef4444;">*</span></label>
                    <input type="email" id="readscope_lookup_email" name="customer_email" class="readscope-lookup-input" placeholder="<?php esc_attr_e('your-email@domain.com', 'the-readscope-tour-and-accommodation-booking'); ?>" required>
                </div>
            </div>

            <div class="readscope-lookup-actions">
                <button type="submit" class="readscope-btn readscope-btn-primary readscope-lookup-submit" id="readscopeLookupBtn">
                    <span class="btn-text">🔍 <?php esc_html_e('Track Reservation', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    <span class="btn-spinner" style="display:none;">⏳ <?php esc_html_e('Searching...', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                </button>
            </div>
        </form>

        <div id="readscopeLookupError" class="readscope-lookup-alert error" style="display:none;" aria-live="polite"></div>

        <div id="readscopeLookupResult" class="readscope-lookup-result" style="display:none;" aria-live="polite">
            <div class="readscope-result-card">
                <div class="readscope-result-header">
                    <div>
                        <span class="readscope-result-label"><?php esc_html_e('Booking Reference', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <h3 class="readscope-result-code" id="resBookingCode">#TRV-0000</h3>
                        <small class="readscope-result-date" id="resCreatedAt"></small>
                    </div>
                    <div class="readscope-result-badges">
                        <span class="readscope-badge" id="resBookingStatus"></span>
                        <span class="readscope-badge" id="resPaymentStatus"></span>
                        <span class="readscope-badge" id="resCancelRequestedBadge" style="display:none; background:#fee2e2; color:#991b1b;">
                            🚨 <?php esc_html_e('Cancel Requested', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </span>
                    </div>
                </div>

                <div class="readscope-result-customer-info">
                    <p>👤 <strong><?php esc_html_e('Traveler:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <span id="resCustomerName"></span> (<span id="resCustomerEmail"></span>)</p>
                    <p>💳 <strong><?php esc_html_e('Payment Method:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <span id="resPaymentMethod"></span></p>
                </div>

                <div class="readscope-result-items-box">
                    <h4 style="margin: 0 0 10px; font-size: var(--trv-text-sm); text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;"><?php esc_html_e('Booked Package / Stays', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                    <div id="resItemsContainer"></div>
                </div>

                <div class="readscope-result-footer">
                    <div class="readscope-result-total">
                        <span><?php esc_html_e('Total Amount:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <strong id="resTotalAmount">$0.00</strong>
                    </div>
                    <div class="readscope-result-actions" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                        <a href="#" id="resVoucherBtn" target="_blank" rel="noopener" class="readscope-btn readscope-btn-success">
                            🖨️ <?php esc_html_e('Print Booking Voucher', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </a>
                        <button type="button" id="resCancelBtn" class="readscope-btn readscope-btn-cancel" style="display:none;">
                            🚨 <?php esc_html_e('Cancel Booking', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </button>
                    </div>
                </div>

                <!-- Cancellation Request Box -->
                <div id="readscopeCancelBox" style="display:none; margin-top:20px; padding:16px; background:#fff1f2; border:1px solid #fecdd3; border-radius:8px;">
                    <h4 style="margin:0 0 8px; color:#9f1239; font-size: var(--trv-text-base); font-weight:700;">
                        <?php esc_html_e('Submit Cancellation Request', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </h4>
                    <p style="margin:0 0 12px; font-size: var(--trv-text-sm); color:#881337;">
                        <?php esc_html_e('Are you sure you wish to cancel this reservation? Please provide a reason below:', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </p>
                    <textarea id="readscope_cancel_reason" rows="3" class="readscope-lookup-input" placeholder="<?php esc_attr_e('Reason for cancellation (optional)...', 'the-readscope-tour-and-accommodation-booking'); ?>" style="margin-bottom:12px; font-size: var(--trv-text-sm);"></textarea>
                    <div style="display:flex; gap:10px;">
                        <button type="button" id="readscopeConfirmCancelBtn" class="readscope-btn readscope-btn-danger">
                            <?php esc_html_e('Confirm Cancellation', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </button>
                        <button type="button" id="readscopeDismissCancelBtn" class="readscope-btn" style="background:#f1f5f9; color:#334155;">
                            <?php esc_html_e('Keep My Booking', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </button>
                    </div>
                    <div id="readscopeCancelAlert" style="display:none; margin-top:10px; font-size: var(--trv-text-sm); font-weight:600;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
