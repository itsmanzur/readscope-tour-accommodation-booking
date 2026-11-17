<?php

declare(strict_types=1);

namespace Readscope\Shortcodes;

use Readscope\Config\Config;
use Readscope\Support\Money;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ThankYouShortcode
 *
 * Renders the post-booking confirmation & thank-you page:
 * - Security: HMAC signed token validation (purpose: thankyou, 72-hour validity, no PII in URL)
 * - Cache busting: DONOTCACHEPAGE and nocache_headers()
 * - Summary: Reservation details, status badge, pricing breakdown
 * - Offline Payment Instructions box (if configured)
 * - Actions: Print voucher, Add to calendar (.ics), Track reservation
 * - Conversion tracking: Optional GTM dataLayer event push (PII-free) with sessionStorage guard
 *
 * Shortcode: [readscope_thank_you]
 *
 * @package Readscope\Shortcodes
 */
class ThankYouShortcode
{
    /**
     * Register shortcode handler.
     *
     * @return void
     */
    public static function register(): void
    {
        add_shortcode('readscope_thank_you', [self::class, 'render']);
    }

    /**
     * Generate secure, time-bound HMAC token for thank-you page access.
     *
     * @param int      $bookingId
     * @param int|null $issuedAt
     * @return string
     */
    public static function generateThankYouToken(int $bookingId, ?int $issuedAt = null): string
    {
        $issuedAt = $issuedAt ?? time();
        $salt     = wp_salt('nonce');
        $hash     = hash_hmac('sha256', "readscope_thankyou_{$bookingId}_{$issuedAt}", $salt);

        return "{$issuedAt}.{$hash}";
    }

    /**
     * Verify whether the provided thank-you token is authentic and within the 72-hour validity window.
     *
     * @param int    $bookingId
     * @param string $token
     * @return bool
     */
    public static function verifyThankYouToken(int $bookingId, string $token): bool
    {
        if (empty($token) || !str_contains($token, '.')) {
            return false;
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return false;
        }

        $issuedAt = (int) $parts[0];
        $hash     = (string) $parts[1];
        $now      = time();

        // 72 hours validity (72 * 3600 = 259200 seconds), allow 5 minutes future clock skew
        if ($issuedAt <= 0 || ($now - $issuedAt) > 259200 || $issuedAt > ($now + 300)) {
            return false;
        }

        $salt         = wp_salt('nonce');
        $expectedHash = hash_hmac('sha256', "readscope_thankyou_{$bookingId}_{$issuedAt}", $salt);

        return hash_equals($expectedHash, $hash);
    }

    /**
     * Render the Thank You page.
     *
     * @param array<string, mixed>|string $atts
     * @return string
     */
    public static function render(array|string $atts = []): string
    {
        // 1. Prevent search indexing and intermediate proxy/CDN caching
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- cache-plugin convention
        }
        nocache_headers();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Capability URL authenticated by the HMAC thank-you token (verifyThankYouToken, hash_equals) below; a nonce cannot work in a shared link.
        $bookingCode = isset($_GET['code']) ? strtoupper(sanitize_text_field(wp_unslash((string) $_GET['code']))) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Capability URL authenticated by the HMAC thank-you token (verifyThankYouToken, hash_equals) below; a nonce cannot work in a shared link.
        $token       = isset($_GET['token']) ? sanitize_text_field(wp_unslash((string) $_GET['token'])) : '';

        $lookupPageId = (int) Config::get('lookup_page_id', 0);
        $lookupUrl    = $lookupPageId > 0 ? get_permalink($lookupPageId) : home_url('/');

        if (empty($bookingCode) || empty($token)) {
            return self::renderInvalidTokenState(__('Missing booking reference or security token.', 'the-readscope-tour-and-accommodation-booking'), $lookupUrl);
        }

