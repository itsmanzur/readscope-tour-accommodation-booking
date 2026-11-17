<?php

declare(strict_types=1);

namespace Readscope\Admin;

use Readscope\Config\Config;
use Readscope\Shortcodes\CurrencySwitcherShortcode;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class SettingsPage
 *
 * Renders and saves Readscope Booking global settings with tabbed UI and test email dispatcher.
 *
 * @package Readscope\Admin
 */
class SettingsPage
{
    /**
     * Read a whitelisted choice field from $_POST, falling back to $default when absent or not allowed.
     *
     * @param array<int, string> $allowed
     */
    protected static function choice(string $field, array $allowed, string $default): string
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only called from saveSettings() after handleSave() verified the nonce and capability; the value is matched against a whitelist.
        $raw = isset($_POST[$field]) ? (string) wp_unslash($_POST[$field]) : '';

        // Exact match first (separators such as "," or " " cannot survive sanitize_key()), then the key-normalized form.
        if (in_array($raw, $allowed, true)) {
            return $raw;
        }

        $normalized = sanitize_key($raw);

        return in_array($normalized, $allowed, true) ? $normalized : $default;
    }

    /**
     * Accept only simple CSS lengths (12px, 0.5rem, 50%) for the corner radius; otherwise the 8px default.
     */
    protected static function borderRadius(string $value): string
    {
        $value = trim($value);

        return preg_match('/^\d{1,3}(?:\.\d{1,2})?(?:px|rem|em|%)$/', $value) === 1 ? $value : '8px';
    }

    /**
     * Keep a three-letter ISO-style code, otherwise fall back to the previously saved value.
     */
    protected static function sanitizeCurrencyCode(string $raw, string $fallback): string
    {
        $code = strtoupper(preg_replace('/[^A-Za-z]/', '', sanitize_text_field($raw)) ?? '');

        return strlen($code) === 3 ? $code : ($fallback !== '' ? $fallback : 'USD');
    }

    /**
     * admin-post handler: verify, persist, then redirect (Post/Redirect/Get) so a browser refresh cannot
     * resubmit the form.
     *
     * @return void
     */
    public static function handleSave(): void
    {
        check_admin_referer('readscope_settings_action', 'readscope_settings_nonce');

        if (!current_user_can('manage_readscope_settings')) {
            wp_die(esc_html__('Unauthorized access.', 'the-readscope-tour-and-accommodation-booking'));
        }

        self::saveSettings();

        wp_safe_redirect(add_query_arg('settings-updated', '1', admin_url('admin.php?page=readscope-settings')));
        exit;
    }

    /**
     * Sanitize the submitted settings form ($_POST) and persist it. Caller must have verified nonce and capability.
     *
     * @return void
     */
    public static function saveSettings(): void
    {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- handleSave() runs check_admin_referer() and current_user_can() before calling this method.
        $currentSettings = get_option('readscope_settings', Config::getDefaults());

        $newSettings = [
            'currency'                => self::sanitizeCurrencyCode(sanitize_text_field(wp_unslash($_POST['currency'] ?? '')), (string) ($currentSettings['currency'] ?? 'USD')),
            'currency_symbol'         => sanitize_text_field(wp_unslash($_POST['currency_symbol'] ?? '$')),
            'currency_position'       => self::choice('currency_position', ['left', 'right', 'left_space', 'right_space'], 'left'),
            'number_of_decimals'      => min(4, absint($_POST['number_of_decimals'] ?? 2)),
            'decimal_separator'       => self::choice('decimal_separator', ['.', ','], '.'),
            'thousand_separator'      => self::choice('thousand_separator', [',', '.', ' ', ''], ','),
            'plugin_language'         => self::choice('plugin_language', ['default', 'en', 'bn'], 'default'),
            'use_bangla_digits'       => isset($_POST['use_bangla_digits']) ? 1 : 0,
            'travel_homepage'         => isset($_POST['travel_homepage']) ? 1 : 0,
            'tax_enabled'           => isset($_POST['tax_enabled']) ? 1 : 0,
            'tax_label'               => sanitize_text_field(wp_unslash($_POST['tax_label'] ?? 'Tax')),
            'tax_rate'                => min(100.0, max(0.0, (float) sanitize_text_field(wp_unslash($_POST['tax_rate'] ?? '0')))),
            'tax_mode'                => self::choice('tax_mode', ['exclusive', 'inclusive'], 'exclusive'),
            'tax_applies_to'          => self::choice('tax_applies_to', ['all', 'tours', 'rooms'], 'all'),
            'default_booking_status'     => self::choice('default_booking_status', ['pending', 'confirmed'], 'pending'),
            'redirect_after_booking'     => self::choice('redirect_after_booking', ['inline', 'thankyou'], 'inline'),
            'thankyou_page_id'           => absint($_POST['thankyou_page_id'] ?? 0),
            'enable_datalayer'           => isset($_POST['enable_datalayer']) ? 1 : 0,
            'allow_cancel_requests'      => isset($_POST['allow_cancel_requests']) ? 1 : 0,
            'customer_self_cancel_hours' => absint($_POST['customer_self_cancel_hours'] ?? 0),
            'pending_expiry_hours'       => min(8760, absint($_POST['pending_expiry_hours'] ?? 0)),
            'require_email_verification' => isset($_POST['require_email_verification']) ? 1 : 0,
            'unverified_expiry_minutes'  => max(5, min(1440, absint($_POST['unverified_expiry_minutes'] ?? 60))),
            'email_from_name'            => sanitize_text_field(wp_unslash($_POST['email_from_name'] ?? get_bloginfo('name'))),
            'email_from_address'         => sanitize_email(wp_unslash($_POST['email_from_address'] ?? get_option('admin_email'))),
            'email_notification_address' => sanitize_email(wp_unslash($_POST['email_notification_address'] ?? get_option('admin_email'))),
            'reminder_days_before'       => max(0, min(30, (int) sanitize_text_field(wp_unslash($_POST['reminder_days_before'] ?? '2')))),
            'lookup_page_id'             => absint($_POST['lookup_page_id'] ?? 0),
            'my_bookings_page_id'        => absint($_POST['my_bookings_page_id'] ?? 0),
            'offline_payment_instructions' => sanitize_textarea_field(wp_unslash($_POST['offline_payment_instructions'] ?? '')),
            'bank_transfer_enabled'        => isset($_POST['bank_transfer_enabled']) ? 1 : 0,
            'bank_name'                    => sanitize_text_field(wp_unslash($_POST['bank_name'] ?? '')),
            'bank_account_name'            => sanitize_text_field(wp_unslash($_POST['bank_account_name'] ?? '')),
            'bank_account_number'          => sanitize_text_field(wp_unslash($_POST['bank_account_number'] ?? '')),
            'bank_iban'                    => sanitize_text_field(wp_unslash($_POST['bank_iban'] ?? '')),
            'bank_swift_routing'           => sanitize_text_field(wp_unslash($_POST['bank_swift_routing'] ?? '')),
            'bank_branch'                  => sanitize_text_field(wp_unslash($_POST['bank_branch'] ?? '')),
            'bank_instructions'            => sanitize_textarea_field(wp_unslash($_POST['bank_instructions'] ?? '')),
            'cash_on_arrival_enabled'      => isset($_POST['cash_on_arrival_enabled']) ? 1 : 0,
            'cash_on_arrival_instructions' => sanitize_textarea_field(wp_unslash($_POST['cash_on_arrival_instructions'] ?? '')),
            // 1. Customer: Booking Received
            'email_customer_booking_received_enabled'            => isset($_POST['email_customer_booking_received_enabled']) ? 1 : 0,
            'email_customer_booking_received_subject'            => sanitize_text_field(wp_unslash($_POST['email_customer_booking_received_subject'] ?? '')),
            'email_customer_booking_received_heading'            => sanitize_text_field(wp_unslash($_POST['email_customer_booking_received_heading'] ?? '')),
            'email_customer_booking_received_additional_content' => wp_kses_post(wp_unslash($_POST['email_customer_booking_received_additional_content'] ?? '')),
            // 2. Customer: Booking Confirmed
            'email_customer_booking_confirmed_enabled'           => isset($_POST['email_customer_booking_confirmed_enabled']) ? 1 : 0,
            'email_customer_booking_confirmed_subject'           => sanitize_text_field(wp_unslash($_POST['email_customer_booking_confirmed_subject'] ?? '')),
            'email_customer_booking_confirmed_heading'           => sanitize_text_field(wp_unslash($_POST['email_customer_booking_confirmed_heading'] ?? '')),
            'email_customer_booking_confirmed_additional_content'=> wp_kses_post(wp_unslash($_POST['email_customer_booking_confirmed_additional_content'] ?? '')),
            // 3. Customer: Booking Cancelled
            'email_customer_booking_cancelled_enabled'           => isset($_POST['email_customer_booking_cancelled_enabled']) ? 1 : 0,
            'email_customer_booking_cancelled_subject'           => sanitize_text_field(wp_unslash($_POST['email_customer_booking_cancelled_subject'] ?? '')),
            'email_customer_booking_cancelled_heading'           => sanitize_text_field(wp_unslash($_POST['email_customer_booking_cancelled_heading'] ?? '')),
            'email_customer_booking_cancelled_additional_content'=> wp_kses_post(wp_unslash($_POST['email_customer_booking_cancelled_additional_content'] ?? '')),
            // 4. Customer: Trip Reminder
            'email_customer_trip_reminder_enabled'               => isset($_POST['email_customer_trip_reminder_enabled']) ? 1 : 0,
            'email_customer_trip_reminder_subject'               => sanitize_text_field(wp_unslash($_POST['email_customer_trip_reminder_subject'] ?? '')),
            'email_customer_trip_reminder_heading'               => sanitize_text_field(wp_unslash($_POST['email_customer_trip_reminder_heading'] ?? '')),
            'email_customer_trip_reminder_additional_content'    => wp_kses_post(wp_unslash($_POST['email_customer_trip_reminder_additional_content'] ?? '')),
            // 5. Admin: New Booking Alert
            'email_admin_new_booking_enabled'                    => isset($_POST['email_admin_new_booking_enabled']) ? 1 : 0,
            'email_admin_new_booking_subject'                    => sanitize_text_field(wp_unslash($_POST['email_admin_new_booking_subject'] ?? '')),
            'email_admin_new_booking_heading'                    => sanitize_text_field(wp_unslash($_POST['email_admin_new_booking_heading'] ?? '')),
            'email_admin_new_booking_additional_content'         => wp_kses_post(wp_unslash($_POST['email_admin_new_booking_additional_content'] ?? '')),
            // 6. Admin: Booking Cancelled Alert
            'email_admin_booking_cancelled_enabled'              => isset($_POST['email_admin_booking_cancelled_enabled']) ? 1 : 0,
            'email_admin_booking_cancelled_subject'              => sanitize_text_field(wp_unslash($_POST['email_admin_booking_cancelled_subject'] ?? '')),
            'email_admin_booking_cancelled_heading'              => sanitize_text_field(wp_unslash($_POST['email_admin_booking_cancelled_heading'] ?? '')),
            'email_admin_booking_cancelled_additional_content'   => wp_kses_post(wp_unslash($_POST['email_admin_booking_cancelled_additional_content'] ?? '')),
            // 7. Admin: New Inquiry Alert
            'email_admin_new_inquiry_enabled'                    => isset($_POST['email_admin_new_inquiry_enabled']) ? 1 : 0,
            'email_admin_new_inquiry_subject'                    => sanitize_text_field(wp_unslash($_POST['email_admin_new_inquiry_subject'] ?? '')),
            'email_admin_new_inquiry_heading'                    => sanitize_text_field(wp_unslash($_POST['email_admin_new_inquiry_heading'] ?? '')),
            'email_admin_new_inquiry_additional_content'         => wp_kses_post(wp_unslash($_POST['email_admin_new_inquiry_additional_content'] ?? '')),
            'primary_color'              => sanitize_hex_color(wp_unslash((string)($_POST['primary_color'] ?? '#0d9488'))) ?: '#0d9488',
            'primary_hover'              => sanitize_hex_color(wp_unslash((string)($_POST['primary_hover'] ?? '#0f766e'))) ?: '#0f766e',
            'accent_color'               => sanitize_hex_color(wp_unslash((string)($_POST['accent_color'] ?? '#f59e0b'))) ?: '#f59e0b',
            'border_radius'              => self::borderRadius(sanitize_text_field(wp_unslash($_POST['border_radius'] ?? ''))),
            'font_family'                => self::choice('font_family', \Readscope\Support\Typography::MODES, 'anek'),
            'button_text_color'          => sanitize_hex_color(wp_unslash((string)($_POST['button_text_color'] ?? '#ffffff'))) ?: '#ffffff',
            'enable_schema'              => isset($_POST['enable_schema']) ? 1 : 0,
            'enable_opengraph'           => isset($_POST['enable_opengraph']) ? 1 : 0,
            'webhook_url'                => esc_url_raw(wp_unslash($_POST['webhook_url'] ?? '')),
            'webhook_secret'             => sanitize_text_field(wp_unslash($_POST['webhook_secret'] ?? '')),
            'webhook_events'             => isset($_POST['webhook_events']) && is_array($_POST['webhook_events']) ? array_map('sanitize_text_field', wp_unslash($_POST['webhook_events'])) : [],
            'webhook_include_phone'      => isset($_POST['webhook_include_phone']) ? 1 : 0,
            'webhook_include_message'    => isset($_POST['webhook_include_message']) ? 1 : 0,
            'require_consent'            => isset($_POST['require_consent']) ? 1 : 0,
            'privacy_policy_page_id'     => absint($_POST['privacy_policy_page_id'] ?? 0),
            'terms_page_id'              => absint($_POST['terms_page_id'] ?? 0),
            'consent_label'              => sanitize_text_field(wp_unslash($_POST['consent_label'] ?? 'I agree to the {privacy} and {terms}')),
            'store_ip'                   => isset($_POST['store_ip']) ? 1 : 0,
            // Retention now counts days from the end of the trip; the old month value is cleared once days are saved.
            'anonymize_after_days'       => min(3650, max(0, (int) sanitize_text_field(wp_unslash($_POST['anonymize_after_days'] ?? '0')))),
            'anonymize_after_months'     => 0,
            'retention_statuses'         => array_values(array_intersect(
                array_map('sanitize_key', (array) wp_unslash($_POST['retention_statuses'] ?? [])),
                \Readscope\Services\PrivacyService::RETENTION_STATUS_CHOICES
            )),
            'delete_inquiries_after_months' => max(0, (int) sanitize_text_field(wp_unslash($_POST['delete_inquiries_after_months'] ?? '0'))),
            'proxy_mode'                 => in_array(sanitize_text_field(wp_unslash($_POST['proxy_mode'] ?? '')), ['cloudflare', 'reverse_proxy'], true) ? sanitize_text_field(wp_unslash($_POST['proxy_mode'])) : 'disabled',
            'trusted_proxies'            => sanitize_textarea_field(wp_unslash($_POST['trusted_proxies'] ?? '')),
            'trust_proxy_headers'        => (isset($_POST['proxy_mode']) && in_array($_POST['proxy_mode'], ['cloudflare', 'reverse_proxy'], true)) ? 1 : 0,
            'erase_data_on_uninstall'    => isset($_POST['erase_data_on_uninstall']) ? 1 : 0,
        ];

        $merged = array_merge($currentSettings, $newSettings);
        update_option('readscope_settings', $merged);
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Render the settings page view.
     *
     * @return void
     */
    public static function render(): void
    {
        if (!current_user_can('manage_readscope_settings')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'the-readscope-tour-and-accommodation-booking'), 403);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag that only shows the "saved" notice; current_user_can() is checked above.
        if (isset($_GET['settings-updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'the-readscope-tour-and-accommodation-booking') . '</p></div>';
        }

        $currency          = Config::get('currency', 'USD');
        $currencySymbol    = Config::get('currency_symbol', '$');
        $currencyPos       = Config::get('currency_position', 'left');
        $numDecimals       = (int) Config::get('number_of_decimals', 2);
        $decimalSep        = (string) Config::get('decimal_separator', '.');
        $thousandSep       = (string) Config::get('thousand_separator', ',');
        $pluginLanguage    = Config::get('plugin_language', 'default');
        $useBanglaDigits   = Config::get('use_bangla_digits', 0);
        $travelHomepage    = Config::get('travel_homepage', 0);
        $taxEnabled      = Config::get('tax_enabled', 0);
        $taxLabel          = Config::get('tax_label', 'Tax');
        $taxRate           = Config::get('tax_rate', 0.0);
        $taxMode           = Config::get('tax_mode', 'exclusive');
        $taxAppliesTo      = Config::get('tax_applies_to', 'all');
        $defaultStatus           = Config::get('default_booking_status', 'pending');
        $redirectAfterBooking    = Config::get('redirect_after_booking', 'inline');
        $thankyouPageId          = (int) Config::get('thankyou_page_id', 0);
        $enableDatalayer         = Config::get('enable_datalayer', 0);
        $allowCancelRequests     = Config::get('allow_cancel_requests', 1);
        $customerSelfCancelHours = (int) Config::get('customer_self_cancel_hours', 0);
        $pendingExpiryHours      = (int) Config::get('pending_expiry_hours', 0);
        $requireEmailVerify      = Config::get('require_email_verification', 0);
        $unverifiedExpiryMinutes = (int) Config::get('unverified_expiry_minutes', 60);
        $fromName                = Config::get('email_from_name', get_bloginfo('name'));
        $fromEmail         = Config::get('email_from_address', get_option('admin_email'));
        $notifyEmail       = Config::get('email_notification_address', get_option('admin_email'));
        $reminderDays      = (int) Config::get('reminder_days_before', 2);
        $lookupPageId      = (int) Config::get('lookup_page_id', 0);
        $myBookingsPageId  = (int) Config::get('my_bookings_page_id', 0);
        $offlinePayment    = (string) Config::get('offline_payment_instructions', '');
        $bankEnabled       = (bool) Config::get('bank_transfer_enabled', 1);
        $bankName          = (string) Config::get('bank_name', '');
        $bankAccountName   = (string) Config::get('bank_account_name', '');
        $bankAccountNumber  = (string) Config::get('bank_account_number', '');
        $bankIban          = (string) Config::get('bank_iban', '');
        $bankSwiftRouting  = (string) Config::get('bank_swift_routing', '');
        $bankBranch        = (string) Config::get('bank_branch', '');
        $bankInstructions  = (string) Config::get('bank_instructions', '');
        $cashEnabled       = (bool) Config::get('cash_on_arrival_enabled', 1);
        $cashInstructions  = (string) Config::get('cash_on_arrival_instructions', '');
        $requireConsent        = Config::get('require_consent', 0);
        $privacyPolicyPageId   = (int) Config::get('privacy_policy_page_id', 0);
        $termsPageId           = (int) Config::get('terms_page_id', 0);
        $consentLabel          = (string) Config::get('consent_label', 'I agree to the {privacy} and {terms}');
        $storeIp               = Config::get('store_ip', 1);
        $anonymizeAfterDays    = \Readscope\Services\PrivacyService::retentionDays();
        $retentionStatuses     = \Readscope\Services\PrivacyService::retentionStatuses();
        $deleteInqAfterMonths  = (int) Config::get('delete_inquiries_after_months', 0);
        $webhookIncludePhone   = Config::get('webhook_include_phone', 1);
        $webhookIncludeMessage = Config::get('webhook_include_message', 1);
        $primaryColor      = Config::get('primary_color', '#0d9488');
        $primaryHover      = Config::get('primary_hover', '#0f766e');
        $accentColor       = Config::get('accent_color', '#f59e0b');
        $borderRadius      = Config::get('border_radius', '8px');
        $fontFamily        = \Readscope\Support\Typography::mode();
        $btnTextColor      = Config::get('button_text_color', '#ffffff');
        $enableSchema      = Config::get('enable_schema', 1);
        $enableOpenGraph   = Config::get('enable_opengraph', 1);
        $webhookUrl        = Config::get('webhook_url', '');
        $webhookSecret     = Config::get('webhook_secret', '');
        $webhookEvents     = (array) Config::get('webhook_events', ['booking.created', 'booking.status_changed', 'inquiry.created']);
        $proxyMode         = Config::get('proxy_mode', 'disabled');
        $trustedProxies    = Config::get('trusted_proxies', '');
        $eraseData         = Config::get('erase_data_on_uninstall', 0);
        $currencies        = CurrencySwitcherShortcode::getCurrencies();

        $emailTemplates = [
            'customer_booking_received' => [
                'title'       => __('Customer: Booking Request Received (Pending)', 'the-readscope-tour-and-accommodation-booking'),
                'recipient'   => __('Customer (Booker)', 'the-readscope-tour-and-accommodation-booking'),
                'desc'        => __('Sent to traveler immediately when a new booking is placed in Pending status.', 'the-readscope-tour-and-accommodation-booking'),
                'enabled'     => (bool) Config::get('email_customer_booking_received_enabled', true),
                'subject'     => (string) Config::get('email_customer_booking_received_subject', 'Your Booking Request Received #{booking_code} - {site_name}'),
                'heading'     => (string) Config::get('email_customer_booking_received_heading', 'Booking Request Received'),
                'content'     => (string) Config::get('email_customer_booking_received_additional_content', ''),
            ],
            'customer_booking_confirmed' => [
                'title'       => __('Customer: Booking Confirmed', 'the-readscope-tour-and-accommodation-booking'),
                'recipient'   => __('Customer (Booker)', 'the-readscope-tour-and-accommodation-booking'),
                'desc'        => __('Sent to traveler when a booking is confirmed. Includes printable voucher access.', 'the-readscope-tour-and-accommodation-booking'),
                'enabled'     => (bool) Config::get('email_customer_booking_confirmed_enabled', true),
                'subject'     => (string) Config::get('email_customer_booking_confirmed_subject', 'Your Booking Confirmation #{booking_code} - {site_name}'),
                'heading'     => (string) Config::get('email_customer_booking_confirmed_heading', 'Booking Confirmed!'),
                'content'     => (string) Config::get('email_customer_booking_confirmed_additional_content', ''),
            ],
            'customer_booking_cancelled' => [
                'title'       => __('Customer: Booking Cancelled', 'the-readscope-tour-and-accommodation-booking'),
                'recipient'   => __('Customer (Booker)', 'the-readscope-tour-and-accommodation-booking'),
                'desc'        => __('Sent to traveler when a reservation is cancelled.', 'the-readscope-tour-and-accommodation-booking'),
                'enabled'     => (bool) Config::get('email_customer_booking_cancelled_enabled', true),
                'subject'     => (string) Config::get('email_customer_booking_cancelled_subject', 'Your Booking #{booking_code} has been Cancelled - {site_name}'),
                'heading'     => (string) Config::get('email_customer_booking_cancelled_heading', 'Booking Cancelled'),
                'content'     => (string) Config::get('email_customer_booking_cancelled_additional_content', ''),
            ],
            'customer_trip_reminder' => [
                'title'       => __('Customer: Upcoming Trip Reminder', 'the-readscope-tour-and-accommodation-booking'),
                'recipient'   => __('Customer (Booker)', 'the-readscope-tour-and-accommodation-booking'),
                'desc'        => __('Automated reminder dispatched X days prior to check-in/departure.', 'the-readscope-tour-and-accommodation-booking'),
                'enabled'     => (bool) Config::get('email_customer_trip_reminder_enabled', true),
                'subject'     => (string) Config::get('email_customer_trip_reminder_subject', 'Upcoming Trip Reminder: #{booking_code} - {site_name}'),
                'heading'     => (string) Config::get('email_customer_trip_reminder_heading', 'Your Trip is Coming Up Soon!'),
                'content'     => (string) Config::get('email_customer_trip_reminder_additional_content', ''),
            ],
            'admin_new_booking' => [
                'title'       => __('Admin: New Booking Notification', 'the-readscope-tour-and-accommodation-booking'),
                'recipient'   => __('Admin Notification Recipient', 'the-readscope-tour-and-accommodation-booking'),
                'desc'        => __('Sent to site administrator when a new booking is submitted.', 'the-readscope-tour-and-accommodation-booking'),
                'enabled'     => (bool) Config::get('email_admin_new_booking_enabled', true),
                'subject'     => (string) Config::get('email_admin_new_booking_subject', '[New Booking] #{booking_code} by {customer_name}'),
                'heading'     => (string) Config::get('email_admin_new_booking_heading', 'New Booking Received'),
                'content'     => (string) Config::get('email_admin_new_booking_additional_content', ''),
            ],
            'admin_booking_cancelled' => [
                'title'       => __('Admin: Booking Cancelled Alert', 'the-readscope-tour-and-accommodation-booking'),
                'recipient'   => __('Admin Notification Recipient', 'the-readscope-tour-and-accommodation-booking'),
                'desc'        => __('Sent to site administrator when a booking is cancelled.', 'the-readscope-tour-and-accommodation-booking'),
                'enabled'     => (bool) Config::get('email_admin_booking_cancelled_enabled', true),
                'subject'     => (string) Config::get('email_admin_booking_cancelled_subject', '[Booking Cancelled] #{booking_code} - {customer_name}'),
                'heading'     => (string) Config::get('email_admin_booking_cancelled_heading', 'Booking Cancelled Notification'),
                'content'     => (string) Config::get('email_admin_booking_cancelled_additional_content', ''),
            ],
            'admin_new_inquiry' => [
                'title'       => __('Admin: New Customer Inquiry', 'the-readscope-tour-and-accommodation-booking'),
                'recipient'   => __('Admin Notification Recipient', 'the-readscope-tour-and-accommodation-booking'),
                'desc'        => __('Sent to site administrator when a traveler submits the trip inquiry popup form.', 'the-readscope-tour-and-accommodation-booking'),
                'enabled'     => (bool) Config::get('email_admin_new_inquiry_enabled', true),
                'subject'     => (string) Config::get('email_admin_new_inquiry_subject', '[New Inquiry] {item_title} from {customer_name}'),
                'heading'     => (string) Config::get('email_admin_new_inquiry_heading', 'New Traveler Trip Inquiry'),
                'content'     => (string) Config::get('email_admin_new_inquiry_additional_content', ''),
            ],
        ];
        ?>
        <div class="wrap readscope-admin-wrap">
            <div class="readscope-page-header">
                <div class="readscope-page-header__text">
                    <h1><?php esc_html_e('Readscope Booking Settings', 'the-readscope-tour-and-accommodation-booking'); ?></h1>
                    <p class="readscope-subtitle"><?php esc_html_e('Configure your currency rates, booking defaults, design styling, SEO, and Webhooks.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                </div>
            </div>
            <hr class="wp-header-end">

            <!-- Settings Tabs Navigation -->
            <h2 class="nav-tab-wrapper readscope-settings-tabs">
                <a href="#tab-general" class="nav-tab nav-tab-active" data-tab="general">
                    <span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e('General & Currency', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
                <a href="#tab-appearance" class="nav-tab" data-tab="appearance">
                    <span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e('Design & SEO', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
                <a href="#tab-emails" class="nav-tab" data-tab="emails">
                    <span class="dashicons dashicons-email"></span> <?php esc_html_e('Email Notifications', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
                <a href="#tab-webhooks" class="nav-tab" data-tab="webhooks">
                    <span class="dashicons dashicons-rest-api"></span> <?php esc_html_e('Webhooks & API', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
                <a href="#tab-privacy" class="nav-tab" data-tab="privacy">
                    <span class="dashicons dashicons-privacy"></span> <?php esc_html_e('Privacy & GDPR', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
                <a href="#tab-advanced" class="nav-tab" data-tab="advanced">
                    <span class="dashicons dashicons-shield"></span> <?php esc_html_e('Advanced & System', 'the-readscope-tour-and-accommodation-booking'); ?>
                </a>
            </h2>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="readscope-settings-form">
                <input type="hidden" name="action" value="readscope_save_settings">
                <?php wp_nonce_field('readscope_settings_action', 'readscope_settings_nonce'); ?>

                <!-- TAB 1: General & Currency -->
                <div id="settings-tab-general" class="readscope-settings-tab-pane">
                    <div class="readscope-settings-box">
                        <h2><?php esc_html_e('Base Currency & Pricing Options', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="currency"><?php esc_html_e('Base Currency Code', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="currency" id="currency">
                                        <option value="USD" <?php selected($currency, 'USD'); ?>>USD - US Dollar ($)</option>
                                        <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR - Euro (€)</option>
                                        <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP - British Pound (£)</option>
                                        <option value="BDT" <?php selected($currency, 'BDT'); ?>>BDT - Bangladeshi Taka (৳)</option>
                                        <option value="INR" <?php selected($currency, 'INR'); ?>>INR - Indian Rupee (₹)</option>
                                        <option value="AUD" <?php selected($currency, 'AUD'); ?>>AUD - Australian Dollar ($)</option>
                                        <option value="CAD" <?php selected($currency, 'CAD'); ?>>CAD - Canadian Dollar ($)</option>
                                        <option value="AED" <?php selected($currency, 'AED'); ?>>AED - UAE Dirham (AED)</option>
                                    </select>
                                    <p class="description"><?php esc_html_e('The main currency used to store prices in the database.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="currency_symbol"><?php esc_html_e('Currency Symbol', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="currency_symbol" type="text" id="currency_symbol" value="<?php echo esc_attr($currencySymbol); ?>" class="small-text readscope-input-symbol">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="currency_position"><?php esc_html_e('Symbol Position', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="currency_position" id="currency_position">
                                        <option value="left" <?php selected($currencyPos, 'left'); ?>><?php esc_html_e('Left ($99)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="right" <?php selected($currencyPos, 'right'); ?>><?php esc_html_e('Right (99$)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="left_space" <?php selected($currencyPos, 'left_space'); ?>><?php esc_html_e('Left with space ($ 99)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="right_space" <?php selected($currencyPos, 'right_space'); ?>><?php esc_html_e('Right with space (99 $)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="number_of_decimals"><?php esc_html_e('Number Format', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <div class="readscope-inline-fields">
                                        <input name="number_of_decimals" type="number" id="number_of_decimals" min="0" max="4" value="<?php echo esc_attr((string) $numDecimals); ?>" class="small-text">
                                        <span><?php esc_html_e('decimals', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                        <select name="decimal_separator" id="decimal_separator" aria-label="<?php esc_attr_e('Decimal separator', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                            <option value="." <?php selected($decimalSep, '.'); ?>><?php esc_html_e('Decimal: . (period)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                            <option value="," <?php selected($decimalSep, ','); ?>><?php esc_html_e('Decimal: , (comma)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        </select>
                                        <select name="thousand_separator" id="thousand_separator" aria-label="<?php esc_attr_e('Thousands separator', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                            <option value="," <?php selected($thousandSep, ','); ?>><?php esc_html_e('Thousands: , (comma)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                            <option value="." <?php selected($thousandSep, '.'); ?>><?php esc_html_e('Thousands: . (period)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                            <option value=" " <?php selected($thousandSep, ' '); ?>><?php esc_html_e('Thousands: space', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                            <option value="" <?php selected($thousandSep, ''); ?>><?php esc_html_e('Thousands: none', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Tax & VAT Configuration', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Tax', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label>
                                        <input name="tax_enabled" type="checkbox" id="tax_enabled" value="1" <?php checked($taxEnabled, 1); ?>>
                                        <?php esc_html_e('Calculate tax on bookings', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="tax_label"><?php esc_html_e('Tax Name / Label', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="tax_label" type="text" id="tax_label" value="<?php echo esc_attr($taxLabel); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. VAT / GST / Sales Tax', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="tax_rate"><?php esc_html_e('Tax Rate (%)', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="tax_rate" type="number" step="0.01" min="0" max="100" id="tax_rate" value="<?php echo esc_attr((string) $taxRate); ?>" class="small-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="tax_mode"><?php esc_html_e('Tax Mode', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="tax_mode" id="tax_mode">
                                        <option value="exclusive" <?php selected($taxMode, 'exclusive'); ?>><?php esc_html_e('Exclusive (Tax added on top of subtotal)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="inclusive" <?php selected($taxMode, 'inclusive'); ?>><?php esc_html_e('Inclusive (Tax extracted from price)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="tax_applies_to"><?php esc_html_e('Tax Applies To', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="tax_applies_to" id="tax_applies_to">
                                        <option value="all" <?php selected($taxAppliesTo, 'all'); ?>><?php esc_html_e('All Bookings (Tours & Rooms)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="tours" <?php selected($taxAppliesTo, 'tours'); ?>><?php esc_html_e('Tours Only', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="rooms" <?php selected($taxAppliesTo, 'rooms'); ?>><?php esc_html_e('Rooms / Accommodations Only', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Supported Multi-Currencies for Frontend Switcher', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('These exchange rates are used when travelers switch currencies with [readscope_currency_switcher]:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        
                        <table class="widefat striped readscope-settings-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Currency', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                    <th><?php esc_html_e('Symbol', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                    <th><?php esc_html_e('Exchange Rate (vs 1 Base)', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currencies as $code => $c) : ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($code); ?></strong> (<?php echo esc_html($c['name']); ?>)</td>
                                        <td><?php echo esc_html($c['symbol']); ?></td>
                                        <td><?php echo esc_html((string)$c['rate']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Booking Defaults & Post-Booking Experience', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="default_booking_status"><?php esc_html_e('Default Booking Status', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="default_booking_status" id="default_booking_status">
                                        <option value="pending" <?php selected($defaultStatus, 'pending'); ?>><?php esc_html_e('Pending (Awaiting Admin Confirmation)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="confirmed" <?php selected($defaultStatus, 'confirmed'); ?>><?php esc_html_e('Instant Confirmed', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="redirect_after_booking"><?php esc_html_e('Post-Booking Action', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="redirect_after_booking" id="redirect_after_booking">
                                        <option value="inline" <?php selected($redirectAfterBooking, 'inline'); ?>><?php esc_html_e('Inline Message (Stay on Booking Page)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="thankyou" <?php selected($redirectAfterBooking, 'thankyou'); ?>><?php esc_html_e('Redirect to Dedicated Thank You / Confirmation Page', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Choose what happens immediately after a customer submits the frontend booking form.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="thankyou_page_id"><?php esc_html_e('Thank You / Confirmation Page', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <?php
                                    wp_dropdown_pages([
                                        'name'              => 'thankyou_page_id',
                                        'id'                => 'thankyou_page_id',
                                        'selected'          => absint($thankyouPageId),
                                        'show_option_none'  => esc_html__('— Select Page with [readscope_thank_you] —', 'the-readscope-tour-and-accommodation-booking'),
                                        'option_none_value' => '0',
                                    ]);
                                    ?>
                                    <p class="description"><?php esc_html_e('Page containing the [readscope_thank_you] shortcode where travelers are redirected.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Traveler Self-Service & Cancellation Policy', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Allow Cancellation Requests', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="allow_cancel_requests">
                                        <input name="allow_cancel_requests" type="checkbox" id="allow_cancel_requests" value="1" <?php checked($allowCancelRequests, 1); ?>>
                                        <strong><?php esc_html_e('Allow travelers to request cancellation via the booking lookup portal.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('When enabled, travelers can initiate a cancellation request on the track booking page.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="customer_self_cancel_hours"><?php esc_html_e('Self-Cancellation Window (Hours Before Check-in)', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="customer_self_cancel_hours" type="number" id="customer_self_cancel_hours" min="0" max="720" value="<?php echo esc_attr((string) $customerSelfCancelHours); ?>" class="small-text">
                                    <?php esc_html_e('hours before trip departure / hotel check-in.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    <p class="description"><?php esc_html_e('Set to 0 (default) to require admin review for all cancellations. If set to > 0, unpaid bookings can be instantly cancelled by the traveler if check-in is at least N hours away, immediately releasing inventory.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Booking Hygiene & Anti-Abuse', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Require Email Confirmation', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="require_email_verification">
                                        <input name="require_email_verification" type="checkbox" id="require_email_verification" value="1" <?php checked($requireEmailVerify, 1); ?>>
                                        <strong><?php esc_html_e('Travelers must click a link emailed to them before the booking is secured.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('Stops fake bookings from locking your inventory and from spamming third-party inboxes. Staff alerts are sent only after confirmation.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="unverified_expiry_minutes"><?php esc_html_e('Unconfirmed Booking Expiry', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="unverified_expiry_minutes" type="number" id="unverified_expiry_minutes" min="5" max="1440" value="<?php echo esc_attr((string) $unverifiedExpiryMinutes); ?>" class="small-text">
                                    <?php esc_html_e('minutes, then the booking is cancelled and its spots released.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pending_expiry_hours"><?php esc_html_e('Auto-Cancel Unpaid Pending Bookings', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="pending_expiry_hours" type="number" id="pending_expiry_hours" min="0" max="8760" value="<?php echo esc_attr((string) $pendingExpiryHours); ?>" class="small-text">
                                    <?php esc_html_e('hours after creation.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    <p class="description"><?php esc_html_e('0 (default) keeps pending bookings until you confirm or cancel them manually.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Language & Localization / ভাষা ও লোকালাইজেশন', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="plugin_language"><?php esc_html_e('Plugin Language / প্লাগিনের ভাষা', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="plugin_language" id="plugin_language">
                                        <option value="default" <?php selected($pluginLanguage, 'default'); ?>><?php
                                        echo esc_html(
                                            sprintf(
                                                /* translators: %s: Active WordPress locale code */
                                                __('Site Default (WordPress Locale: %s)', 'the-readscope-tour-and-accommodation-booking'),
                                                get_locale()
                                            )
                                        );
                                        ?></option>
                                        <option value="en" <?php selected($pluginLanguage, 'en'); ?>>English (US)</option>
                                        <option value="bn" <?php selected($pluginLanguage, 'bn'); ?>>বাংলা (Bengali - Bangladesh & Global)</option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Choose whether Readscope Booking displays in English or Bengali across the admin panel, booking forms, vouchers, and notifications.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><?php esc_html_e('Bengali Numbers / বাংলা সংখ্যা', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="use_bangla_digits">
                                        <input name="use_bangla_digits" type="checkbox" id="use_bangla_digits" value="1" <?php checked($useBanglaDigits, 1); ?>>
                                        <?php esc_html_e('Render prices and counters in Bengali digits (যেমন: ৳১,৫০০, ৩ জন)', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    </label>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><?php esc_html_e('Travel Homepage Layout', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="travel_homepage">
                                        <input name="travel_homepage" type="checkbox" id="travel_homepage" value="1" <?php checked($travelHomepage, 1); ?>>
                                        <?php esc_html_e('Turn the site homepage and footer into a travel storefront', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('Adds the search bar, destinations, open tours and hotels to the homepage and hides the latest-posts list there. In the footer it shows a short tagline when yours is empty, replaces the default theme credit with your site name, and fills empty theme placeholder menus with booking links. Menus and footer text you have set yourself are kept. Leave this off if your theme already has its own homepage.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Offline Payment Methods (Bank Transfer & Cash on Arrival)', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Configure structured bank details for direct wire transfer and Pay-on-Arrival terms for bookings without online payment gateways.', 'the-readscope-tour-and-accommodation-booking'); ?></p>

                        <!-- Bank Transfer Configuration -->
                        <h3 class="readscope-settings-subhead"><?php esc_html_e('Direct Bank Transfer (Wire / Deposit)', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Bank Transfer', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="bank_transfer_enabled">
                                        <input name="bank_transfer_enabled" type="checkbox" id="bank_transfer_enabled" value="1" <?php checked($bankEnabled); ?>>
                                        <strong><?php esc_html_e('Allow travelers to select Bank Transfer during reservation.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bank_name"><?php esc_html_e('Bank Name', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="bank_name" type="text" id="bank_name" value="<?php echo esc_attr($bankName); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. Standard Chartered / City Bank', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bank_account_name"><?php esc_html_e('Account Name / Beneficiary', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="bank_account_name" type="text" id="bank_account_name" value="<?php echo esc_attr($bankAccountName); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. Readscope Holidays Ltd.', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bank_account_number"><?php esc_html_e('Account Number', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="bank_account_number" type="text" id="bank_account_number" value="<?php echo esc_attr($bankAccountNumber); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. 01-2345678-01', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bank_iban"><?php esc_html_e('IBAN (International)', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="bank_iban" type="text" id="bank_iban" value="<?php echo esc_attr($bankIban); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. GB29NWBK60161331926819', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bank_swift_routing"><?php esc_html_e('SWIFT / BIC / Routing Code', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="bank_swift_routing" type="text" id="bank_swift_routing" value="<?php echo esc_attr($bankSwiftRouting); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. CIBLBDDH', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bank_branch"><?php esc_html_e('Branch Name & Address', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="bank_branch" type="text" id="bank_branch" value="<?php echo esc_attr($bankBranch); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. Principal Branch, Dhaka', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bank_instructions"><?php esc_html_e('Bank Transfer Instructions', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <textarea name="bank_instructions" id="bank_instructions" rows="3" class="large-text" placeholder="<?php esc_attr_e('e.g. Please include your Booking Reference # in your transfer remarks and submit transaction reference on Thank You page.', 'the-readscope-tour-and-accommodation-booking'); ?>"><?php echo esc_textarea($bankInstructions); ?></textarea>
                                </td>
                            </tr>
                        </table>

                        <!-- Cash on Arrival Configuration -->
                        <h3 class="readscope-settings-subhead"><?php esc_html_e('Cash on Arrival / Pay at Check-in', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Cash on Arrival', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="cash_on_arrival_enabled">
                                        <input name="cash_on_arrival_enabled" type="checkbox" id="cash_on_arrival_enabled" value="1" <?php checked($cashEnabled); ?>>
                                        <strong><?php esc_html_e('Allow travelers to pay in cash or card at the tour desk / hotel front reception.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cash_on_arrival_instructions"><?php esc_html_e('Arrival Payment Instructions', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <textarea name="cash_on_arrival_instructions" id="cash_on_arrival_instructions" rows="2" class="large-text" placeholder="<?php esc_attr_e('e.g. Payment is collected at the tour reception desk before boarding. Cash and all major debit/credit cards accepted.', 'the-readscope-tour-and-accommodation-booking'); ?>"><?php echo esc_textarea($cashInstructions); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Design & SEO Styling -->
                <div id="settings-tab-appearance" class="readscope-settings-tab-pane" style="display:none;">
                    <div class="readscope-settings-box">
                        <h2><?php esc_html_e('Brand Color Customizer & Styling', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Match Readscope Booking booking widgets, cards, and buttons with your active WordPress theme styling.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="primary_color"><?php esc_html_e('Primary Brand Color', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="primary_color" type="color" id="primary_color" value="<?php echo esc_attr($primaryColor); ?>" class="readscope-color-input">
                                    <code><?php echo esc_html($primaryColor); ?></code>
                                    <p class="description"><?php esc_html_e('Used for primary booking buttons, price tags, active calendar dates, and highlights.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="primary_hover"><?php esc_html_e('Primary Hover Color', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="primary_hover" type="color" id="primary_hover" value="<?php echo esc_attr($primaryHover); ?>" class="readscope-color-input">
                                    <code><?php echo esc_html($primaryHover); ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="accent_color"><?php esc_html_e('Accent / Badge Color', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="accent_color" type="color" id="accent_color" value="<?php echo esc_attr($accentColor); ?>" class="readscope-color-input">
                                    <code><?php echo esc_html($accentColor); ?></code>
                                    <p class="description"><?php esc_html_e('Used for promotional ribbons, star ratings, and discount tags.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="button_text_color"><?php esc_html_e('Button Text Color', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="button_text_color" type="color" id="button_text_color" value="<?php echo esc_attr($btnTextColor); ?>" class="readscope-color-input">
                                    <code><?php echo esc_html($btnTextColor); ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="border_radius"><?php esc_html_e('Widget Border Radius', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="border_radius" id="border_radius">
                                        <option value="4px" <?php selected($borderRadius, '4px'); ?>><?php esc_html_e('Sharp (4px)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="8px" <?php selected($borderRadius, '8px'); ?>><?php esc_html_e('Modern Rounded (8px - Default)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="12px" <?php selected($borderRadius, '12px'); ?>><?php esc_html_e('Smooth Curved (12px)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="16px" <?php selected($borderRadius, '16px'); ?>><?php esc_html_e('Pill Curved (16px)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="font_family"><?php esc_html_e('Font', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="font_family" id="font_family">
                                        <option value="anek" <?php selected($fontFamily, 'anek'); ?>><?php esc_html_e('Anek Bangla, headings in your theme\'s font (Default)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="anek_all" <?php selected($fontFamily, 'anek_all'); ?>><?php esc_html_e('Anek Bangla for text and headings', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="theme" <?php selected($fontFamily, 'theme'); ?>><?php esc_html_e('Your theme\'s fonts, Bengali in Anek Bangla', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Anek Bangla keeps forms, prices and buttons crisp on any theme and covers Bengali and English. Headings follow your theme so Readscope Booking pages still look like your site. Choose your theme\'s fonts only if its text font is a clear sans-serif with several weights.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Search Engine Optimization (SEO) & Social Sharing', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Schema.org JSON-LD', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="enable_schema">
                                        <input name="enable_schema" type="checkbox" id="enable_schema" value="1" <?php checked($enableSchema, 1); ?>>
                                        <strong><?php esc_html_e('Enable automatic Schema.org structured data on Tour and Hotel pages.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('Generates TouristTrip and Hotel JSON-LD metadata so Google can display rich search snippets, prices, and star ratings.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('OpenGraph & Twitter Cards', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="enable_opengraph">
                                        <input name="enable_opengraph" type="checkbox" id="enable_opengraph" value="1" <?php checked($enableOpenGraph, 1); ?>>
                                        <strong><?php esc_html_e('Enable rich social sharing preview cards on Facebook, WhatsApp, and Twitter / X.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Google Tag Manager / DataLayer', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="enable_datalayer">
                                        <input name="enable_datalayer" type="checkbox" id="enable_datalayer" value="1" <?php checked($enableDatalayer, 1); ?>>
                                        <strong><?php esc_html_e('Enable eCommerce conversion tracking dataLayer event on Thank You page.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('Pushes a privacy-friendly readscope_booking event (booking_id, value, currency) to window.dataLayer without customer PII.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: Email Notifications -->
                <div id="settings-tab-emails" class="readscope-settings-tab-pane" style="display: none;">
                    <div class="readscope-settings-box">
                        <h2><?php esc_html_e('Sender Details & Routing', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="email_from_name"><?php esc_html_e('Sender "From" Name', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="email_from_name" type="text" id="email_from_name" value="<?php echo esc_attr($fromName); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('The name appearing in traveler inboxes (e.g. Your Agency Name).', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="email_from_address"><?php esc_html_e('Sender "From" Email', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="email_from_address" type="email" id="email_from_address" value="<?php echo esc_attr($fromEmail); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('The outbound email address used in the From and Reply-To headers.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="email_notification_address"><?php esc_html_e('Admin Notification Recipient', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="email_notification_address" type="email" id="email_notification_address" value="<?php echo esc_attr($notifyEmail); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('Internal staff / operator email that receives new booking alerts, cancellation notices, and customer inquiries.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('General Email & Reminder Settings', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="reminder_days_before"><?php esc_html_e('Upcoming Trip Reminder (Days Before)', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="reminder_days_before" type="number" id="reminder_days_before" min="0" max="30" value="<?php echo esc_attr((string) $reminderDays); ?>" class="small-text">
                                    <?php esc_html_e('days before tour departure or hotel check-in date.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    <p class="description"><?php esc_html_e('Set to 0 to disable automated daily trip reminder emails. Readscope Booking runs a daily background cron check to dispatch reminders.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="lookup_page_id"><?php esc_html_e('Customer Booking Lookup Page', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <?php
                                    wp_dropdown_pages([
                                        'name'              => 'lookup_page_id',
                                        'id'                => 'lookup_page_id',
                                        'selected'          => absint($lookupPageId),
                                        'show_option_none'  => esc_html__('— Select Page with [readscope_booking_lookup] —', 'the-readscope-tour-and-accommodation-booking'),
                                        'option_none_value' => '0',
                                    ]);
                                    ?>
                                    <p class="description"><?php esc_html_e('Selected page is used to construct the {lookup_url} placeholder so travelers can look up and self-manage reservations.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="my_bookings_page_id"><?php esc_html_e('Customer Account / My Bookings Page', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <?php
                                    wp_dropdown_pages([
                                        'name'              => 'my_bookings_page_id',
                                        'id'                => 'my_bookings_page_id',
                                        'selected'          => absint($myBookingsPageId),
                                        'show_option_none'  => esc_html__('— Select Page with [readscope_my_bookings] —', 'the-readscope-tour-and-accommodation-booking'),
                                        'option_none_value' => '0',
                                    ]);
                                    ?>
                                    <p class="description"><?php esc_html_e('Selected page is used as the authenticated traveler account dashboard displaying upcoming trips, vouchers, and reservation management.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="offline_payment_instructions"><?php esc_html_e('Offline / Manual Payment Instructions', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <textarea name="offline_payment_instructions" id="offline_payment_instructions" rows="4" class="large-text" placeholder="<?php esc_attr_e("Bank Transfer Details:\nBank: City Bank\nAccount: 123456789\nOr bKash Merchant: 01700000000 (Use booking code as reference)", 'the-readscope-tour-and-accommodation-booking'); ?>"><?php echo esc_textarea($offlinePayment); ?></textarea>
                                    <p class="description"><?php esc_html_e('Included in Pending Booking emails via the {offline_payment_instructions} placeholder for manual bank wire, bKash, or pay-on-arrival instructions.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Available Template Placeholders', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Use these tags inside email subjects, headings, and additional content. They will be dynamically replaced with real booking data:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin: 12px 0 24px; font-size: var(--trv-admin-text-sm); line-height: 1.8;">
                            <code>{customer_name}</code>, <code>{customer_email}</code>, <code>{customer_phone}</code>, <code>{booking_code}</code>, <code>{item_title}</code>, <code>{item_type}</code>, <code>{check_in}</code>, <code>{check_out}</code>, <code>{guests}</code>, <code>{total_amount}</code>, <code>{subtotal}</code>, <code>{tax_amount}</code>, <code>{discount_amount}</code>, <code>{status}</code>, <code>{site_name}</code>, <code>{site_url}</code>, <code>{voucher_url}</code>, <code>{lookup_url}</code>, <code>{offline_payment_instructions}</code>
                            <br><small style="color: #64748b;"><?php esc_html_e('For inquiry notifications: {inquiry_name}, {inquiry_email}, {inquiry_phone}, {inquiry_message}, {item_title}, {site_name}.', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                        </div>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Email Templates Configuration', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Customize the subject lines, headings, and additional notes for all automated emails. You can also override email layouts in your active WordPress theme under your-theme/readscope/emails/.', 'the-readscope-tour-and-accommodation-booking'); ?></p>

                        <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 18px;">
                            <?php foreach ($emailTemplates as $tmplKey => $tmpl) : ?>
                                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 14px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <label style="font-size: var(--trv-admin-text-lg); font-weight: 600; color: #1e293b; cursor: pointer;">
                                                <input type="checkbox" name="email_<?php echo esc_attr($tmplKey); ?>_enabled" value="1" <?php checked($tmpl['enabled']); ?>>
                                                <?php echo esc_html($tmpl['title']); ?>
                                            </label>
                                            <span style="font-size: var(--trv-admin-text-xs); background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 9999px; font-weight: 600;">
                                                <?php echo esc_html($tmpl['recipient']); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <button type="button" class="button button-small readscope-send-single-test-email-btn" data-email-type="<?php echo esc_attr($tmplKey); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('readscope_test_email_nonce')); ?>">
                                                <?php esc_html_e('Test This Email', 'the-readscope-tour-and-accommodation-booking'); ?>
                                            </button>
                                        </div>
                                    </div>

                                    <p style="color: #64748b; font-size: var(--trv-admin-text-sm); margin: 0 0 14px;"><?php echo esc_html($tmpl['desc']); ?></p>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                                        <div>
                                            <label style="display: block; font-weight: 600; font-size: var(--trv-admin-text-xs); color: #475569; margin-bottom: 4px;" for="email_<?php echo esc_attr($tmplKey); ?>_subject">
                                                <?php esc_html_e('Email Subject Line', 'the-readscope-tour-and-accommodation-booking'); ?>
                                            </label>
                                            <input type="text" name="email_<?php echo esc_attr($tmplKey); ?>_subject" id="email_<?php echo esc_attr($tmplKey); ?>_subject" value="<?php echo esc_attr($tmpl['subject']); ?>" class="large-text">
                                        </div>
                                        <div>
                                            <label style="display: block; font-weight: 600; font-size: var(--trv-admin-text-xs); color: #475569; margin-bottom: 4px;" for="email_<?php echo esc_attr($tmplKey); ?>_heading">
                                                <?php esc_html_e('Email Main Heading', 'the-readscope-tour-and-accommodation-booking'); ?>
                                            </label>
                                            <input type="text" name="email_<?php echo esc_attr($tmplKey); ?>_heading" id="email_<?php echo esc_attr($tmplKey); ?>_heading" value="<?php echo esc_attr($tmpl['heading']); ?>" class="large-text">
                                        </div>
                                    </div>

                                    <div>
                                        <label style="display: block; font-weight: 600; font-size: var(--trv-admin-text-xs); color: #475569; margin-bottom: 4px;" for="email_<?php echo esc_attr($tmplKey); ?>_additional_content">
                                            <?php esc_html_e('Additional Content / Custom Notes', 'the-readscope-tour-and-accommodation-booking'); ?>
                                        </label>
                                        <textarea name="email_<?php echo esc_attr($tmplKey); ?>_additional_content" id="email_<?php echo esc_attr($tmplKey); ?>_additional_content" rows="3" class="large-text" placeholder="<?php esc_attr_e('Optional custom message, arrival guidelines, or policy instructions...', 'the-readscope-tour-and-accommodation-booking'); ?>"><?php echo esc_textarea($tmpl['content']); ?></textarea>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Test Email Dispatcher', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Send a sample email to verify your WordPress mail server setup and layout rendering.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        
                        <div class="readscope-inline-fields">
                            <select id="readscope_test_email_type">
                                <?php foreach ($emailTemplates as $tmplKey => $tmpl) : ?>
                                    <option value="<?php echo esc_attr($tmplKey); ?>"><?php echo esc_html($tmpl['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="email" id="readscope_test_email_recipient" value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" placeholder="<?php esc_attr_e('your-email@example.com', 'the-readscope-tour-and-accommodation-booking'); ?>">
                            <button type="button" class="button button-secondary" id="readscope-send-test-email-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('readscope_test_email_nonce')); ?>">
                                <?php esc_html_e('Send Test Email', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </button>
                        </div>
                        <div id="readscope-test-email-alert" style="display: none; margin-top: 12px; font-weight: 600;"></div>
                    </div>
                </div>

                <!-- TAB 4: Webhooks & API -->
                <div id="settings-tab-webhooks" class="readscope-settings-tab-pane" style="display: none;">
                    <div class="readscope-settings-box">
                        <h2><?php esc_html_e('Outbound Webhooks (Zapier, Make, n8n, CRMs)', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Send real-time JSON payloads to external webhooks whenever reservations are created or updated.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="webhook_url"><?php esc_html_e('Webhook Target URL', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="webhook_url" type="url" id="webhook_url" value="<?php echo esc_url($webhookUrl); ?>" class="large-text" placeholder="<?php esc_attr_e('https://hooks.zapier.com/hooks/catch/...', 'the-readscope-tour-and-accommodation-booking'); ?>" style="max-width: 600px;">
                                    <p class="description"><?php esc_html_e('Your external automation endpoint URL that receives POST requests.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="webhook_secret"><?php esc_html_e('Secret Key (HMAC SHA-256)', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <div class="readscope-inline-fields">
                                        <input name="webhook_secret" type="password" autocomplete="new-password" id="webhook_secret" value="<?php echo esc_attr($webhookSecret); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. secret_readscope_xxxx', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                        <button type="button" class="button" id="readscope-gen-secret-btn"><?php esc_html_e('Generate Secret', 'the-readscope-tour-and-accommodation-booking'); ?></button>
                                    </div>
                                    <p class="description"><?php esc_html_e('Optional secret key used to compute the X-Readscope-Signature HTTP header for payload verification.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><?php esc_html_e('Subscribed Events', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <fieldset>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input name="webhook_events[]" type="checkbox" value="booking.created" <?php checked(in_array('booking.created', $webhookEvents, true)); ?>>
                                            <strong><code>booking.created</code></strong> — <?php esc_html_e('Fired when a new tour or hotel booking is submitted.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                        </label>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input name="webhook_events[]" type="checkbox" value="booking.status_changed" <?php checked(in_array('booking.status_changed', $webhookEvents, true)); ?>>
                                            <strong><code>booking.status_changed</code></strong> — <?php esc_html_e('Fired when a booking status changes (confirmed, cancelled, completed).', 'the-readscope-tour-and-accommodation-booking'); ?>
                                        </label>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input name="webhook_events[]" type="checkbox" value="inquiry.created" <?php checked(in_array('inquiry.created', $webhookEvents, true)); ?>>
                                            <strong><code>inquiry.created</code></strong> — <?php esc_html_e('Fired when a customer inquiry is received.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><?php esc_html_e('Payload Data Minimization', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <fieldset>
                                        <label style="display: block; margin-bottom: 6px;">
                                            <input name="webhook_include_phone" type="checkbox" value="1" <?php checked($webhookIncludePhone, 1); ?>>
                                            <?php esc_html_e('Include customer phone numbers in webhook payloads.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                        </label>
                                        <label style="display: block;">
                                            <input name="webhook_include_message" type="checkbox" value="1" <?php checked($webhookIncludeMessage, 1); ?>>
                                            <?php esc_html_e('Include custom traveler inquiry messages in inquiry webhook payloads.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                        </label>
                                    </fieldset>
                                    <p class="description"><?php esc_html_e('Uncheck to strip personal communication details and phone numbers from external automated webhooks for privacy minimization.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Test Webhook Dispatcher', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Send a test ping payload to your webhook target URL to verify connectivity.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        
                        <div style="display: flex; gap: 10px; align-items: center; margin-top: 12px;">
                            <button type="button" class="button button-secondary" id="readscope-send-test-webhook-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('readscope_test_webhook_nonce')); ?>">
                                <?php esc_html_e('Send Test Ping Payload', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </button>
                        </div>
                        <div id="readscope-test-webhook-alert" style="display: none; margin-top: 12px; font-weight: 600;"></div>
                    </div>
                </div>

                <!-- TAB 5: Privacy & GDPR Compliance -->
                <div id="settings-tab-privacy" class="readscope-settings-tab-pane" style="display: none;">
                    <div class="readscope-settings-box">
                        <h2><?php esc_html_e('User Consent & Terms Agreement', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Require travelers to explicitly agree to your Terms and Privacy Policy before submitting bookings or trip inquiries.', 'the-readscope-tour-and-accommodation-booking'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Require Consent', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="require_consent">
                                        <input name="require_consent" type="checkbox" id="require_consent" value="1" <?php checked($requireConsent, 1); ?>>
                                        <strong><?php esc_html_e('Require checkbox agreement before checkout and inquiry submission.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    </label>
                                    <p class="description"><?php esc_html_e('When enabled, bookings and inquiries cannot proceed without agreeing to terms. Readscope Booking records the exact timestamp and policy version SHA-256 hash.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="privacy_policy_page_id"><?php esc_html_e('Privacy Policy Page', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <?php
                                    wp_dropdown_pages([
                                        'name'              => 'privacy_policy_page_id',
                                        'id'                => 'privacy_policy_page_id',
                                        'selected'          => absint($privacyPolicyPageId),
                                        'show_option_none'  => esc_html__('— Use WordPress Core Privacy Policy Page —', 'the-readscope-tour-and-accommodation-booking'),
                                        'option_none_value' => '0',
                                    ]);
                                    ?>
                                    <p class="description"><?php esc_html_e('By default, Readscope Booking links to the WordPress site privacy policy defined under Settings → Privacy.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="terms_page_id"><?php esc_html_e('Terms & Conditions Page', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <?php
                                    wp_dropdown_pages([
                                        'name'              => 'terms_page_id',
                                        'id'                => 'terms_page_id',
                                        'selected'          => absint($termsPageId),
                                        'show_option_none'  => esc_html__('— Select Terms and Conditions Page —', 'the-readscope-tour-and-accommodation-booking'),
                                        'option_none_value' => '0',
                                    ]);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="consent_label"><?php esc_html_e('Consent Checkbox Label', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="consent_label" type="text" id="consent_label" value="<?php echo esc_attr($consentLabel); ?>" class="large-text">
                                    <p class="description"><?php esc_html_e('Placeholders {privacy} and {terms} will be automatically transformed into clickable links.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('IP Storage & Data Minimization', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Store IP Addresses', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="store_ip">
                                        <input name="store_ip" type="checkbox" id="store_ip" value="1" <?php checked($storeIp, 1); ?>>
                                        <?php esc_html_e('Log client IP addresses with booking and inquiry records.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('If disabled, the IP address column will remain blank in the database. Rate limiting defenses continue securely using one-way transient hashes.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Scheduled Data Retention & Anonymization Routine', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Automate GDPR storage limitation compliance. Bookings are safely anonymized (retaining financial amounts and lines for tax laws), while inquiries are permanently deleted.', 'the-readscope-tour-and-accommodation-booking'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="anonymize_after_days"><?php esc_html_e('Anonymize Bookings After', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="anonymize_after_days" type="number" id="anonymize_after_days" min="0" max="3650" value="<?php echo esc_attr((string) $anonymizeAfterDays); ?>" class="small-text">
                                    <?php esc_html_e('days after the trip ends (last check-out, or check-in for tours). Set to 0 to disable automated anonymization.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    <p class="description"><?php esc_html_e('Replaces customer name, email, phone, billing address, notes, payment references, and IP with anonymized values, scrubs them from the booking log, revokes voucher links, and keeps revenue totals intact.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Anonymize Bookings With Status', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <fieldset>
                                        <?php
                                        $readscopeStatusLabels = [
                                            'completed' => __('Completed', 'the-readscope-tour-and-accommodation-booking'),
                                            'cancelled' => __('Cancelled', 'the-readscope-tour-and-accommodation-booking'),
                                            'confirmed' => __('Confirmed', 'the-readscope-tour-and-accommodation-booking'),
                                            'pending'   => __('Pending', 'the-readscope-tour-and-accommodation-booking'),
                                            'on_hold'   => __('On hold', 'the-readscope-tour-and-accommodation-booking'),
                                        ];
                                        foreach ($readscopeStatusLabels as $readscopeStatus => $readscopeStatusLabel) :
                                            ?>
                                            <label style="margin-right: 14px;"><input type="checkbox" name="retention_statuses[]" value="<?php echo esc_attr($readscopeStatus); ?>" <?php checked(in_array($readscopeStatus, $retentionStatuses, true)); ?>> <?php echo esc_html($readscopeStatusLabel); ?></label>
                                        <?php endforeach; ?>
                                    </fieldset>
                                    <p class="description"><?php esc_html_e('Only bookings in these statuses are anonymized. None ticked = Completed and Cancelled.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="delete_inquiries_after_months"><?php esc_html_e('Delete Old Inquiries After', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <input name="delete_inquiries_after_months" type="number" id="delete_inquiries_after_months" min="0" max="120" value="<?php echo esc_attr((string) $deleteInqAfterMonths); ?>" class="small-text">
                                    <?php esc_html_e('months (Set to 0 to retain inquiries indefinitely).', 'the-readscope-tour-and-accommodation-booking'); ?>
                                </td>
                            </tr>
                        </table>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-top: 15px; max-width: 650px;">
                            <h4 style="margin: 0 0 8px; color: #1e293b;"><?php esc_html_e('Retention Routine Dry-Run Preview', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                            <p style="margin: 0 0 12px; font-size: var(--trv-admin-text-sm); color: #64748b;"><?php esc_html_e('Check how many expired records are currently eligible for scheduled retention actions without modifying data.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            <button type="button" class="button button-secondary" id="readscope-retention-dry-run-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('readscope_admin_nonce')); ?>">
                                <?php esc_html_e('Check Eligible Records Count', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </button>
                            <div id="readscope-retention-dry-run-result" style="display: none; margin-top: 10px; font-size: var(--trv-admin-text-sm); font-weight: 600;"></div>
                        </div>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('WordPress Core Privacy Tools Integration', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <p class="description"><?php esc_html_e('Readscope Booking is fully integrated with standard WordPress privacy compliance features:', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        <ul style="list-style: disc; padding-left: 20px; line-height: 1.8; color: #475569;">
                            <li><strong><?php esc_html_e('Export Personal Data:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Exports traveler bookings and trip inquiries when requested via Tools → Export Personal Data.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                            <li><strong><?php esc_html_e('Erase Personal Data:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Erases inquiry submissions and wishlist user meta, and anonymizes booking financial records for tax/statutory compliance via Tools → Erase Personal Data.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                            <li><strong><?php esc_html_e('Privacy Policy Guide:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php esc_html_e('Suggested disclosures are automatically registered in the WordPress Privacy Policy Guide under Settings → Privacy.', 'the-readscope-tour-and-accommodation-booking'); ?></li>
                        </ul>
                    </div>
                </div>

                <!-- TAB 6: Advanced & System -->
                <div id="settings-tab-advanced" class="readscope-settings-tab-pane" style="display: none;">
                    <div class="readscope-settings-box">
                        <h2><?php esc_html_e('Network & Proxy Configuration', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="proxy_mode"><?php esc_html_e('Behind Cloudflare / Reverse Proxy', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <select name="proxy_mode" id="proxy_mode">
                                        <option value="disabled" <?php selected($proxyMode, 'disabled'); ?>><?php esc_html_e('Disabled (Standard direct connection / REMOTE_ADDR)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="cloudflare" <?php selected($proxyMode, 'cloudflare'); ?>><?php esc_html_e('Cloudflare (Trust CF-Connecting-IP only from Cloudflare IPs)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                        <option value="reverse_proxy" <?php selected($proxyMode, 'reverse_proxy'); ?>><?php esc_html_e('Reverse Proxy / Load Balancer (Trust X-Forwarded-For)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Enable this if your website is behind Cloudflare, AWS CloudFront, Nginx reverse proxy, or load balancers for accurate client IP detection.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="trusted_proxies"><?php esc_html_e('Custom Trusted Proxies', 'the-readscope-tour-and-accommodation-booking'); ?></label></th>
                                <td>
                                    <textarea name="trusted_proxies" id="trusted_proxies" rows="3" class="large-text code" placeholder="10.0.0.0/8, 192.168.1.100"><?php echo esc_textarea($trustedProxies); ?></textarea>
                                    <p class="description"><?php esc_html_e('Optional comma- or newline-separated list of trusted upstream proxy/load balancer IPs or CIDR subnets.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('Data Deletion on Uninstall', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Erase Data on Delete', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                                <td>
                                    <label for="erase_data_on_uninstall">
                                        <input name="erase_data_on_uninstall" type="checkbox" id="erase_data_on_uninstall" value="1" <?php checked($eraseData, 1); ?>>
                                        <?php esc_html_e('Check this box if you want to completely erase custom database tables and settings when the plugin is deleted.', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="readscope-settings-box">
                        <h2><?php esc_html_e('Setup Wizard & Demo Tools', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <div class="readscope-settings-tip">
                            <h4 style="margin: 0 0 6px; color: #0f766e;"><?php esc_html_e('Quick Onboarding & Sample Data', 'the-readscope-tour-and-accommodation-booking'); ?></h4>
                            <p style="margin: 0 0 12px; font-size: var(--trv-admin-text-sm); color: #115e59;"><?php esc_html_e('Need to regenerate core pages, re-configure currency, or re-import sample demo tours and boutique hotels?', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=readscope-setup-wizard')); ?>" class="button button-secondary">
                                <?php esc_html_e('Launch Setup Wizard', 'the-readscope-tour-and-accommodation-booking'); ?>
                            </a>
                        </div>

                    </div>
                    <div class="readscope-settings-box">

                        <h2><?php esc_html_e('System Information', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <table class="widefat striped readscope-settings-table">
                            <tbody>
                                <tr>
                                    <td><strong><?php esc_html_e('Readscope Booking Core Version', 'the-readscope-tour-and-accommodation-booking'); ?></strong></td>
                                    <td><code><?php echo esc_html(READSCOPE_VERSION); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e('Database Schema Version', 'the-readscope-tour-and-accommodation-booking'); ?></strong></td>
                                    <td><code><?php echo esc_html((string)get_option('readscope_db_version', '1.1.0')); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e('PHP Version', 'the-readscope-tour-and-accommodation-booking'); ?></strong></td>
                                    <td><code><?php echo esc_html(PHP_VERSION); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong><?php esc_html_e('WordPress Version', 'the-readscope-tour-and-accommodation-booking'); ?></strong></td>
                                    <td><code><?php echo esc_html(get_bloginfo('version')); ?></code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="readscope-savebar">
                    <input type="submit" name="readscope_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'the-readscope-tour-and-accommodation-booking'); ?>">
                </div>
            </form>
        </div>
        <?php
    }
}
