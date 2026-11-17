<?php

declare(strict_types=1);

namespace Readscope\Tests\Integration;

use Readscope\Common\Container;
use Readscope\Repositories\InventoryRepository;
use Readscope\Services\BookingService;
use Readscope\Services\EmailService;
use Readscope\Services\InventoryService;
use Readscope\Services\PricingService;
use Readscope\Tests\TestCase;

/**
 * Class HookCompatTest
 *
 * The 'readscope/send_email' filter is asked once per email (in enqueue), never per attempt, and not for
 * test emails.
 */
class HookCompatTest extends TestCase
{
    protected EmailService $emailService;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanDatabaseTables();

        $GLOBALS['readscope_mock_sent_emails']      = [];
        $GLOBALS['readscope_mock_scheduled_events'] = [];
        $GLOBALS['readscope_mock_filters']          = [];
        $GLOBALS['readscope_mock_action_callbacks'] = [];
        $GLOBALS['readscope_mock_actions']          = [];
        update_option('readscope_settings', []);

        $inventory            = new InventoryService(new InventoryRepository());
        $pricing              = new PricingService($inventory);
        $this->emailService   = new EmailService();
        $this->bookingService = new BookingService($inventory, $this->emailService, $pricing);

        $container = Container::getInstance();
        $container->instance(InventoryService::class, $inventory);
        $container->instance(EmailService::class, $this->emailService);
        $container->instance(BookingService::class, $this->bookingService);
        $container->instance(PricingService::class, $pricing);

        $this->emailService->register();
    }

    private function book(): array
    {
        return $this->bookingService->createBooking([
            'item_id'        => $this->createTour(100.0, 10),
            'check_in'       => gmdate('Y-m-d', strtotime('+10 days')),
            'adults'         => 2,
            'customer_name'  => 'Ayesha Rahman',
            'customer_email' => 'ayesha@example.com',
            'customer_phone' => '+8801711000111',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function emailJobs(): array
    {
        return array_values(array_filter(
            $GLOBALS['readscope_mock_scheduled_events'] ?? [],
            static fn ($e) => $e['hook'] === EmailService::QUEUE_HOOK
        ));
    }

    // ------------------------------------------------------------------ readscope/send_email

    public function test_send_email_filter_is_asked_once_per_email_not_per_attempt(): void
    {
        $asked = [];
        add_filter('readscope/send_email', function ($send, $emailType) use (&$asked) {
            $asked[] = $emailType;
            return $send;
        }, 10, 3);

        $this->book();
        $jobs = $this->emailJobs();
        $this->assertNotEmpty($jobs, 'The booking queues emails.');
        $this->assertCount(count($jobs), $asked, 'One question per queued email.');

        // The worker, and a retry of the same job, do not ask again.
        $GLOBALS['readscope_mock_scheduled_events'] = [];
        foreach ($jobs as $job) {
            $this->emailService->dispatch(...$job['args']);
            $args    = $job['args'];
            $args[3] = 2;
            $this->emailService->dispatch(...$args);
        }
        $this->assertCount(count($jobs), $asked, 'dispatch() must not apply the filter again.');
        $this->assertNotEmpty($GLOBALS['readscope_mock_sent_emails'], 'Approved emails are delivered.');
    }

    public function test_suppressed_email_is_not_queued_at_all(): void
    {
        add_filter('readscope/send_email', static fn () => false, 10, 3);

        $this->book();
        $this->assertEmpty($this->emailJobs(), 'A suppressed email never reaches the queue.');
    }

    public function test_test_email_ignores_the_send_email_filter(): void
    {
        $asked = 0;
        add_filter('readscope/send_email', function () use (&$asked) {
            $asked++;
            return false;
        }, 10, 3);

        $this->assertTrue($this->emailService->sendTestEmail('admin@example.com'), 'The test email is sent.');
        $this->assertEquals(0, $asked, 'The filter is not consulted for test emails.');
        $this->assertCount(1, $GLOBALS['readscope_mock_sent_emails']);
    }
}
