<?php
/**
 * Tourivo Customer Account / My Bookings Template
 *
 * @package Tourivo
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

<div class="tourivo-my-bookings-container" style="--trv-primary: <?php echo esc_attr($brandColor); ?>;">

<?php if (!empty($isGuest)) : ?>
    <!-- 1. Guest Visitor State -->
    <div class="tourivo-my-bookings-login-prompt">
        <div class="tourivo-prompt-card">
            <div class="tourivo-prompt-icon" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($brandColor); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h2 class="tourivo-prompt-title"><?php echo esc_html($title); ?></h2>
            <p class="tourivo-prompt-desc">
                <?php esc_html_e('Please log in to your account to view your travel itineraries, download vouchers, or manage your bookings.', 'tourivo'); ?>
            </p>

            <div class="tourivo-prompt-actions">
                <a href="<?php echo esc_url($loginUrl); ?>" class="tourivo-btn-account tourivo-btn-primary">
                    <?php esc_html_e('Log in to Your Account', 'tourivo'); ?>
                </a>
                <?php if (!empty($canRegister) && !empty($registerUrl)) : ?>
                    <a href="<?php echo esc_url($registerUrl); ?>" class="tourivo-btn-account tourivo-btn-outline">
                        <?php esc_html_e('Register an Account', 'tourivo'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($lookupUrl)) : ?>
                <div class="tourivo-prompt-guest-lookup">
                    <span><?php esc_html_e('Booked as a guest without an account?', 'tourivo'); ?></span>
                    <a href="<?php echo esc_url($lookupUrl); ?>" class="tourivo-guest-link">
                        <?php esc_html_e('Track Your Booking with Reference Code →', 'tourivo'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else : ?>
    <?php
    \Tourivo\Support\PortalAssets::enqueue(
        'my-bookings',
        [
            'nonce'   => $nonce,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'i18n'    => [
                'processing' => __('Processing...', 'tourivo'),
                'confirmCancellation' => __('Confirm Cancellation', 'tourivo'),
                'cancellationSubmittedSuccessfully' => __('Cancellation submitted successfully.', 'tourivo'),
                'failedToProcessCancellation' => __('Failed to process cancellation.', 'tourivo'),
                'aNetworkErrorOccurred' => __('A network error occurred. Please try again.', 'tourivo'),
                'loadingBookingDetails' => __('Loading booking details...', 'tourivo'),
                'bookingReference' => __('Booking Reference', 'tourivo'),
                'placedOn' => __('Placed on:', 'tourivo'),
                'reservedItems' => __('Reserved Items', 'tourivo'),
                'paymentFinancialSummary' => __('Payment & Financial Summary', 'tourivo'),
                'paymentMethod' => __('Payment Method:', 'tourivo'),
                'paymentStatus' => __('Payment Status:', 'tourivo'),
                'tax' => __('Tax:', 'tourivo'),
                'total' => __('Total:', 'tourivo'),
                'yourNotes' => __('Your Notes:', 'tourivo'),
                'failedToLoadBooking' => __('Failed to load booking details.', 'tourivo'),
                'networkErrorShort' => __('A network error occurred.', 'tourivo'),
            ],
        ]
    );
    ?>
    <!-- 2. Authenticated Traveler Dashboard State -->
    <div class="tourivo-my-bookings-dashboard">
        <!-- Top Welcome Header -->
        <div class="tourivo-dashboard-header">
            <div>
                <span class="tourivo-traveler-welcome">
                    <?php
                    printf(
                        /* translators: %s: Traveler display name */
                        esc_html__('Welcome back, %s 👋', 'tourivo'),
                        esc_html($currentUser ? ($currentUser->display_name ?: $currentUser->user_login) : '')
                    );
                    ?>
                </span>
                <h1 class="tourivo-dashboard-title"><?php echo esc_html($title); ?></h1>
            </div>
            <?php if (!empty($lookupUrl)) : ?>
                <div>
                    <a href="<?php echo esc_url($lookupUrl); ?>" class="tourivo-link-lookup">
                        🔍 <?php esc_html_e('Quick Booking Lookup', 'tourivo'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($showStats) && !empty($stats)) : ?>
            <!-- Stats KPI Cards -->
            <div class="tourivo-stats-grid">
                <div class="tourivo-stat-card">
                    <div class="tourivo-stat-icon">🌴</div>
                    <div class="tourivo-stat-data">
                        <span class="tourivo-stat-value"><?php echo esc_html((string) $stats['total']); ?></span>
                        <span class="tourivo-stat-label"><?php esc_html_e('Total Trips', 'tourivo'); ?></span>
                    </div>
                </div>

                <div class="tourivo-stat-card">
                    <div class="tourivo-stat-icon" style="color:#0d9488;">📅</div>
                    <div class="tourivo-stat-data">
                        <span class="tourivo-stat-value"><?php echo esc_html((string) $stats['upcoming']); ?></span>
                        <span class="tourivo-stat-label"><?php esc_html_e('Upcoming Trips', 'tourivo'); ?></span>
                    </div>
                </div>

                <div class="tourivo-stat-card">
                    <div class="tourivo-stat-icon" style="color:#10b981;">✅</div>
                    <div class="tourivo-stat-data">
                        <span class="tourivo-stat-value"><?php echo esc_html((string) $stats['completed']); ?></span>
                        <span class="tourivo-stat-label"><?php esc_html_e('Completed Trips', 'tourivo'); ?></span>
                    </div>
                </div>

                <div class="tourivo-stat-card">
                    <div class="tourivo-stat-icon" style="color:#6366f1;">💳</div>
                    <div class="tourivo-stat-data">
                        <span class="tourivo-stat-value"><?php echo esc_html($stats['total_spent']); ?></span>
                        <span class="tourivo-stat-label"><?php esc_html_e('Total Spent', 'tourivo'); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="tourivo-bookings-tabs" role="tablist">
            <button type="button" class="tourivo-tab-btn active" data-tab="all" role="tab" aria-selected="true">
                <?php esc_html_e('All Bookings', 'tourivo'); ?>
                <span class="tourivo-tab-count"><?php echo esc_html((string) ($stats['total'] ?? count($bookings))); ?></span>
            </button>
            <button type="button" class="tourivo-tab-btn" data-tab="upcoming" role="tab" aria-selected="false">
                <?php esc_html_e('Upcoming', 'tourivo'); ?>
                <span class="tourivo-tab-count"><?php echo esc_html((string) ($stats['upcoming'] ?? 0)); ?></span>
            </button>
            <button type="button" class="tourivo-tab-btn" data-tab="completed" role="tab" aria-selected="false">
                <?php esc_html_e('Completed', 'tourivo'); ?>
                <span class="tourivo-tab-count"><?php echo esc_html((string) ($stats['completed'] ?? 0)); ?></span>
            </button>
            <button type="button" class="tourivo-tab-btn" data-tab="cancelled" role="tab" aria-selected="false">
                <?php esc_html_e('Cancelled', 'tourivo'); ?>
                <span class="tourivo-tab-count"><?php echo esc_html((string) ($stats['cancelled'] ?? 0)); ?></span>
            </button>
        </div>

        <!-- Bookings List -->
        <div class="tourivo-bookings-list" id="tourivoBookingsList">
            <?php if (empty($bookings)) : ?>
                <div class="tourivo-no-bookings">
                    <div style="font-size: var(--trv-text-display); margin-bottom: 12px;">🗺️</div>
                    <h3><?php esc_html_e('No bookings found yet', 'tourivo'); ?></h3>
                    <p><?php esc_html_e("When you reserve a tour package or hotel stay, it will appear here in your dashboard.", 'tourivo'); ?></p>
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
                        ? __('Hotel Stay', 'tourivo')
                        : __('Tour Package', 'tourivo');

                    $checkInDate = $item && !empty($item->check_in) ? gmdate('M d, Y', strtotime((string) $item->check_in)) : '—';
                    $checkOutDate = $item && !empty($item->check_out) ? gmdate('M d, Y', strtotime((string) $item->check_out)) : '';
                    $dateRange = $checkOutDate ? "{$checkInDate} → {$checkOutDate}" : $checkInDate;
                    ?>
                    <div class="tourivo-booking-card" data-category="<?php echo esc_attr((string) $b['category']); ?>">
                        <div class="tourivo-card-header">
                            <div class="tourivo-card-reference">
                                <span class="tourivo-ref-label"><?php esc_html_e('Booking Code', 'tourivo'); ?></span>
                                <strong class="tourivo-ref-code">#<?php echo esc_html((string) $b['booking_code']); ?></strong>
                            </div>

                            <div class="tourivo-card-badges">
                                <span class="tourivo-badge" style="background: <?php echo esc_attr($statusBg); ?>; color: <?php echo esc_attr($statusColor); ?>;">
                                    <?php echo esc_html(ucfirst((string) $b['booking_status'])); ?>
                                </span>
                                <span class="tourivo-badge" style="background: <?php echo esc_attr($payBg); ?>; color: <?php echo esc_attr($payColor); ?>;">
                                    <?php echo esc_html(ucfirst((string) $b['payment_status'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="tourivo-card-body">
                            <div class="tourivo-item-summary">
                                <div class="tourivo-item-type-badge"><?php echo esc_html($itemTypeLabel); ?></div>
                                <h3 class="tourivo-item-title"><?php echo esc_html($item ? (string) $item->item_title : __('Tour / Room', 'tourivo')); ?></h3>
                            </div>

                            <div class="tourivo-meta-grid">
                                <div>
                                    <span class="tourivo-meta-label">📅 <?php esc_html_e('Date / Duration', 'tourivo'); ?></span>
                                    <span class="tourivo-meta-val"><?php echo esc_html($dateRange); ?></span>
                                </div>
                                <?php if ($item && !empty($item->time_slot)) : ?>
                                    <div>
                                        <span class="tourivo-meta-label">⏰ <?php esc_html_e('Time Slot', 'tourivo'); ?></span>
                                        <span class="tourivo-meta-val"><?php echo esc_html((string) $item->time_slot); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="tourivo-meta-label">👥 <?php esc_html_e('Guests / Qty', 'tourivo'); ?></span>
                                    <span class="tourivo-meta-val"><?php echo esc_html($item ? (string) $item->quantity : '1'); ?></span>
                                </div>
                                <div>
                                    <span class="tourivo-meta-label">💰 <?php esc_html_e('Total Amount', 'tourivo'); ?></span>
                                    <strong class="tourivo-meta-val" style="color: #0f172a; font-size: var(--trv-text-base);"><?php echo esc_html((string) $b['total_amount']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="tourivo-card-actions">
                            <button type="button" class="tourivo-btn-sm tourivo-btn-outline btnViewDetails" data-booking-id="<?php echo esc_attr((string) $b['id']); ?>">
                                📄 <?php esc_html_e('View Details', 'tourivo'); ?>
                            </button>

                            <a href="<?php echo esc_url((string) $b['voucher_url']); ?>" target="_blank" rel="noopener noreferrer" class="tourivo-btn-sm tourivo-btn-outline">
                                🎫 <?php esc_html_e('Download Voucher', 'tourivo'); ?>
                            </a>

                            <a href="<?php echo esc_url((string) $b['calendar_url']); ?>" class="tourivo-btn-sm tourivo-btn-outline">
                                📅 <?php esc_html_e('Add to Calendar', 'tourivo'); ?>
                            </a>
                            <?php do_action('tourivo/my_booking_actions', $b); ?>

                            <?php if (!empty($b['can_cancel'])) : ?>
                                <button type="button" class="tourivo-btn-sm tourivo-btn-danger btnOpenCancelModal" data-booking-id="<?php echo esc_attr((string) $b['id']); ?>" data-code="<?php echo esc_attr((string) $b['booking_code']); ?>">
                                    ✕ <?php esc_html_e('Cancel Booking', 'tourivo'); ?>
                                </button>
                            <?php elseif (!empty($b['cancel_requested'])) : ?>
                                <span class="tourivo-cancel-pending-pill">
                                    ⏳ <?php esc_html_e('Cancellation Requested', 'tourivo'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Booking Details Modal -->
    <div class="tourivo-modal-backdrop" id="tourivoDetailsModal" style="display:none;" aria-hidden="true">
        <div class="tourivo-modal-box">
            <div class="tourivo-modal-header">
                <h3 class="tourivo-modal-title"><?php esc_html_e('Reservation Details', 'tourivo'); ?></h3>
                <button type="button" class="tourivo-modal-close" data-tourivo-action="close-details" aria-label="<?php esc_attr_e('Close', 'tourivo'); ?>">×</button>
            </div>
            <div class="tourivo-modal-body" id="tourivoDetailsBody">
                <div class="tourivo-modal-loader"><?php esc_html_e('Loading details...', 'tourivo'); ?></div>
            </div>
            <div class="tourivo-modal-footer">
                <button type="button" class="tourivo-btn-account tourivo-btn-outline" data-tourivo-action="close-details">
                    <?php esc_html_e('Close', 'tourivo'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- 4. Cancellation Confirmation Modal -->
    <div class="tourivo-modal-backdrop" id="tourivoCancelModal" style="display:none;" aria-hidden="true">
        <div class="tourivo-modal-box">
            <div class="tourivo-modal-header">
                <h3 class="tourivo-modal-title"><?php esc_html_e('Confirm Cancellation', 'tourivo'); ?></h3>
                <button type="button" class="tourivo-modal-close" data-tourivo-action="close-cancel" aria-label="<?php esc_attr_e('Close', 'tourivo'); ?>">×</button>
            </div>
            <div class="tourivo-modal-body">
                <p style="margin-top:0; color:#475569;">
                    <?php esc_html_e('Are you sure you wish to cancel this reservation? Depending on our cancellation policy and booking status, this will either immediately release your reservation or submit a review request to our operations team.', 'tourivo'); ?>
                </p>
                <div style="margin-bottom: 16px;">
                    <label for="trvCancelReason" style="display:block; font-weight:600; margin-bottom:6px; color:#0f172a;">
                        <?php esc_html_e('Reason for Cancellation (optional):', 'tourivo'); ?>
                    </label>
                    <textarea id="trvCancelReason" rows="3" class="tourivo-input-textarea" placeholder="<?php esc_attr_e('e.g., Changed travel dates, medical reason...', 'tourivo'); ?>"></textarea>
                </div>
                <div id="trvCancelFeedback" style="display:none; padding:10px; border-radius:6px; font-size: var(--trv-text-sm); margin-bottom:12px;"></div>
            </div>
            <div class="tourivo-modal-footer">
                <button type="button" class="tourivo-btn-account tourivo-btn-outline" data-tourivo-action="close-cancel">
                    <?php esc_html_e('Nevermind, Keep Booking', 'tourivo'); ?>
                </button>
                <button type="button" class="tourivo-btn-account tourivo-btn-danger" id="btnConfirmCancel">
                    <?php esc_html_e('Confirm Cancellation', 'tourivo'); ?>
                </button>
            </div>
        </div>
    </div>


<?php endif; ?>

</div>
