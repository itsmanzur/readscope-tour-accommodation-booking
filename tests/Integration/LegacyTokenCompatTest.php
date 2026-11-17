<?php
/**
 * Links emailed by 1.0.x (HMAC tokens built with the old "tourivo" prefix) must keep working after the upgrade,
 * while every newly generated token uses the new prefix.
 */

declare(strict_types=1);

namespace Readscope\Tests\Integration;

use Readscope\Services\BookingService;
use Readscope\Services\WebhookService;
use Readscope\Shortcodes\BookingLookupShortcode;
use Readscope\Shortcodes\ThankYouShortcode;
use Readscope\Tests\TestCase;

class LegacyTokenCompatTest extends TestCase
{
    private function legacy(string $payload): string
    {
        return hash_hmac('sha256', $payload, wp_salt('nonce'));
    }

    public function test_voucher_token_with_access_key_accepts_legacy_and_new(): void
    {
        $new    = BookingLookupShortcode::generateVoucherToken(7, 'a@example.com', 'KEY123');
        $legacy = $this->legacy('tourivo_voucher_7_key_KEY123');

        $this->assertNotEquals($legacy, $new, 'New tokens must not use the legacy string.');
        $this->assertTrue(BookingLookupShortcode::verifyVoucherToken(7, 'a@example.com', 'KEY123', $new));
        $this->assertTrue(BookingLookupShortcode::verifyVoucherToken(7, 'a@example.com', 'KEY123', $legacy));
        $this->assertFalse(BookingLookupShortcode::verifyVoucherToken(7, 'a@example.com', 'KEY123', substr($legacy, 0, -1) . '0'));
        $this->assertFalse(BookingLookupShortcode::verifyVoucherToken(8, 'a@example.com', 'KEY123', $legacy), 'Bound to the booking id.');
        $this->assertFalse(BookingLookupShortcode::verifyVoucherToken(7, 'a@example.com', 'KEY123', ''));
    }

    public function test_voucher_token_without_access_key_accepts_legacy_and_new(): void
    {
        $new    = BookingLookupShortcode::generateVoucherToken(7, 'a@example.com');
        $legacy = $this->legacy('tourivo_voucher_7_a@example.com');

        $this->assertTrue(BookingLookupShortcode::verifyVoucherToken(7, 'a@example.com', null, $new));
        $this->assertTrue(BookingLookupShortcode::verifyVoucherToken(7, 'a@example.com', '', $legacy));
        $this->assertFalse(BookingLookupShortcode::verifyVoucherToken(7, 'b@example.com', null, $legacy), 'Bound to the email.');
        $this->assertFalse(BookingLookupShortcode::verifyVoucherToken(7, 'a@example.com', null, 'tampered'));
    }

    public function test_thank_you_token_accepts_legacy_inside_the_window_only(): void
    {
        $issued = time() - 60;
        $legacy = $issued . '.' . $this->legacy("tourivo_thankyou_9_{$issued}");

        $this->assertTrue(ThankYouShortcode::verifyThankYouToken(9, ThankYouShortcode::generateThankYouToken(9)));
        $this->assertTrue(ThankYouShortcode::verifyThankYouToken(9, $legacy));
        $this->assertFalse(ThankYouShortcode::verifyThankYouToken(10, $legacy), 'Bound to the booking id.');
        $this->assertFalse(ThankYouShortcode::verifyThankYouToken(9, $issued . '.' . str_repeat('0', 64)));

        $old        = time() - 73 * 3600;
        $oldLegacy  = $old . '.' . $this->legacy("tourivo_thankyou_9_{$old}");
        $this->assertFalse(ThankYouShortcode::verifyThankYouToken(9, $oldLegacy), 'The 72 hour expiry still applies.');
    }

    public function test_new_thank_you_and_verification_tokens_never_use_the_legacy_prefix(): void
    {
        $issued = time();
        $token  = ThankYouShortcode::generateThankYouToken(4, $issued);
        $this->assertNotEquals($issued . '.' . $this->legacy("tourivo_thankyou_4_{$issued}"), $token);

        $this->assertNotEquals(
            $this->legacy('tourivo_verify_4_a@example.com_' . ($issued + 100)),
            BookingService::generateVerificationToken(4, 'A@Example.com', $issued + 100)
        );
    }

    public function test_verification_token_accepts_legacy_with_and_without_expiry(): void
    {
        $service = (new \ReflectionClass(BookingService::class))->newInstanceWithoutConstructor();
        $match   = new \ReflectionMethod(BookingService::class, 'verificationTokenMatches');
        $match->setAccessible(true);
        $booking = (object) ['id' => 4, 'customer_email' => 'A@Example.com', 'created_at' => gmdate('Y-m-d H:i:s')];

        $exp = time() + 3600;
        $this->assertTrue($match->invoke($service, $booking, $this->legacy('tourivo_verify_4_a@example.com_' . $exp), $exp));
        $this->assertTrue($match->invoke($service, $booking, BookingService::generateVerificationToken(4, 'A@Example.com', $exp), $exp));
        $this->assertFalse($match->invoke($service, $booking, $this->legacy('tourivo_verify_4_a@example.com_' . $exp) . 'x', $exp), 'Tampered.');

        $past = time() - 10;
        $this->assertFalse($match->invoke($service, $booking, $this->legacy('tourivo_verify_4_a@example.com_' . $past), $past), 'Expired link stays expired.');

        // Oldest links carry no expiry; they are valid only within the booking age window.
        $this->assertTrue($match->invoke($service, $booking, $this->legacy('tourivo_verify_4_a@example.com'), 0));
        $booking->created_at = gmdate('Y-m-d H:i:s', time() - 90 * 86400);
        $this->assertFalse($match->invoke($service, $booking, $this->legacy('tourivo_verify_4_a@example.com'), 0));
    }

    public function test_webhook_sends_new_and_legacy_headers_with_identical_values(): void
    {
        $h = WebhookService::signatureHeaders('{"a":1}', 'secret', 1700000000);

        foreach (['Signature', 'Timestamp', 'Signature-V2'] as $name) {
            $this->assertArrayHasKey('X-Readscope-' . $name, $h);
            $this->assertArrayHasKey('X-Tourivo-' . $name, $h);
            $this->assertSame($h['X-Readscope-' . $name], $h['X-Tourivo-' . $name]);
        }
        $this->assertSame([], WebhookService::signatureHeaders('{}', ''));
    }

    public function test_deprecated_constants_alias_the_new_ones(): void
    {
        $main = (string) file_get_contents(dirname(__DIR__, 2) . '/the-readscope-tour-and-accommodation-booking.php');
        $this->assertStringContainsString("define('TOURIVO_VERSION', READSCOPE_VERSION)", $main);
        $this->assertStringContainsString("define('TOURIVO_API_LEVEL', READSCOPE_API_LEVEL)", $main);
    }
}
