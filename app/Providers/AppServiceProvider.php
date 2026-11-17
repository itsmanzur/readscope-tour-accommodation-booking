<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Config\Config;
use Readscope\Services\ReviewService;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AppServiceProvider
 *
 * Registers core application services, i18n textdomain loading, and core hooks.
 *
 * @package Readscope\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Core application bindings
        $this->container->singleton(\Readscope\Services\WebhookService::class, fn () => new \Readscope\Services\WebhookService());
        $this->container->singleton(\Readscope\Services\EmailService::class, fn () => new \Readscope\Services\EmailService());
    }

    public function boot(): void
    {
        // Initialize Review Service
        new ReviewService();

        // Initialize Webhook Dispatcher
        $webhookService = $this->container->get(\Readscope\Services\WebhookService::class);
        $webhookService->register();

        // Initialize Email Dispatcher & Cron
        $emailService = $this->container->get(\Readscope\Services\EmailService::class);
        $emailService->register();

        // Initialize Privacy Tools & GDPR Compliance
        \Readscope\Support\Privacy::register();

        // Register daily retention cron hook
        add_action('readscope_daily_privacy_retention', [\Readscope\Services\PrivacyService::class, 'runDailyRetention']);
        // The same daily event also drops expired rate-limit counters.
        add_action('readscope_daily_privacy_retention', [\Readscope\Support\RateLimiter::class, 'purgeExpired']);
        // Scheduling reads the translated cron schedule labels, so it waits for init.
        add_action('init', static function (): void {
            if (!wp_next_scheduled('readscope_daily_privacy_retention')) {
                wp_schedule_event(time(), 'daily', 'readscope_daily_privacy_retention');
            }
        }, 20);

        // Register WP-CLI command if CLI environment
        if (defined('WP_CLI') && WP_CLI && class_exists('\WP_CLI')) {
            \WP_CLI::add_command('readscope', \Readscope\Cli\ReadscopeCli::class);
        }

        // Currency code filter
        add_filter('readscope/currency_code', static function (string $default = 'USD'): string {
            return (string) Config::get('currency', $default);
        });

        // Register custom image sizes or core setups
        $this->addAction('after_setup_theme', [$this, 'setupThemeSupport']);
    }

    /**
     * Add thumbnail sizes for tours and hotels.
     *
     * @return void
     */
    public function setupThemeSupport(): void
    {
        add_image_size('readscope-card', 600, 400, true);
        add_image_size('readscope-gallery', 1200, 800, true);
        add_image_size('readscope-thumbnail', 150, 150, true);
    }
}
