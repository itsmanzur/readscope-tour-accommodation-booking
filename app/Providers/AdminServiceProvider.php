<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Admin\AdminDashboard;
use Readscope\Admin\BookingsTable;
use Readscope\Admin\DocumentationPage;
use Readscope\Admin\InquiriesTable;
use Readscope\Admin\SettingsPage;
use Readscope\Admin\SetupWizard;
use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Common\Container;
use Readscope\Database\Seeder;
use Readscope\PostTypes\HotelPostType;
use Readscope\PostTypes\TourPostType;
use Readscope\Services\BookingService;
use Readscope\Services\EmailService;
use Readscope\Services\ExportService;
use Readscope\Services\InquiryService;
use Readscope\Services\InventoryService;
use Readscope\Services\LogService;
use Readscope\Services\PaymentService;
use Readscope\Admin\ReportsPage;
use Readscope\Services\ReportingService;
use Readscope\Shortcodes\BookingLookupShortcode;
use Readscope\Shortcodes\ThankYouShortcode;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AdminServiceProvider
 *
 * Registers admin menus, admin scripts, and dashboard UI hooks.
 *
 * @package Readscope\Providers
 */
class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(ExportService::class, static fn (): ExportService => new ExportService());
        $this->container->singleton(ReportingService::class, static fn (): ReportingService => new ReportingService());
        $this->container->singleton(PaymentService::class, static fn (): PaymentService => new PaymentService());
    }

    public function boot(): void
    {
        // Public & Admin AJAX for Inquiry submission
        add_action('wp_ajax_readscope_submit_inquiry', [$this, 'handleSubmitInquiry']);
        add_action('wp_ajax_nopriv_readscope_submit_inquiry', [$this, 'handleSubmitInquiry']);

        // Public & Admin AJAX for Customer Transaction Reference Submission
        add_action('wp_ajax_readscope_submit_payment_reference', [$this, 'handleSubmitPaymentReference']);
        add_action('wp_ajax_nopriv_readscope_submit_payment_reference', [$this, 'handleSubmitPaymentReference']);

        // Printable Voucher & Calendar .ics handlers (Admin & Public with token)
        add_action('admin_post_readscope_print_voucher', [$this, 'handlePrintVoucher']);
        add_action('admin_post_nopriv_readscope_print_voucher', [$this, 'handlePrintVoucher']);
        add_action('admin_post_readscope_verify_email', [$this, 'handleVerifyEmail']);
        add_action('admin_post_nopriv_readscope_verify_email', [$this, 'handleVerifyEmail']);
        add_action('admin_post_readscope_download_ics', [$this, 'handleDownloadIcs']);
        add_action('admin_post_nopriv_readscope_download_ics', [$this, 'handleDownloadIcs']);

        if (!is_admin()) {
            return;
        }

        $this->addAction('admin_menu', [$this, 'loadAdminLocale'], 1);
        $this->addAction('admin_menu', [$this, 'registerAdminMenus'], 9);
        $this->addAction('admin_menu', [$this, 'loadAdminLocale'], 29);
        $this->addAction('admin_enqueue_scripts', [$this, 'maybeSwitchUiLocale'], 1);
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_filter('admin_body_class', [$this, 'addAdminBodyClass']);
        $this->addAction('admin_post_readscope_export_bookings_csv', [ExportService::class, 'handleExportRequest']);
        $this->addAction('admin_post_readscope_export_accounting_csv', [$this, 'handleExportAccountingCsv']);
        $this->addAction('admin_post_readscope_save_settings', [SettingsPage::class, 'handleSave']);
        $this->addAction('admin_post_readscope_fix_proxy', [$this, 'handleFixProxy']);

        // Admin Notices
        $this->addAction('admin_notices', [$this, 'renderWelcomeDemoNotice']);
        $this->addAction('admin_notices', [$this, 'renderProxyModeNotice']);

        // AJAX handlers
        $this->addAction('wp_ajax_readscope_import_sample_data', [$this, 'handleSampleDataImport']);
        $this->addAction('wp_ajax_readscope_dismiss_welcome_notice', [$this, 'handleDismissWelcomeNotice']);
        $this->addAction('wp_ajax_readscope_update_booking_status', [$this, 'handleUpdateBookingStatus']);
        $this->addAction('wp_ajax_readscope_revoke_booking_links', [$this, 'handleRevokeBookingLinks']);
        $this->addAction('wp_ajax_readscope_create_manual_booking', [$this, 'handleCreateManualBooking']);
        $this->addAction('wp_ajax_readscope_update_inquiry_status', [$this, 'handleUpdateInquiryStatus']);
        $this->addAction('wp_ajax_readscope_delete_inquiry', [$this, 'handleDeleteInquiry']);
        $this->addAction('wp_ajax_readscope_send_test_email', [$this, 'handleSendTestEmail']);
        $this->addAction('wp_ajax_readscope_send_test_webhook', [$this, 'handleSendTestWebhook']);
        $this->addAction('wp_ajax_readscope_get_booking_timeline', [$this, 'handleGetBookingTimeline']);
        $this->addAction('wp_ajax_readscope_add_booking_note', [$this, 'handleAddBookingNote']);
        $this->addAction('wp_ajax_readscope_get_admin_calendar', [$this, 'handleGetAdminCalendar']);
        $this->addAction('wp_ajax_readscope_update_availability', [$this, 'handleUpdateAvailability']);
        $this->addAction('wp_ajax_readscope_retention_dry_run', [$this, 'handleRetentionDryRun']);
        $this->addAction('wp_ajax_readscope_get_export_preview', [$this, 'handleExportPreview']);
        $this->addAction('wp_ajax_readscope_record_payment', [$this, 'handleRecordPayment']);

        // Setup Wizard AJAX Handlers
        $this->addAction('wp_ajax_readscope_wizard_save_step1', [SetupWizard::class, 'handleSaveStep1']);
        $this->addAction('wp_ajax_readscope_wizard_create_pages', [SetupWizard::class, 'handleCreatePages']);
        $this->addAction('wp_ajax_readscope_wizard_import_demo', [SetupWizard::class, 'handleImportDemo']);
    }

    /**
     * Switch WordPress locale on Readscope Booking admin pages if plugin_language is customized.
     *
     * @return void
     */
    public function maybeSwitchUiLocale(): void
    {
        $lang = (string) \Readscope\Config\Config::get('plugin_language', 'default');
        if ('default' === $lang) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        $isReadscope = str_contains((string) $screen->id, 'readscope')
            || in_array((string) $screen->post_type, ['readscope_tour', 'readscope_hotel', 'readscope_room'], true);

        if (!$isReadscope) {
            return;
        }

        $locale = ('bn' === $lang) ? 'bn_BD' : 'en_US';
        readscope_load_plugin_textdomain($locale);
    }

    /**
     * Load the plugin language before admin menus are registered.
     *
     * Menu titles are translated at registration time. The screen-specific
     * switch runs later, so without this the sidebar stays in the site locale.
     */
    public function loadAdminLocale(): void
    {
        $lang = (string) \Readscope\Config\Config::get('plugin_language', 'default');
        if ('default' === $lang) {
            return;
        }

        readscope_load_plugin_textdomain('bn' === $lang ? 'bn_BD' : 'en_US');
    }

    /**
     * Register top-level Readscope Booking admin menu and submenus.
     *
     * @return void
     */
    public function registerAdminMenus(): void
    {
        $this->loadAdminLocale();
        add_menu_page(
            __('The Readscope Tour and Accommodation Booking', 'the-readscope-tour-and-accommodation-booking'),
            esc_html__('Readscope', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope',
            'readscope',
            [$this, 'renderDashboardPage'],
            'dashicons-palmtree',
            85.5
        );

        add_submenu_page(
            'readscope',
            __('Dashboard', 'the-readscope-tour-and-accommodation-booking'),
            __('Dashboard', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope',
            'readscope',
            [$this, 'renderDashboardPage']
        );

        add_submenu_page(
            'readscope',
            __('Bookings', 'the-readscope-tour-and-accommodation-booking'),
            __('Bookings', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope_bookings',
            'readscope-bookings',
            [$this, 'renderBookingsPage']
        );

        add_submenu_page(
            'readscope',
            __('Inquiries', 'the-readscope-tour-and-accommodation-booking'),
            __('Inquiries', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope_bookings',
            'readscope-inquiries',
            [$this, 'renderInquiriesPage']
        );

        add_submenu_page(
            'readscope',
            __('Reports & Analytics', 'the-readscope-tour-and-accommodation-booking'),
            __('Reports', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope_bookings',
            'readscope-reports',
            [ReportsPage::class, 'render']
        );

        add_submenu_page(
            'readscope',
            __('Docs & Features', 'the-readscope-tour-and-accommodation-booking'),
            __('Docs & Features', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope',
            'readscope-docs',
            [$this, 'renderDocumentationPage']
        );

        add_submenu_page(
            'readscope',
            __('Setup Wizard', 'the-readscope-tour-and-accommodation-booking'),
            __('Setup Wizard', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope_settings',
            'readscope-setup-wizard',
            [SetupWizard::class, 'render']
        );

        add_submenu_page(
            'readscope',
            __('Settings', 'the-readscope-tour-and-accommodation-booking'),
            __('Settings', 'the-readscope-tour-and-accommodation-booking'),
            'manage_readscope_settings',
            'readscope-settings',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Enqueue admin styles and scripts.
     *
     * @param string $hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash((string) $_GET['page'])) : '';
        if ('readscope-setup-wizard' === $page) {
            SetupWizard::enqueueAssets();
        }

        $isReadscopeScreen = str_contains($hook, 'readscope') || in_array($hook, ['index.php', 'plugins.php', 'edit.php'], true);
        if (!$isReadscopeScreen) {
            return;
        }

        $cssVer = file_exists(READSCOPE_PLUGIN_DIR . 'assets/css/admin.css') ? (string) filemtime(READSCOPE_PLUGIN_DIR . 'assets/css/admin.css') : READSCOPE_VERSION;
        $jsVer  = file_exists(READSCOPE_PLUGIN_DIR . 'assets/js/admin.js') ? (string) filemtime(READSCOPE_PLUGIN_DIR . 'assets/js/admin.js') : READSCOPE_VERSION;

        wp_enqueue_style(
            'readscope-admin',
            READSCOPE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $cssVer
        );

        \Readscope\Support\Typography::enqueue();

        wp_enqueue_script(
            'readscope-admin-js',
            READSCOPE_PLUGIN_URL . 'assets/js/admin.js',
            [],
            $jsVer,
            true
        );

        wp_localize_script('readscope-admin-js', 'readscopeAdminConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('readscope_admin_nonce'),
        ]);
    }

    /**
     * Mark every Readscope Booking (and Readscope Booking Pro) admin page, so the shared admin styles can scope to it.
     *
     * Screen ids follow the translated menu title, so the page slug is the reliable signal.
     */
    public function addAdminBodyClass(string $classes): string
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash((string) $_GET['page'])) : '';

        return str_starts_with($page, 'readscope') ? $classes . ' readscope-admin-screen' : $classes;
    }

    public function renderDashboardPage(): void
    {
        AdminDashboard::render();
    }

    public function renderBookingsPage(): void
    {
        BookingsTable::render();
    }

    public function renderInquiriesPage(): void
    {
        InquiriesTable::render();
    }

    public function renderDocumentationPage(): void
    {
        DocumentationPage::render();
    }

    public function renderSettingsPage(): void
    {
        SettingsPage::render();
    }

    /**
     * Render beginner-friendly welcome notice to import starter demo content if site is empty.
     */
    public function renderWelcomeDemoNotice(): void
    {
        if (!current_user_can('manage_readscope')) {
            return;
        }

        // Check if user dismissed notice
        $dismissed = get_user_meta(get_current_user_id(), 'readscope_dismiss_welcome_notice', true);
        if ($dismissed) {
            return;
        }

        // Check if there are already tours or hotels published
        $toursCount = (int) (wp_count_posts(TourPostType::POST_TYPE)->publish ?? 0);
        $hotelsCount = (int) (wp_count_posts(HotelPostType::POST_TYPE)->publish ?? 0);

        if ($toursCount > 0 || $hotelsCount > 0) {
            return;
        }

        $nonce = wp_create_nonce('readscope_admin_nonce');
        ?>
        <div class="notice notice-info is-dismissible readscope-welcome-notice" data-nonce="<?php echo esc_attr($nonce); ?>" style="border-left-color: #0284c7; background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%); border-radius: 8px; padding: 16px 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin: 20px 20px 15px 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="background: #0284c7; color: #fff; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: var(--trv-admin-text-3xl); flex-shrink: 0; box-shadow: 0 2px 4px rgba(2,132,199,0.3);">
                        🌴
                    </div>
                    <div>
                        <h3 style="margin: 0 0 4px 0; font-size: var(--trv-admin-text-lg); font-weight: 700; color: #0f172a;">
                            <?php esc_html_e('Welcome to Readscope Booking! Want to explore with ready-made demo content?', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </h3>
                        <p style="margin: 0; color: #475569; font-size: var(--trv-admin-text-base);">
                            <?php esc_html_e('Get started in seconds! Import 3 complete tours (Bali, Swiss Alps, Dubai) and 2 boutique hotels with 4 rooms to test booking forms, filters, and calendars immediately.', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="button button-primary readscope-import-sample-btn" data-nonce="<?php echo esc_attr($nonce); ?>" style="background: #0284c7; border-color: #0284c7; padding: 6px 16px; height: auto; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(2,132,199,0.25);">
                        <span class="dashicons dashicons-cloud-upload"></span> <?php esc_html_e('⚡ Import Starter Demo Content', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=readscope-docs')); ?>" class="button button-secondary" style="height: auto; padding: 6px 14px;">
                        <?php esc_html_e('Docs & Features Hub', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </a>
                </div>
            </div>
            <div class="readscope-seeder-notice" style="display:none; margin-top:12px;"></div>
        </div>
        <?php
    }

    /**
     * Warn when the site evidently sits behind a CDN / reverse proxy but client-IP detection is off.
     *
     * With proxy_mode disabled every visitor shares the proxy's IP, so IP-based rate limits would
     * throttle all customers together after a handful of bookings.
     */
    public function renderProxyModeNotice(): void
    {
        if (!current_user_can('manage_readscope_settings')) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !str_contains((string) $screen->id, 'readscope')) {
            return;
        }

        $mode = (string) \Readscope\Config\Config::get('proxy_mode', 'disabled');
        if (in_array($mode, ['cloudflare', 'reverse_proxy'], true)) {
            return;
        }

        $behindCloudflare = !empty($_SERVER['HTTP_CF_CONNECTING_IP']);
        $behindProxy      = !empty($_SERVER['HTTP_X_FORWARDED_FOR']);
        if (!$behindCloudflare && !$behindProxy) {
            return;
        }

        if ($this->isLocalDevHost()) {
            return;
        }

        ?>
        <div class="notice notice-info readscope-proxy-notice">
            <p>
                <span class="readscope-proxy-notice__text">
                    <?php esc_html_e('Your site sits behind another server. Without a quick fix, real customers can get blocked after only a few bookings.', 'the-readscope-tour-and-accommodation-booking'); ?>
                </span>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="readscope_fix_proxy">
                    <?php wp_nonce_field('readscope_fix_proxy'); ?>
                    <button type="submit" class="button button-primary button-small"><?php esc_html_e('Fix this for me', 'the-readscope-tour-and-accommodation-booking'); ?></button>
                </form>
            </p>
        </div>
        <?php
    }

    /**
     * Local WP and similar dev hosts always look like a proxy. Don't ask the site owner to configure that.
     */
    private function isLocalDevHost(): bool
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }
        $host = strtolower($host);

        return $host === 'localhost'
            || $host === '127.0.0.1'
            || $host === '[::1]'
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test');
    }

    /**
     * Turn on the matching visitor-address mode without sending the owner into advanced settings.
     */
    public function handleFixProxy(): void
    {
        if (!current_user_can('manage_readscope_settings')) {
            wp_die(esc_html__('Unauthorized access.', 'the-readscope-tour-and-accommodation-booking'));
        }
        check_admin_referer('readscope_fix_proxy');

        $mode = !empty($_SERVER['HTTP_CF_CONNECTING_IP']) ? 'cloudflare' : 'reverse_proxy';
        $settings = get_option('readscope_settings', []);
        if (!is_array($settings)) {
            $settings = [];
        }
        $settings['proxy_mode'] = $mode;
        $settings['trust_proxy_headers'] = 1;
        update_option('readscope_settings', $settings);

        wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=readscope'));
        exit;
    }

    /**
     * Public handler for the "confirm your email" link sent after a booking.
     */
    public function handleVerifyEmail(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Capability URL from the verification email, authenticated by its HMAC token (BookingService::verifyEmail); a nonce cannot work in an emailed link.
        $code  = isset($_GET['code']) ? strtoupper(sanitize_text_field(wp_unslash((string) $_GET['code']))) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Capability URL from the verification email, authenticated by its HMAC token (BookingService::verifyEmail); a nonce cannot work in an emailed link.
        $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash((string) $_GET['token'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Capability URL from the verification email, authenticated by its HMAC token (BookingService::verifyEmail); a nonce cannot work in an emailed link.
        $expiresAt = isset($_GET['exp']) ? (int) $_GET['exp'] : 0;

        if (!\Readscope\Support\RateLimiter::hit('readscope_verify_' . md5(\Readscope\Support\ClientIp::get()), 20, 600)) {
            wp_die(esc_html__('Too many attempts. Please try again later.', 'the-readscope-tour-and-accommodation-booking'), '', ['response' => 429]);
        }

        $bookingService = Container::getInstance()->get(BookingService::class);
        $result = $bookingService->verifyEmail($code, $token, $expiresAt);

        if (empty($result['success'])) {
            wp_die(
                esc_html($result['message']),
                esc_html__('Email confirmation', 'the-readscope-tour-and-accommodation-booking'),
                ['response' => 400, 'back_link' => true]
            );
        }

        $thankyouPageId = (int) \Readscope\Config\Config::get('thankyou_page_id', 0);
        $base = $thankyouPageId > 0 ? (string) get_permalink($thankyouPageId) : home_url('/');

        wp_safe_redirect(add_query_arg([
            'code'     => $code,
            'token'    => ThankYouShortcode::generateThankYouToken((int) ($result['booking_id'] ?? 0)),
            'verified' => 1,
        ], $base));
        exit;
    }

    /**
     * AJAX handler to permanently dismiss welcome banner.
     */
    public function handleDismissWelcomeNotice(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        if (!current_user_can('manage_readscope')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        update_user_meta(get_current_user_id(), 'readscope_dismiss_welcome_notice', 1);

        wp_send_json_success();
    }

    /**
     * AJAX handler for 1-click sample data import.
     */
    public function handleSampleDataImport(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        if (!current_user_can('manage_readscope')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $res = Seeder::run();

        if (empty($res['success'])) {
            wp_send_json_error([
                'message' => $res['message'] ?? __('Failed to import sample demo data. Please check server logs.', 'the-readscope-tour-and-accommodation-booking'),
            ], 500);
        }

        if (!empty($res['skipped'])) {
            wp_send_json_success([
                'message' => $res['message'] ?? __('Demo content is already imported.', 'the-readscope-tour-and-accommodation-booking'),
                'skipped' => true,
            ]);
        }

        // Mark welcome notice dismissed only on genuine new demo data import
        update_user_meta(get_current_user_id(), 'readscope_dismiss_welcome_notice', 1);

        wp_send_json_success([
            'message' => sprintf(
                /* translators: 1: Tours count, 2: Hotels count, 3: Rooms count */
                __('Sample data successfully imported! Created %1$d Tours, %2$d Hotels, and %3$d Rooms.', 'the-readscope-tour-and-accommodation-booking'),
                $res['tours_created'],
                $res['hotels_created'],
                $res['rooms_created']
            ),
            'tours_created'  => $res['tours_created'],
            'hotels_created' => $res['hotels_created'],
            'rooms_created'  => $res['rooms_created'],
        ]);
    }

    /**
     * AJAX handler for inline booking status update.
     */
    public function handleUpdateBookingStatus(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $bookingId = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $status    = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';

        if ($bookingId <= 0 || !in_array($status, ['pending', 'confirmed', 'completed', 'cancelled', 'on_hold'], true)) {
            wp_send_json_error(['message' => __('Invalid booking ID or status.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        $bookingService = Container::getInstance()->get(\Readscope\Services\BookingService::class);
        $result = $bookingService->changeStatus($bookingId, $status, ['source' => 'admin_ajax']);

        if (!$result['success']) {
            wp_send_json_error(['message' => $result['message']], 400);
        }

        wp_send_json_success(['message' => $result['message']]);
    }

    /**
     * AJAX handler: invalidate every previously issued voucher / calendar link of a booking.
     */
    public function handleRevokeBookingLinks(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
            return;
        }

        $bookingId = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $bookingService = Container::getInstance()->get(BookingService::class);

        if ($bookingId <= 0 || !$bookingService->rotateAccessKey($bookingId)) {
            wp_send_json_error(['message' => __('Could not revoke the links for this booking.', 'the-readscope-tour-and-accommodation-booking')], 400);
            return;
        }

        wp_send_json_success(['message' => __('Previously shared voucher links no longer work. New links are generated automatically.', 'the-readscope-tour-and-accommodation-booking')]);
    }

    /**
     * AJAX handler for creating manual booking.
     */
    public function handleCreateManualBooking(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $itemId        = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        $itemType      = isset($_POST['item_type']) ? sanitize_text_field(wp_unslash($_POST['item_type'])) : 'tour';
        $checkIn       = isset($_POST['check_in']) ? sanitize_text_field(wp_unslash($_POST['check_in'])) : '';
        $checkOut      = isset($_POST['check_out']) ? sanitize_text_field(wp_unslash($_POST['check_out'])) : null;
        $adults        = isset($_POST['adults']) ? max(1, (int) $_POST['adults']) : 1;
        $children      = isset($_POST['children']) ? max(0, (int) $_POST['children']) : 0;
        $rooms         = isset($_POST['rooms']) ? max(1, (int) $_POST['rooms']) : 1;
        $totalAmount   = isset($_POST['total_amount']) ? (float) $_POST['total_amount'] : 0.0;
        $customerName  = isset($_POST['customer_name']) ? sanitize_text_field(wp_unslash($_POST['customer_name'])) : '';
        $customerEmail = isset($_POST['customer_email']) ? sanitize_email(wp_unslash($_POST['customer_email'])) : '';
        $customerPhone = isset($_POST['customer_phone']) ? sanitize_text_field(wp_unslash($_POST['customer_phone'])) : '';
        $paymentStatus = isset($_POST['payment_status']) ? sanitize_text_field(wp_unslash($_POST['payment_status'])) : 'paid';
        $customerNotes = isset($_POST['customer_notes']) ? sanitize_textarea_field(wp_unslash($_POST['customer_notes'])) : '';
        $sendCustomerEmail = isset($_POST['send_customer_email']) && ($_POST['send_customer_email'] === '1' || $_POST['send_customer_email'] === 'true' || $_POST['send_customer_email'] === true);

        if ($itemId <= 0 || empty($checkIn) || empty($customerName) || empty($customerEmail)) {
            wp_send_json_error(['message' => __('Please fill in all required fields.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        $customerId = 0;
        $foundUser = get_user_by('email', $customerEmail);
        if ($foundUser) {
            $customerId = (int) $foundUser->ID;
        }

        $bookingService = Container::getInstance()->get(BookingService::class);
        $result = $bookingService->createBooking([
            'item_id'             => $itemId,
            'item_type'           => $itemType,
            'check_in'            => $checkIn,
            'check_out'           => $checkOut,
            'adults'              => $adults,
            'children'            => $children,
            'rooms'               => $rooms,
            'total_amount'        => $totalAmount,
            'customer_id'         => $customerId,
            'customer_name'       => $customerName,
            'customer_email'      => $customerEmail,
            'customer_phone'      => $customerPhone,
            'customer_notes'      => $customerNotes,
            'payment_method'      => 'manual_admin',
            'payment_status'      => $paymentStatus,
            'booking_status'      => 'confirmed',
            'send_customer_email' => $sendCustomerEmail,
        ], true);

        if (!empty($result['success'])) {
            wp_send_json_success([
                /* translators: %s: Booking Reference Code */
                'message'      => sprintf(__('Manual booking created successfully! Reference Code: #%s', 'the-readscope-tour-and-accommodation-booking'), $result['booking_code']),
                'booking_code' => $result['booking_code'],
            ]);
        } else {
            wp_send_json_error(['message' => $result['message'] ?? __('Failed to create manual booking.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }
    }

    /**
     * AJAX handler for customer submitting inquiry from frontend modal.
     */
    public function handleSubmitInquiry(): void
    {
        check_ajax_referer('readscope_frontend_nonce', 'nonce');

        // Only the inquiry form's own fields are read, each one sanitized for its type. The nonce was checked above.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every key is sanitized by RequestInput::pick().
        $data = \Readscope\Support\RequestInput::pick($_POST, [
            'item_id'        => 'int',
            'item_type'      => 'key',
            'customer_name'  => 'text',
            'customer_email' => 'email',
            'customer_phone' => 'text',
            'travel_date'    => 'text',
            'guests'         => 'int',
            'message'        => 'textarea',
            'consent'        => 'bool',
            'readscope_inq_hp' => 'text',
        ]);

        $inquiryService = Container::getInstance()->get(InquiryService::class);
        $res = $inquiryService->createInquiry($data);

        if ($res['success']) {
            wp_send_json_success($res);
        } else {
            wp_send_json_error($res, 400);
        }
    }

    /**
     * AJAX handler for updating inquiry status.
     */
    public function handleUpdateInquiryStatus(): void
    {
        check_ajax_referer('readscope_inquiry_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $inquiryId = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $status    = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';

        $inquiryService = Container::getInstance()->get(InquiryService::class);
        if ($inquiryService->updateStatus($inquiryId, $status)) {
            wp_send_json_success(['message' => __('Inquiry status updated.', 'the-readscope-tour-and-accommodation-booking')]);
        } else {
            wp_send_json_error(['message' => __('Failed to update inquiry status.', 'the-readscope-tour-and-accommodation-booking')]);
        }
    }

    /**
     * AJAX handler for deleting an inquiry.
     */
    public function handleDeleteInquiry(): void
    {
        check_ajax_referer('readscope_inquiry_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $inquiryId = isset($_POST['id']) ? absint($_POST['id']) : 0;

        $inquiryService = Container::getInstance()->get(InquiryService::class);
        if ($inquiryService->deleteInquiry($inquiryId)) {
            wp_send_json_success(['message' => __('Inquiry deleted.', 'the-readscope-tour-and-accommodation-booking')]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete inquiry.', 'the-readscope-tour-and-accommodation-booking')]);
        }
    }

    /**
     * AJAX handler for sending test email verification.
     */
    public function handleSendTestEmail(): void
    {
        check_ajax_referer('readscope_test_email_nonce', 'nonce');

        if (!current_user_can('manage_readscope_settings')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $emailType = isset($_POST['email_type']) ? sanitize_key(wp_unslash($_POST['email_type'])) : 'customer_booking_confirmed';
        // The type selects a template file: accept only the real email types, anything else gets the default.
        if (!in_array($emailType, EmailService::EMAIL_TYPES, true)) {
            $emailType = 'customer_booking_confirmed';
        }

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => __('Please enter a valid email address.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        $emailService = new EmailService();
        $sent = $emailService->sendTestEmail($email, $emailType);

        if ($sent) {
            wp_send_json_success([
                /* translators: %s: Email address */
                'message' => sprintf(__('Test email successfully dispatched to %s!', 'the-readscope-tour-and-accommodation-booking'), $email),
            ]);
        } else {
            wp_send_json_error(['message' => __('Mail sending failed. Please verify your WordPress SMTP settings.', 'the-readscope-tour-and-accommodation-booking')], 500);
        }
    }

    /**
     * AJAX handler to dispatch a test webhook payload.
     */
    public function handleSendTestWebhook(): void
    {
        check_ajax_referer('readscope_test_webhook_nonce', 'nonce');

        if (!current_user_can('manage_readscope_settings')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $url    = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
        $secret = isset($_POST['secret']) ? sanitize_text_field(wp_unslash($_POST['secret'])) : '';

        $webhookService = Container::getInstance()->get(\Readscope\Services\WebhookService::class);
        $res = $webhookService->sendTestWebhook($url, $secret);

        if ($res['success']) {
            wp_send_json_success(['message' => $res['message']]);
        } else {
            wp_send_json_error(['message' => $res['message']]);
        }
    }

    /**
     * AJAX handler for getting booking timeline history.
     */
    public function handleGetBookingTimeline(): void
    {
        check_ajax_referer('readscope_timeline_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $bookingId = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        if ($bookingId <= 0) {
            wp_send_json_error(['message' => __('Invalid booking ID.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        $logs = LogService::getTimeline($bookingId);

        ob_start();
        if (!empty($logs)) : ?>
            <ul class="timeline-list" style="list-style: none; margin: 0; padding: 0;">
                <?php foreach ($logs as $log) : 
                    $user = $log['user_id'] ? get_userdata((int)$log['user_id']) : null;
                    $userName = $user ? $user->display_name : __('System / Guest', 'the-readscope-tour-and-accommodation-booking');
                    $isNote = $log['action'] === 'internal_note';
                ?>
                    <li class="timeline-item" style="padding: 8px 10px; margin-bottom: 8px; border-left: 3px solid <?php echo $isNote ? '#8b5cf6' : '#0284c7'; ?>; background: #f8fafc; border-radius: 0 6px 6px 0; font-size: var(--trv-admin-text-sm);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                            <strong><?php echo $isNote ? '📝 ' . esc_html__('Internal Staff Note', 'the-readscope-tour-and-accommodation-booking') : '📌 ' . esc_html(ucwords(str_replace('_', ' ', $log['action']))); ?></strong>
                            <small style="color: #64748b;"><?php echo esc_html(gmdate('M d, Y g:i A', strtotime($log['created_at']))); ?></small>
                        </div>
                        <div style="color: #334155; line-height: 1.4;"><?php echo nl2br(esc_html($log['details'])); ?></div>
                        <small style="color: #94a3b8; font-size: var(--trv-admin-text-xs);"><?php
                        echo esc_html(
                            sprintf(
                                /* translators: %s: Staff or user display name */
                                __('Logged by: %s', 'the-readscope-tour-and-accommodation-booking'),
                                $userName
                            )
                        );
                        ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <div style="text-align: center; color: #64748b; padding: 12px;"><?php esc_html_e('No activity logs recorded yet for this booking.', 'the-readscope-tour-and-accommodation-booking'); ?></div>
        <?php endif;

        $html = ob_get_clean() ?: '';
        wp_send_json_success(['html' => $html]);
    }

    /**
     * AJAX handler for adding internal note to a booking.
     */
    public function handleAddBookingNote(): void
    {
        check_ajax_referer('readscope_timeline_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $bookingId = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
        $note      = isset($_POST['note']) ? sanitize_textarea_field(wp_unslash($_POST['note'])) : '';

        if ($bookingId <= 0 || empty($note)) {
            wp_send_json_error(['message' => __('Note content cannot be empty.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        $logId = LogService::log($bookingId, 'internal_note', $note, get_current_user_id());

        if ($logId > 0) {
            wp_send_json_success(['message' => __('Internal note added successfully.', 'the-readscope-tour-and-accommodation-booking')]);
        } else {
            wp_send_json_error(['message' => __('Failed to record note.', 'the-readscope-tour-and-accommodation-booking')]);
        }
    }

    /**
     * Handle public and admin printable booking voucher rendering.
     *
     * Two ways in, checked in order:
     * 1. Customers (emailed or shared links, usually logged out): a capability URL authenticated by the
     *    HMAC token. A nonce cannot work here, it expires and is tied to a user session.
     * 2. Staff with manage_readscope_bookings: the link built in BookingsTable carries a nonce
     *    (_readscope_nonce, action readscope_voucher_{code}). A capability alone is not enough.
     * A missing or wrong token never falls through to the staff path unless the nonce is valid.
     *
     * @return void
     */
    public function handlePrintVoucher(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- capability URL authenticated by HMAC token; staff path verifies nonce below.
        $code  = isset($_GET['code']) ? strtoupper(sanitize_text_field(wp_unslash((string) $_GET['code']))) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- capability URL authenticated by HMAC token; staff path verifies nonce below.
        $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash((string) $_GET['token'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- value is passed to wp_verify_nonce() below.
        $nonce = isset($_GET['_readscope_nonce']) ? sanitize_text_field(wp_unslash((string) $_GET['_readscope_nonce'])) : '';

        if (empty($code)) {
            wp_die(esc_html__('Invalid or missing booking reference code.', 'the-readscope-tour-and-accommodation-booking'), 400);
        }

        global $wpdb;
        $bookingsTable = $wpdb->prefix . 'readscope_bookings';
        $itemsTable    = $wpdb->prefix . 'readscope_booking_items';

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $readscopeBooking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$bookingsTable} WHERE booking_code = %s LIMIT 1",
            $code
        ));

        if (!$readscopeBooking) {
            wp_die(esc_html__('Booking not found.', 'the-readscope-tour-and-accommodation-booking'), 404);
        }

        // Authorization: valid HMAC token, or staff capability plus a valid nonce. Explicit sequential checks.
        $expectedToken = BookingLookupShortcode::generateVoucherToken((int) $readscopeBooking->id, (string) $readscopeBooking->customer_email, $readscopeBooking->access_key ?? null);
        $authorized    = false;
        if ($token !== '' && hash_equals($expectedToken, $token)) {
            $authorized = true;
        } elseif (current_user_can('manage_readscope_bookings')) {
            if ($nonce !== '' && wp_verify_nonce($nonce, 'readscope_voucher_' . $code)) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            wp_die(esc_html__('Access denied. Invalid voucher security token.', 'the-readscope-tour-and-accommodation-booking'), 403);
        }

        $readscopeItems = (array) $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$itemsTable} WHERE booking_id = %d",
            $readscopeBooking->id
        ));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

        $readscopeCurrencySymbol = (string) apply_filters('readscope/currency_symbol', '$');
        $readscopeSiteName       = get_bloginfo('name');

        include untrailingslashit(READSCOPE_PLUGIN_DIR) . '/templates/booking-voucher.php';
        exit;
    }

    /**
     * Handle iCalendar (.ics) download for bookings.
     *
     * Customers use emailed or shared capability URLs authenticated by the HMAC token (thank-you or voucher
     * token); a nonce cannot work there, it expires and is tied to a user session. Staff with
     * manage_readscope_bookings must send a valid nonce (_readscope_nonce, action readscope_ics_{code}).
     *
     * @return void
     */
    public function handleDownloadIcs(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- capability URL authenticated by HMAC token; staff path verifies nonce below.
        $code  = isset($_GET['code']) ? strtoupper(sanitize_text_field(wp_unslash((string) $_GET['code']))) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- capability URL authenticated by HMAC token; staff path verifies nonce below.
        $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash((string) $_GET['token'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- value is passed to wp_verify_nonce() below.
        $nonce = isset($_GET['_readscope_nonce']) ? sanitize_text_field(wp_unslash((string) $_GET['_readscope_nonce'])) : '';

        if (empty($code)) {
            wp_die(esc_html__('Invalid or missing booking reference code.', 'the-readscope-tour-and-accommodation-booking'), 400);
        }

        global $wpdb;
        $bookingsTable = $wpdb->prefix . 'readscope_bookings';

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $readscopeBooking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$bookingsTable} WHERE booking_code = %s LIMIT 1",
            $code
        ));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

        if (!$readscopeBooking) {
            wp_die(esc_html__('Booking not found.', 'the-readscope-tour-and-accommodation-booking'), 404);
        }

        $bookingId            = (int) $readscopeBooking->id;
        $expectedVoucherToken = BookingLookupShortcode::generateVoucherToken($bookingId, (string) $readscopeBooking->customer_email, $readscopeBooking->access_key ?? null);
        $authorized           = false;
        if ($token !== '' && ThankYouShortcode::verifyThankYouToken($bookingId, $token)) {
            $authorized = true;
        } elseif ($token !== '' && hash_equals($expectedVoucherToken, $token)) {
            $authorized = true;
        } elseif (current_user_can('manage_readscope_bookings')) {
            if ($nonce !== '' && wp_verify_nonce($nonce, 'readscope_ics_' . $code)) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            wp_die(esc_html__('Access denied. Invalid calendar security token.', 'the-readscope-tour-and-accommodation-booking'), 403);
        }

        $ics = ThankYouShortcode::generateIcsContent($bookingId);
        if (empty($ics)) {
            wp_die(esc_html__('Could not generate calendar file.', 'the-readscope-tour-and-accommodation-booking'), 500);
        }

        $filename = 'booking-' . sanitize_file_name((string) $readscopeBooking->booking_code) . '.ics';

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        // iCalendar file download (text/calendar), not HTML; values are escaped while the feed is built.
        echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    /**
     * Stop the request with 403 unless $itemId really is a post of the claimed item type and the user may edit it.
     *
     * The type must match first, so an ID of another post type (or another plugin's post) can never be
     * passed off as a tour or room; then only the item's own edit_post capability counts.
     *
     * @param int    $itemId
     * @param string $itemType tour | room | hotel_room | hotel
     * @return bool False once the 403 response was sent.
     */
    private function authorizeInventoryItem(int $itemId, string $itemType): bool
    {
        $postTypes = [
            'tour'       => 'readscope_tour',
            'room'       => 'readscope_room',
            'hotel_room' => 'readscope_room',
            'hotel'      => 'readscope_hotel',
        ];

        if (!isset($postTypes[$itemType]) || get_post_type($itemId) !== $postTypes[$itemType] || !current_user_can('edit_post', $itemId)) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
            return false;
        }

        return true;
    }

    /**
     * AJAX handler to fetch admin monthly availability calendar grid.
     *
     * @return void
     */
    public function handleGetAdminCalendar(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        $itemId   = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        $itemType = isset($_POST['item_type']) ? sanitize_text_field(wp_unslash($_POST['item_type'])) : 'tour';
        $year     = isset($_POST['year']) ? absint($_POST['year']) : (int) wp_date('Y');
        $month    = isset($_POST['month']) ? absint($_POST['month']) : (int) wp_date('n');
        $timeSlot = isset($_POST['time_slot']) ? sanitize_text_field(wp_unslash($_POST['time_slot'])) : 'all_day';

        if ($itemId <= 0) {
            wp_send_json_error(['message' => __('Invalid item ID.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        if (!$this->authorizeInventoryItem($itemId, $itemType)) {
            return;
        }

        $invService = Container::getInstance()->get(InventoryService::class);
        $calendar   = $invService->getAdminCalendarAvailability($itemId, $itemType, $year, $month, $timeSlot);
        $defaultCap = $invService->getDefaultCapacity($itemId, $itemType);
        $unitPrice  = $invService->getUnitPrice($itemId, $itemType);

        wp_send_json_success([
            'year'             => $year,
            'month'            => $month,
            'days'             => $calendar,
            'default_capacity' => $defaultCap,
            'base_price'       => $unitPrice,
            'currency_symbol'  => (string) apply_filters('readscope/currency_symbol', '$'),
        ]);
    }

    /**
     * AJAX handler to update item availability, capacity, pricing and blocking status.
     *
     * @return void
     */
    public function handleUpdateAvailability(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        $itemId    = isset($_POST['item_id']) ? absint($_POST['item_id']) : 0;
        $itemType  = isset($_POST['item_type']) ? sanitize_text_field(wp_unslash($_POST['item_type'])) : 'tour';
        $startDate = isset($_POST['start_date']) ? sanitize_text_field(wp_unslash($_POST['start_date'])) : '';
        $endDate   = (isset($_POST['end_date']) && !empty($_POST['end_date'])) ? sanitize_text_field(wp_unslash($_POST['end_date'])) : null;
        $timeSlot  = isset($_POST['time_slot']) ? sanitize_text_field(wp_unslash($_POST['time_slot'])) : 'all_day';
        $force     = !empty($_POST['force']) && ($_POST['force'] === '1' || $_POST['force'] === 'true' || $_POST['force'] === true);

        if ($itemId <= 0 || empty($startDate)) {
            wp_send_json_error(['message' => __('Please provide a valid item ID and date.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        if (!$this->authorizeInventoryItem($itemId, $itemType)) {
            return;
        }

        $changes = [];

        if (isset($_POST['status']) && in_array($_POST['status'], ['available', 'blocked'], true)) {
            $changes['status'] = sanitize_text_field(wp_unslash($_POST['status']));
        }

        if (isset($_POST['capacity']) && $_POST['capacity'] !== '') {
            $changes['capacity'] = (int) sanitize_text_field(wp_unslash((string) $_POST['capacity']));
        }

        if (!empty($_POST['reset_price'])) {
            $changes['reset_price'] = true;
        } elseif (isset($_POST['price_override']) && $_POST['price_override'] !== '') {
            $changes['price_override'] = (float) sanitize_text_field(wp_unslash((string) $_POST['price_override']));
        }

        if (isset($_POST['days_of_week']) && is_array($_POST['days_of_week'])) {
            $changes['days_of_week'] = array_map('intval', wp_unslash($_POST['days_of_week']));
        }

        $invService = Container::getInstance()->get(InventoryService::class);
        $result = $invService->updateAvailability($itemId, $itemType, $startDate, $endDate, $changes, $force, $timeSlot);

        if (!empty($result['success'])) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(['message' => $result['message'] ?? __('Failed to update availability.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }
    }

    /**
     * AJAX handler to calculate retention dry-run counts.
     *
     * @return void
     */
    public function handleRetentionDryRun(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        if (!current_user_can('manage_readscope_settings')) {
            wp_send_json_error(['message' => __('Unauthorized permission.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $counts = \Readscope\Services\PrivacyService::getRetentionDryRunCounts();
        wp_send_json_success($counts);
    }

    /**
     * AJAX handler to calculate live export matching counts and totals.
     *
     * @return void
     */
    public function handleExportPreview(): void
    {
        $exportService = Container::getInstance()->has(ExportService::class)
            ? Container::getInstance()->get(ExportService::class)
            : new ExportService();

        $exportService->handlePreviewAjax();
    }

    /**
     * Handle HTTP POST request for Accounting & Tax CSV export.
     *
     * @return void
     */
    public function handleExportAccountingCsv(): void
    {
        $reportingService = Container::getInstance()->has(ReportingService::class)
            ? Container::getInstance()->get(ReportingService::class)
            : new ReportingService();

        $reportingService->handleExportRequest();
    }

    /**
     * AJAX handler to record received payment by admin.
     *
     * @return void
     */
    public function handleRecordPayment(): void
    {
        check_ajax_referer('readscope_admin_nonce', 'nonce');

        if (!current_user_can('manage_readscope_bookings')) {
            wp_send_json_error(['message' => __('Unauthorized permission to record payments.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $bookingId = absint($_POST['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            wp_send_json_error(['message' => __('Invalid booking ID.', 'the-readscope-tour-and-accommodation-booking')], 400);
        }

        /** @var PaymentService $paymentService */
        $paymentService = Container::getInstance()->get(PaymentService::class);
        $result = $paymentService->recordPayment($bookingId, [
            'amount'         => isset($_POST['amount']) ? (float) sanitize_text_field(wp_unslash($_POST['amount'])) : null,
            'payment_status' => sanitize_key(wp_unslash($_POST['payment_status'] ?? '')),
            'payment_method' => sanitize_text_field(wp_unslash($_POST['payment_method'] ?? '')),
            'transaction_id' => sanitize_text_field(wp_unslash($_POST['transaction_id'] ?? '')),
            'auto_confirm'   => !empty($_POST['auto_confirm']),
            'note'           => sanitize_textarea_field(wp_unslash($_POST['note'] ?? '')),
        ]);

        if (!empty($result['success'])) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(['message' => $result['message']], 400);
        }
    }

    /**
     * AJAX handler for customer submitting transaction/deposit reference.
     *
     * @return void
     */
    public function handleSubmitPaymentReference(): void
    {
        // Accepts frontend nonce OR admin nonce for CSRF
        $verified = check_ajax_referer('readscope_frontend_nonce', 'nonce', false)
            || check_ajax_referer('readscope_admin_nonce', 'nonce', false);

        if (!$verified) {
            wp_send_json_error(['message' => __('Security verification failed. Please refresh the page.', 'the-readscope-tour-and-accommodation-booking')], 403);
        }

        $bookingCode   = sanitize_text_field(wp_unslash($_POST['booking_code'] ?? ''));
        $transactionId = sanitize_text_field(wp_unslash($_POST['transaction_id'] ?? ''));
        $token         = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : null;
        $customerEmail = isset($_POST['customer_email']) ? sanitize_email(wp_unslash($_POST['customer_email'])) : null;
        $notes         = isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : null;

        /** @var PaymentService $paymentService */
        $paymentService = Container::getInstance()->get(PaymentService::class);
        $result = $paymentService->submitTransactionReference(
            $bookingCode,
            $transactionId,
            $token,
            $customerEmail,
            $notes
        );

        if (!empty($result['success'])) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(['message' => $result['message']], 400);
        }
    }
}