        global $wpdb;
        $bookingsTable = $wpdb->prefix . 'readscope_bookings';
        $itemsTable    = $wpdb->prefix . 'readscope_booking_items';

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$bookingsTable} WHERE booking_code = %s LIMIT 1",
            $bookingCode
        ));

        if (!$booking) {
            return self::renderInvalidTokenState(__('No reservation was found matching this reference code.', 'the-readscope-tour-and-accommodation-booking'), $lookupUrl);
        }

        // Verify signed token
        if (!self::verifyThankYouToken((int) $booking->id, $token)) {
            return self::renderInvalidTokenState(__('We could not securely verify your booking confirmation link or the link has expired (links remain active for 72 hours).', 'the-readscope-tour-and-accommodation-booking'), $lookupUrl);
        }

        $items = (array) $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$itemsTable} WHERE booking_id = %d",
            $booking->id
        ));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

        // Fire thank you viewed hook for conversion integrations & audit
        do_action('readscope/thankyou_viewed', (int) $booking->id);

        $voucherToken = BookingLookupShortcode::generateVoucherToken((int) $booking->id, (string) $booking->customer_email, $booking->access_key ?? null);
        $voucherUrl   = add_query_arg([
            'action' => 'readscope_print_voucher',
            'code'   => $booking->booking_code,
            'token'  => $voucherToken,
        ], admin_url('admin-post.php'));

        $icsUrl = add_query_arg([
            'action' => 'readscope_download_ics',
            'code'   => $booking->booking_code,
            'token'  => $token,
        ], admin_url('admin-post.php'));

        $offlinePaymentInstructions = (string) Config::get('offline_payment_instructions', '');
        $enableDataLayer           = (bool) Config::get('enable_datalayer', false);
        $primaryColor              = (string) Config::get('primary_color', '#0d9488');

        ob_start();
        readscope_get_template('thank-you.php', [
            'booking'                    => $booking,
            'items'                      => $items,
            'voucherUrl'                 => $voucherUrl,
            'icsUrl'                     => $icsUrl,
            'lookupUrl'                  => $lookupUrl,
            'token'                      => $token,
            'offlinePaymentInstructions' => $offlinePaymentInstructions,
            'enableDataLayer'            => $enableDataLayer,
            'primaryColor'               => $primaryColor,
        ]);

        return ob_get_clean() ?: '';
    }

    /**
     * Render a polite error card when security token is invalid or expired.
     *
     * @param string $message
     * @param string $lookupUrl
     * @return string
     */
    protected static function renderInvalidTokenState(string $message, string $lookupUrl): string
    {
        ob_start();
        ?>
        <div class="readscope-thankyou-wrapper" style="max-width: 680px; margin: 40px auto; padding: 32px 24px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); font-family: inherit;">
            <div style="font-size: var(--trv-text-display); margin-bottom: 12px;">🔒</div>
            <h3 style="margin: 0 0 8px; color: #1e293b; font-size: var(--trv-text-2xl); font-weight: 700;"><?php esc_html_e('Unable to Display Booking Details', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
            <p style="color: #64748b; font-size: var(--trv-text-sm); line-height: 1.6; margin: 0 0 24px;">
                <?php echo esc_html($message); ?>
            </p>
            <p style="color: #475569; font-size: var(--trv-text-sm); margin: 0 0 20px;">
                <?php esc_html_e('You can view and manage your reservation anytime by entering your booking reference code and email on our self-service portal:', 'the-readscope-tour-and-accommodation-booking'); ?>
            </p>
            <a href="<?php echo esc_url($lookupUrl); ?>" class="button button-primary" style="display: inline-block; background: #0d9488; color: #ffffff; padding: 10px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: var(--trv-text-sm);">
                🔍 <?php esc_html_e('Track My Booking on Lookup Portal', 'the-readscope-tour-and-accommodation-booking'); ?>
            </a>
        </div>
        <?php
        return ob_get_clean() ?: '';
    }

    /**
     * Generate RFC 5545 iCalendar (.ics) string for a booking.
     *
     * @param int $bookingId
     * @return string
     */
    public static function generateIcsContent(int $bookingId): string
    {
        global $wpdb;
        $bookingsTable = $wpdb->prefix . 'readscope_bookings';
        $itemsTable    = $wpdb->prefix . 'readscope_booking_items';

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$bookingsTable} WHERE id = %d", $bookingId));
        if (!$booking) {
            return '';
        }

        $item = \Readscope\Support\BookingItems::summaryRow(\Readscope\Support\BookingItems::forBooking($bookingId), $bookingId);
        // phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

        $siteName = get_bloginfo('name') ?: 'Readscope Booking';
        $host     = wp_parse_url(home_url(), PHP_URL_HOST) ?: 'readscope.local';
        $title    = $item ? (string) $item->item_title : __('Tour / Hotel Reservation', 'the-readscope-tour-and-accommodation-booking');
        $code     = (string) $booking->booking_code;
        $uid      = "readscope-booking-{$bookingId}-{$code}@{$host}";
        $nowStamp = gmdate('Ymd\THis\Z');

        $checkInRaw  = $item ? (string) $item->check_in : (string) $booking->created_at;
        $checkOutRaw = ($item && !empty($item->check_out)) ? (string) $item->check_out : $checkInRaw;

        $dtStart = gmdate('Ymd', strtotime($checkInRaw));
        // iCal all-day DTEND is exclusive. A hotel stay ends on the check-out morning, so DTEND is the check-out
        // date itself; a tour's last day is inclusive, so DTEND is the day after it.
        $isStay = $item && in_array((string) $item->item_type, ['room', 'hotel_room'], true) && !empty($item->check_out);
        $dtEnd  = $isStay
            ? gmdate('Ymd', strtotime($checkOutRaw))
            : gmdate('Ymd', strtotime($checkOutRaw . ' +1 day'));

        $summary = sprintf(
            /* translators: 1: Item title, 2: Booking code */
            __('%1$s (Booking #%2$s)', 'the-readscope-tour-and-accommodation-booking'),
            $title,
            $code
        );

        // Real newlines here; escapeIcsText() turns them into the RFC 5545 "\n" escape.
        $description = implode("\n", [
            /* translators: %s: Booking reference code */
            sprintf(__('Reservation Reference: #%s', 'the-readscope-tour-and-accommodation-booking'), $code),
            /* translators: %s: Customer name */
            sprintf(__('Traveler: %s', 'the-readscope-tour-and-accommodation-booking'), $booking->customer_name),
            /* translators: %s: Total amount */
            sprintf(__('Total: %s', 'the-readscope-tour-and-accommodation-booking'), Money::format((float) $booking->total_amount)),
            /* translators: %s: Site name */
            sprintf(__('Organized by: %s', 'the-readscope-tour-and-accommodation-booking'), $siteName),
        ]);

        $icsStatus = match ((string) $booking->booking_status) {
            'confirmed', 'completed' => 'CONFIRMED',
            'cancelled'              => 'CANCELLED',
            default                  => 'TENTATIVE',
        };

        // Escape iCalendar text
        $summaryEsc     = self::escapeIcsText($summary);
        $descriptionEsc = self::escapeIcsText($description);
        $locationEsc    = self::escapeIcsText((string) home_url('/'));

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Readscope Booking//Readscope Booking System//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$nowStamp}",
            "DTSTART;VALUE=DATE:{$dtStart}",
            "DTEND;VALUE=DATE:{$dtEnd}",
            "SUMMARY:{$summaryEsc}",
            "DESCRIPTION:{$descriptionEsc}",
            "LOCATION:{$locationEsc}",
            "STATUS:{$icsStatus}",
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Escape special characters in iCalendar text fields according to RFC 5545.
     *
     * @param string $text
     * @return string
     */
    protected static function escapeIcsText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace(["\r\n", "\n", "\r"], '\n', $text);

        return $text;
    }
}
