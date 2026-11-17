<?php
/**
 * Readscope Booking Customer Account / My Bookings Template
 *
 * @package Readscope Booking
 * @var bool             $isGuest
 * @var string           $title
 * @var WP_User|null     $currentUser
 * @var bool             $showStats
 * @var array<string, mixed> $stats
 * @var string           $defaultTab
 * @var array<array<string, mixed>> $bookings
 * @var string           $loginUrl
 * @var string           $registerUrl
 * @var bool             $canRegister
 * @var string           $lookupUrl
 * @var string           $nonce
 * @var string           $primaryColor
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside a function; these variables are local.

$brandColor = !empty($primaryColor) ? $primaryColor : '#0d9488';
?>

<div class="readscope-my-bookings-container" style="--trv-primary: <?php echo esc_attr($brandColor); ?>;">

<?php if (!empty($isGuest)) : ?>
    <!-- 1. Guest Visitor State -->
    <div class="readscope-my-bookings-login-prompt">
        <div class="readscope-prompt-card">
            <div class="readscope-prompt-icon" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($brandColor); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h2 class="readscope-prompt-title"><?php echo esc_html($title); ?></h2>
            <p class="readscope-prompt-desc">
                <?php esc_html_e('Please log in to your account to view your travel itineraries, download vouchers, or manage your bookings.', 'the-readscope-tour-and-accommodation-booking'); ?>
            </p>

            <div class="readscope-prompt-actions">
                <a href="<?php echo esc_url($loginUrl); ?>" class="readscope-btn-account readscope-btn-primary">
                    <?php esc_html_e('Log in to Your Account', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
                <?php if (!empty($canRegister) && !empty($registerUrl)) : ?>
                    <a href="<?php echo esc_url($registerUrl); ?>" class="readscope-btn-account readscope-btn-outline">
                        <?php esc_html_e('Register an Account', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($lookupUrl)) : ?>
                <div class="readscope-prompt-guest-lookup">
                    <span><?php esc_html_e('Booked as a guest without an account?', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    <a href="<?php echo esc_url($lookupUrl); ?>" class="readscope-guest-link">
                        <?php esc_html_e('Track Your Booking with Reference Code →', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else : ?>
    <?php
    \Readscope\Support\PortalAssets::enqueue(
        'my-bookings',
        [
            'nonce'   => $nonce,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'i18n'    => [
                'processing' => __('Processing...', 'the-readscope-tour-and-accommodation-booking'),
                'confirmCancellation' => __('Confirm Cancellation', 'the-readscope-tour-and-accommodation-booking'),
                'cancellationSubmittedSuccessfully' => __('Cancellation submitted successfully.', 'the-readscope-tour-and-accommodation-booking'),
                'failedToProcessCancellation' => __('Failed to process cancellation.', 'the-readscope-tour-and-accommodation-booking'),
                'aNetworkErrorOccurred' => __('A network error occurred. Please try again.', 'the-readscope-tour-and-accommodation-booking'),
                'loadingBookingDetails' => __('Loading booking details...', 'the-readscope-tour-and-accommodation-booking'),
                'bookingReference' => __('Booking Reference', 'the-readscope-tour-and-accommodation-booking'),
                'placedOn' => __('Placed on:', 'the-readscope-tour-and-accommodation-booking'),
                'reservedItems' => __('Reserved Items', 'the-readscope-tour-and-accommodation-booking'),
                'paymentFinancialSummary' => __('Payment & Financial Summary', 'the-readscope-tour-and-accommodation-booking'),
                'paymentMethod' => __('Payment Method:', 'the-readscope-tour-and-accommodation-booking'),
                'paymentStatus' => __('Payment Status:', 'the-readscope-tour-and-accommodation-booking'),
                'tax' => __('Tax:', 'the-readscope-tour-and-accommodation-booking'),
                'total' => __('Total:', 'the-readscope-tour-and-accommodation-booking'),
                'yourNotes' => __('Your Notes:', 'the-readscope-tour-and-accommodation-booking'),
                'failedToLoadBooking' => __('Failed to load booking details.', 'the-readscope-tour-and-accommodation-booking'),
                'networkErrorShort' => __('A network error occurred.', 'the-readscope-tour-and-accommodation-booking'),
            ],
        ]
    );
    ?>
    <!-- 2. Authenticated Traveler Dashboard State -->
    <div class="readscope-my-bookings-dashboard">
        <!-- Top Welcome Header -->
        <div class="readscope-dashboard-header">
            <div>
                <span class="readscope-traveler-welcome">
                    <?php
                    printf(
                        /* translators: %s: Traveler display name */
                        esc_html__('Welcome back, %s 👋', 'the-readscope-tour-and-accommodation-booking'),
                        esc_html($currentUser ? ($currentUser->display_name ?: $currentUser->user_login) : '')
                    );
                    ?>
                </span>
                <h1 class="readscope-dashboard-title"><?php echo esc_html($title); ?></h1>
            </div>
            <?php if (!empty($lookupUrl)) : ?>
                <div>
                    <a href="<?php echo esc_url($lookupUrl); ?>" class="readscope-link-lookup">
                        🔍 <?php esc_html_e('Quick Booking Lookup', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($showStats) && !empty($stats)) : ?>
            <!-- Stats KPI Cards -->
            <div class="readscope-stats-grid">
                <div class="readscope-stat-card">
                    <div class="readscope-stat-icon">🌴</div>
                    <div class="readscope-stat-data">
                        <span class="readscope-stat-value"><?php echo esc_html((string) $stats['total']); ?></span>
                        <span class="readscope-stat-label"><?php esc_html_e('Total Trips', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    </div>
                </div>

                <div class="readscope-stat-card">
                    <div class="readscope-stat-icon" style="color:#0d9488;">📅</div>
                    <div class="readscope-stat-data">
                        <span class="readscope-stat-value"><?php echo esc_html((string) $stats['upcoming']); ?></span>
                        <span class="readscope-stat-label"><?php esc_html_e('Upcoming Trips', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    </div>
                </div>

                <div class="readscope-stat-card">
                    <div class="readscope-stat-icon" style="color:#10b981;">✅</div>
                    <div class="readscope-stat-data">
                        <span class="readscope-stat-value"><?php echo esc_html((string) $stats['completed']); ?></span>
                        <span class="readscope-stat-label"><?php esc_html_e('Completed Trips', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    </div>
                </div>

                <div class="readscope-stat-card">
                    <div class="readscope-stat-icon" style="color:#6366f1;">💳</div>
                    <div class="readscope-stat-data">
                        <span class="readscope-stat-value"><?php echo esc_html($stats['total_spent']); ?></span>
                        <span class="readscope-stat-label"><?php esc_html_e('Total Spent', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="readscope-bookings-tabs" role="tablist">
            <button type="button" class="readscope-tab-btn active" data-tab="all" role="tab" aria-selected="true">
                <?php esc_html_e('All Bookings', 'the-readscope-tour-and-accommodation-booking'); ?>
                <span class="readscope-tab-count"><?php echo esc_html((string) ($stats['total'] ?? count($bookings))); ?></span>
            </button>
            <button type="button" class="readscope-tab-btn" data-tab="upcoming" role="tab" aria-selected="false">
                <?php esc_html_e('Upcoming', 'the-readscope-tour-and-accommodation-booking'); ?>
                <span class="readscope-tab-count"><?php echo esc_html((string) ($stats['upcoming'] ?? 0)); ?></span>
            </button>
            <button type="button" class="readscope-tab-btn" data-tab="completed" role="tab" aria-selected="false">
                <?php esc_html_e('Completed', 'the-readscope-tour-and-accommodation-booking'); ?>
                <span class="readscope-tab-count"><?php echo esc_html((string) ($stats['completed'] ?? 0)); ?></span>
            </button>
            <button type="button" class="readscope-tab-btn" data-tab="cancelled" role="tab" aria-selected="false">
                <?php esc_html_e('Cancelled', 'the-readscope-tour-and-accommodation-booking'); ?>
                <span class="readscope-tab-count"><?php echo esc_html((string) ($stats['cancelled'] ?? 0)); ?></span>
            </button>
        </div>

        <!-- Bookings List -->
        <div class="readscope-bookings-list" id="readscopeBookingsList">
            <?php if (empty($bookings)) : ?>
                <div class="readscope-no-bookings">
                    <div style="font-size: var(--trv-text-display); margin-bottom: 12px;">🗺️</div>
                    <h3><?php esc_html_e('No bookings found yet', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                    <p><?php esc_html_e("When you reserve a tour package or hotel stay, it will appear here in your dashboard.", 'the-readscope-tour-and-accommodation-booking'); ?></p>
                </div>
            <?php else : ?>
                <?php foreach ($bookings as $b) : ?>
                    <?php
                    $statusBg = '#f1f5f9';
                    $statusColor = '#475569';
                    if ($b['booking_status'] === 'confirmed' || $b['booking_status'] === 'completed') {
                        $statusBg = '#dcfce7';
                        $statusColor = '#15803d';
                    } elseif ($b['booking_status'] === 'pending') {
                        $statusBg = '#fef3c7';
                        $statusColor = '#b45309';
                    } elseif ($b['booking_status'] === 'cancelled') {
                        $statusBg = '#fee2e2';
                        $statusColor = '#b91c1c';
                    }

                    $payBg = ($b['payment_status'] === 'paid') ? '#dcfce7' : '#f1f5f9';
                    $payColor = ($b['payment_status'] === 'paid') ? '#15803d' : '#475569';
                    $item = $b['first_item'];
                    $itemTypeLabel = ($item && ($item->item_type === 'room' || $item->item_type === 'hotel_room'))
                        ? __('Hotel Stay', 'the-readscope-tour-and-accommodation-booking')
                        : __('Tour Package', 'the-readscope-tour-and-accommodation-booking');

                    $checkInDate = $item && !empty($item->check_in) ? gmdate('M d, Y', strtotime((string) $item->check_in)) : '—';
                    $checkOutDate = $item && !empty($item->check_out) ? gmdate('M d, Y', strtotime((string) $item->check_out)) : '';
                    $dateRange = $checkOutDate ? "{$checkInDate} → {$checkOutDate}" : $checkInDate;
                    ?>
                    <div class="readscope-booking-card" data-category="<?php echo esc_attr((string) $b['category']); ?>">
                        <div class="readscope-card-header">
                            <div class="readscope-card-reference">
                                <span class="readscope-ref-label"><?php esc_html_e('Booking Code', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                <strong class="readscope-ref-code">#<?php echo esc_html((string) $b['booking_code']); ?></strong>
                            </div>

                            <div class="readscope-card-badges">
                                <span class="readscope-badge" style="background: <?php echo esc_attr($statusBg); ?>; color: <?php echo esc_attr($statusColor); ?>;">
                                    <?php echo esc_html(ucfirst((string) $b['booking_status'])); ?>
                                </span>
                                <span class="readscope-badge" style="background: <?php echo esc_attr($payBg); ?>; color: <?php echo esc_attr($payColor); ?>;">
                                    <?php echo esc_html(ucfirst((string) $b['payment_status'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="readscope-card-body">
                            <div class="readscope-item-summary">
                                <div class="readscope-item-type-badge"><?php echo esc_html($itemTypeLabel); ?></div>
                                <h3 class="readscope-item-title"><?php echo esc_html($item ? (string) $item->item_title : __('Tour / Room', 'the-readscope-tour-and-accommodation-booking')); ?></h3>
                            </div>

                            <div class="readscope-meta-grid">
                                <div>
                                    <span class="readscope-meta-label">📅 <?php esc_html_e('Date / Duration', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                    <span class="readscope-meta-val"><?php echo esc_html($dateRange); ?></span>
                                </div>
                                <?php if ($item && !empty($item->time_slot)) : ?>
                                    <div>
                                        <span class="readscope-meta-label">⏰ <?php esc_html_e('Time Slot', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                        <span class="readscope-meta-val"><?php echo esc_html((string) $item->time_slot); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="readscope-meta-label">👥 <?php esc_html_e('Guests / Qty', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                    <span class="readscope-meta-val"><?php echo esc_html($item ? (string) $item->quantity : '1'); ?></span>
                                </div>
                                <div>
                                    <span class="readscope-meta-label">💰 <?php esc_html_e('Total Amount', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                    <strong class="readscope-meta-val" style="color: #0f172a; font-size: var(--trv-text-base);"><?php echo esc_html((string) $b['total_amount']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="readscope-card-actions">
                            <button type="button" class="readscope-btn-sm readscope-btn-outline btnViewDetails" data-booking-id="<?php echo esc_attr((string) $b['id']); ?>">
                                📄 <?php esc_html_e('View Details', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </button>

                            <a href="<?php echo esc_url((string) $b['voucher_url']); ?>" target="_blank" rel="noopener noreferrer" class="readscope-btn-sm readscope-btn-outline">
                                🎫 <?php esc_html_e('Download Voucher', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </a>

                            <a href="<?php echo esc_url((string) $b['calendar_url']); ?>" class="readscope-btn-sm readscope-btn-outline">
                                📅 <?php esc_html_e('Add to Calendar', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </a>
                            <?php do_action('readscope/my_booking_actions', $b); ?>

                            <?php if (!empty($b['can_cancel'])) : ?>
                                <button type="button" class="readscope-btn-sm readscope-btn-danger btnOpenCancelModal" data-booking-id="<?php echo esc_attr((string) $b['id']); ?>" data-code="<?php echo esc_attr((string) $b['booking_code']); ?>">
                                    ✕ <?php esc_html_e('Cancel Booking', 'the-readscope-tour-and-accommodation-booking'); ?>
                                </button>
                            <?php elseif (!empty($b['cancel_requested'])) : ?>
                                <span class="readscope-cancel-pending-pill">
                                    ⏳ <?php esc_html_e('Cancellation Requested', 'the-readscope-tour-and-accommodation-booking'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Booking Details Modal -->
    <div class="readscope-modal-backdrop" id="readscopeDetailsModal" style="display:none;" aria-hidden="true">
        <div class="readscope-modal-box">
            <div class="readscope-modal-header">
                <h3 class="readscope-modal-title"><?php esc_html_e('Reservation Details', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <button type="button" class="readscope-modal-close" data-readscope-action="close-details" aria-label="<?php esc_attr_e('Close', 'the-readscope-tour-and-accommodation-booking'); ?>">×</button>
            </div>
            <div class="readscope-modal-body" id="readscopeDetailsBody">
                <div class="readscope-modal-loader"><?php esc_html_e('Loading details...', 'the-readscope-tour-and-accommodation-booking'); ?></div>
            </div>
            <div class="readscope-modal-footer">
                <button type="button" class="readscope-btn-account readscope-btn-outline" data-readscope-action="close-details">
                    <?php esc_html_e('Close', 'the-readscope-tour-and-accommodation-booking'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- 4. Cancellation Confirmation Modal -->
    <div class="readscope-modal-backdrop" id="readscopeCancelModal" style="display:none;" aria-hidden="true">
        <div class="readscope-modal-box">
            <div class="readscope-modal-header">
                <h3 class="readscope-modal-title"><?php esc_html_e('Confirm Cancellation', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                <button type="button" class="readscope-modal-close" data-readscope-action="close-cancel" aria-label="<?php esc_attr_e('Close', 'the-readscope-tour-and-accommodation-booking'); ?>">×</button>
            </div>
            <div class="readscope-modal-body">
                <p style="margin-top:0; color:#475569;">
                    <?php esc_html_e('Are you sure you wish to cancel this reservation? Depending on our cancellation policy and booking status, this will either immediately release your reservation or submit a review request to our operations team.', 'the-readscope-tour-and-accommodation-booking'); ?>
                </p>
                <div style="margin-bottom: 16px;">
                    <label for="trvCancelReason" style="display:block; font-weight:600; margin-bottom:6px; color:#0f172a;">
                        <?php esc_html_e('Reason for Cancellation (optional):', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </label>
                    <textarea id="trvCancelReason" rows="3" class="readscope-input-textarea" placeholder="<?php esc_attr_e('e.g., Changed travel dates, medical reason...', 'the-readscope-tour-and-accommodation-booking'); ?>"></textarea>
                </div>
                <div id="trvCancelFeedback" style="display:none; padding:10px; border-radius:6px; font-size: var(--trv-text-sm); margin-bottom:12px;"></div>
            </div>
            <div class="readscope-modal-footer">
                <button type="button" class="readscope-btn-account readscope-btn-outline" data-readscope-action="close-cancel">
                    <?php esc_html_e('Nevermind, Keep Booking', 'the-readscope-tour-and-accommodation-booking'); ?>
                </button>
                <button type="button" class="readscope-btn-account readscope-btn-danger" id="btnConfirmCancel">
                    <?php esc_html_e('Confirm Cancellation', 'the-readscope-tour-and-accommodation-booking'); ?>
                </button>
            </div>
        </div>
    </div>


<?php endif; ?>

</div>
