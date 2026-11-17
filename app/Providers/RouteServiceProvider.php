<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Controllers\Api\AvailabilityController;
use Readscope\Controllers\Api\BookingController;
use Readscope\Controllers\Api\PricingController;
use Readscope\Repositories\InventoryRepository;
use Readscope\Services\BookingService;
use Readscope\Services\EmailService;
use Readscope\Services\InquiryService;
use Readscope\Services\InventoryService;
use Readscope\Services\PricingService;
use WP_REST_Server;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RouteServiceProvider
 *
 * Registers REST API endpoints and background cron handlers.
 *
 * @package Readscope\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    public const REST_NAMESPACE = 'readscope/v1';

    public function register(): void
    {
        $this->container->singleton(InventoryRepository::class, fn () => new InventoryRepository());
        $this->container->singleton(
            InventoryService::class,
            fn ($c) => new InventoryService($c->get(InventoryRepository::class))
        );
        $this->container->singleton(EmailService::class, fn () => new EmailService());
        $this->container->singleton(
            PricingService::class,
            fn ($c) => new PricingService($c->get(InventoryService::class))
        );
        $this->container->singleton(
            InquiryService::class,
            fn ($c) => new InquiryService($c->get(EmailService::class))
        );
        $this->container->singleton(
            BookingService::class,
            fn ($c) => new BookingService(
                $c->get(InventoryService::class),
                $c->get(EmailService::class),
                $c->get(PricingService::class)
            )
        );
        $this->container->singleton(
            PricingController::class,
            fn ($c) => new PricingController($c, $c->get(PricingService::class))
        );
        $this->container->singleton(
            AvailabilityController::class,
            fn ($c) => new AvailabilityController($c, $c->get(InventoryService::class))
        );
        $this->container->singleton(
            BookingController::class,
            fn ($c) => new BookingController($c, $c->get(BookingService::class))
        );
    }

    public function boot(): void
    {
        $this->addAction('rest_api_init', [$this, 'registerRestRoutes']);
        $this->addAction('readscope_cleanup_expired_holds', [$this, 'cleanupExpiredHolds']);
        $this->addAction('readscope_expire_stale_bookings', [$this, 'expireStaleBookings']);

        $this->addFilter('cron_schedules', [$this, 'registerCronSchedules']);

        // Schedule cron on init: the schedule label is translated, and translations must not load before init.
        $this->addAction('init', [$this, 'scheduleCronEvents'], 20);
    }

    /**
     * Schedule the recurring cleanup events if missing (re-schedules legacy hourly installs to the 5-minute cadence).
     *
     * @return void
     */
    public function scheduleCronEvents(): void
    {
        if (function_exists('wp_get_schedule') && wp_get_schedule('readscope_cleanup_expired_holds') === 'hourly') {
            wp_clear_scheduled_hook('readscope_cleanup_expired_holds');
        }
        if (!wp_next_scheduled('readscope_cleanup_expired_holds')) {
            wp_schedule_event(time(), 'readscope_five_minutes', 'readscope_cleanup_expired_holds');
        }
        if (!wp_next_scheduled('readscope_expire_stale_bookings')) {
            wp_schedule_event(time(), 'readscope_five_minutes', 'readscope_expire_stale_bookings');
        }
    }

    /**
     * Register the short cron interval used for releasing expired checkout holds.
     *
     * @param array<string, array<string, mixed>> $schedules
     * @return array<string, array<string, mixed>>
     */
    public function registerCronSchedules(array $schedules): array
    {
        $schedules['readscope_five_minutes'] = [
            'interval' => 300,
            'display'  => __('Every 5 minutes (Readscope Booking)', 'the-readscope-tour-and-accommodation-booking'),
        ];

        return $schedules;
    }

    /**
     * Register REST API routes.
     *
     * @return void
     */
    public function registerRestRoutes(): void
    {
        /** @var AvailabilityController $controller */
        $controller = $this->container->get(AvailabilityController::class);

        // GET /wp-json/readscope/v1/availability/check
        register_rest_route(self::REST_NAMESPACE, '/availability/check', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$controller, 'check'],
            'permission_callback' => '__return_true',
        ]);

        // GET /wp-json/readscope/v1/availability/calendar
        register_rest_route(self::REST_NAMESPACE, '/availability/calendar', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$controller, 'calendar'],
            'permission_callback' => '__return_true',
        ]);

        // GET /wp-json/readscope/v1/availability/search
        register_rest_route(self::REST_NAMESPACE, '/availability/search', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$controller, 'searchCalendar'],
            'permission_callback' => '__return_true',
        ]);

        // POST /wp-json/readscope/v1/availability/hold
        register_rest_route(self::REST_NAMESPACE, '/availability/hold', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$controller, 'hold'],
            'permission_callback' => [$this, 'authorizePublicWrite'],
        ]);

        /** @var PricingController $pricingController */
        $pricingController = $this->container->get(PricingController::class);

        // GET /wp-json/readscope/v1/pricing/quote
        register_rest_route(self::REST_NAMESPACE, '/pricing/quote', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$pricingController, 'quote'],
            'permission_callback' => '__return_true',
        ]);

        /** @var BookingController $bookingController */
        $bookingController = $this->container->get(BookingController::class);

        // POST /wp-json/readscope/v1/bookings/direct
        register_rest_route(self::REST_NAMESPACE, '/bookings/direct', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$bookingController, 'directCheckout'],
            'permission_callback' => [$this, 'authorizePublicWrite'],
        ]);
    }

    /**
     * Permission callback for the public booking endpoints.
     *
     * These endpoints are public by design (guests book without an account). The wp_rest nonce
     * localized on the booking form is CSRF protection only; abuse is limited by RateLimiter and
     * server-side validation in the controller.
     *
     * @param \WP_REST_Request $request
     * @return true|\WP_Error
     */
    public function authorizePublicWrite(\WP_REST_Request $request): bool|\WP_Error
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!is_string($nonce) || $nonce === '') {
            $nonce = (string) $request->get_param('_wpnonce');
        }

        // wp_verify_nonce() returns false for an empty string.
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Invalid or missing security token.', 'the-readscope-tour-and-accommodation-booking'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Cancel unverified / expired pending bookings and release their inventory.
     *
     * @return void
     */
    public function expireStaleBookings(): void
    {
        /** @var BookingService $bookings */
        $bookings = $this->container->get(BookingService::class);
        $bookings->expireStaleBookings();
    }

    /**
     * Cleanup expired checkout holds.
     *
     * @return void
     */
    public function cleanupExpiredHolds(): void
    {
        /** @var InventoryService $inventory */
        $inventory = $this->container->get(InventoryService::class);
        $inventory->releaseExpiredHolds();
    }
}
