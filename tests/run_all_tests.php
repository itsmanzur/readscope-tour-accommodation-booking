<?php
/**
 * Readscope Booking Master Test Suite Runner
 *
 * Runs all unit, integration, and security verification tests.
 * Usage: php tests/run_all_tests.php
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/TestCase.php';

// Load Unit tests
require_once __DIR__ . '/Unit/MoneyTest.php';
require_once __DIR__ . '/Unit/ClientIpTest.php';
require_once __DIR__ . '/Unit/RateLimiterTest.php';
require_once __DIR__ . '/Unit/PortalAssetsTest.php';
require_once __DIR__ . '/Unit/EmailHtmlTest.php';
require_once __DIR__ . '/Unit/PublicSearchTest.php';
require_once __DIR__ . '/Unit/DateValidationTest.php';

// Load Integration tests
require_once __DIR__ . '/Integration/ContainerWiringTest.php';
require_once __DIR__ . '/Integration/InventoryHoldTest.php';
require_once __DIR__ . '/Integration/BookingHygieneTest.php';
require_once __DIR__ . '/Integration/MediumFixesTest.php';
require_once __DIR__ . '/Integration/LowFixesTest.php';
require_once __DIR__ . '/Integration/HtmlAllowListTest.php';
require_once __DIR__ . '/Integration/BookingEngineTest.php';
require_once __DIR__ . '/Integration/BookingStatusTransitionTest.php';
require_once __DIR__ . '/Integration/ConcurrencyOverbookingTest.php';
require_once __DIR__ . '/Integration/RestSecurityTest.php';
require_once __DIR__ . '/Integration/LookupVoucherTest.php';
require_once __DIR__ . '/Integration/WebhookTest.php';
require_once __DIR__ . '/Integration/DataAndMiscTest.php';
require_once __DIR__ . '/Integration/AdminAvailabilityManagerTest.php';
require_once __DIR__ . '/Integration/PricingEngineTest.php';
require_once __DIR__ . '/Integration/EmailSystemTest.php';
require_once __DIR__ . '/Integration/PostBookingExperienceTest.php';
require_once __DIR__ . '/Integration/PrivacyGdprTest.php';
require_once __DIR__ . '/Integration/MyBookingsPortalTest.php';
require_once __DIR__ . '/Integration/ExportReportingTest.php';
require_once __DIR__ . '/Integration/ReportsAnalyticsTest.php';
require_once __DIR__ . '/Integration/OfflinePaymentTest.php';
require_once __DIR__ . '/Integration/TravelHomepageOptInTest.php';
require_once __DIR__ . '/Integration/PricingAdjustmentsTest.php';
require_once __DIR__ . '/Integration/MultiItemBookingTest.php';
require_once __DIR__ . '/Integration/ReviewFixesTest.php';
require_once __DIR__ . '/Integration/HookCompatTest.php';

echo "=====================================================\n";
echo "           Readscope Booking Automated Test Suite\n";
echo "=====================================================\n\n";

$testClasses = [
    \Readscope\Tests\Unit\MoneyTest::class,
    \Readscope\Tests\Unit\ClientIpTest::class,
    \Readscope\Tests\Unit\RateLimiterTest::class,
    \Readscope\Tests\Unit\PortalAssetsTest::class,
    \Readscope\Tests\Unit\EmailHtmlTest::class,
    \Readscope\Tests\Unit\PublicSearchTest::class,
    \Readscope\Tests\Unit\DateValidationTest::class,
    \Readscope\Tests\Integration\ContainerWiringTest::class,
    \Readscope\Tests\Integration\InventoryHoldTest::class,
    \Readscope\Tests\Integration\BookingHygieneTest::class,
    \Readscope\Tests\Integration\MediumFixesTest::class,
    \Readscope\Tests\Integration\LowFixesTest::class,
    \Readscope\Tests\Integration\HtmlAllowListTest::class,
    \Readscope\Tests\Integration\BookingEngineTest::class,
    \Readscope\Tests\Integration\BookingStatusTransitionTest::class,
    \Readscope\Tests\Integration\ConcurrencyOverbookingTest::class,
    \Readscope\Tests\Integration\RestSecurityTest::class,
    \Readscope\Tests\Integration\LookupVoucherTest::class,
    \Readscope\Tests\Integration\WebhookTest::class,
    \Readscope\Tests\Integration\DataAndMiscTest::class,
    \Readscope\Tests\Integration\AdminAvailabilityManagerTest::class,
    \Readscope\Tests\Integration\PricingEngineTest::class,
    \Readscope\Tests\Integration\EmailSystemTest::class,
    \Readscope\Tests\Integration\PostBookingExperienceTest::class,
    \Readscope\Tests\Integration\PrivacyGdprTest::class,
    \Readscope\Tests\Integration\MyBookingsPortalTest::class,
    \Readscope\Tests\Integration\ExportReportingTest::class,
    \Readscope\Tests\Integration\ReportsAnalyticsTest::class,
    \Readscope\Tests\Integration\OfflinePaymentTest::class,
    \Readscope\Tests\Integration\TravelHomepageOptInTest::class,
    \Readscope\Tests\Integration\PricingAdjustmentsTest::class,
    \Readscope\Tests\Integration\MultiItemBookingTest::class,
    \Readscope\Tests\Integration\ReviewFixesTest::class,
    \Readscope\Tests\Integration\HookCompatTest::class,
];

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$failures = [];

class SimpleAssertionTrait {
    public function assertTrue($condition, string $msg = ''): void {
        if (!$condition) throw new \Exception($msg ?: 'Failed asserting that condition is true.');
    }
    public function assertFalse($condition, string $msg = ''): void {
        if ($condition) throw new \Exception($msg ?: 'Failed asserting that condition is false.');
    }
    public function assertEquals($expected, $actual, string $msg = ''): void {
        if ($expected !== $actual) throw new \Exception($msg ?: "Failed asserting that '$actual' equals '$expected'.");
    }
    public function assertNotEquals($expected, $actual, string $msg = ''): void {
        if ($expected === $actual) throw new \Exception($msg ?: "Failed asserting that '$actual' does not equal '$expected'.");
    }
    public function assertNotEmpty($value, string $msg = ''): void {
        if (empty($value)) throw new \Exception($msg ?: 'Failed asserting that value is not empty.');
    }
    public function assertEmpty($value, string $msg = ''): void {
        if (!empty($value)) throw new \Exception($msg ?: 'Failed asserting that value is empty.');
    }
    public function assertCount(int $count, $array, string $msg = ''): void {
        if (count($array) !== $count) throw new \Exception($msg ?: "Failed asserting that count is $count.");
    }
    public function assertStringContainsString(string $needle, string $haystack, string $msg = ''): void {
        if (!str_contains($haystack, $needle)) throw new \Exception($msg ?: "Failed asserting that '$haystack' contains '$needle'.");
    }
    public function assertStringNotContainsString(string $needle, string $haystack, string $msg = ''): void {
        if (str_contains($haystack, $needle)) throw new \Exception($msg ?: "Failed asserting that '$haystack' does not contain '$needle'.");
    }
    public function assertNotNull($value, string $msg = ''): void {
        if ($value === null) throw new \Exception($msg ?: 'Failed asserting that value is not null.');
    }
    public function assertArrayHasKey($key, $array, string $msg = ''): void {
        if (!array_key_exists($key, $array)) throw new \Exception($msg ?: "Failed asserting array has key '$key'.");
    }
    public function assertSame($expected, $actual, string $msg = ''): void {
        if ($expected !== $actual) throw new \Exception($msg ?: "Failed asserting that '$actual' is identical to '$expected'.");
    }
    public function assertContains($needle, $haystack, string $msg = ''): void {
        if (!in_array($needle, $haystack, true)) throw new \Exception($msg ?: "Failed asserting that array contains item.");
    }
    public function assertNotContains($needle, $haystack, string $msg = ''): void {
        if (in_array($needle, $haystack, true)) throw new \Exception($msg ?: "Failed asserting that array does not contain item.");
    }
    public function assertStringStartsWith(string $prefix, string $string, string $msg = ''): void {
        if (!str_starts_with($string, $prefix)) throw new \Exception($msg ?: "Failed asserting that string starts with prefix.");
    }
}

foreach ($testClasses as $class) {
    echo "Suite: " . substr(strrchr($class, "\\"), 1) . "\n";
    $reflection = new \ReflectionClass($class);
    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

    $instance = new $class();
    if (!method_exists($instance, 'assertTrue')) {
        // Inject assertion methods if not running under full PHPUnit binary
        class_alias(SimpleAssertionTrait::class, 'InjectedAssertions');
    }

    foreach ($methods as $method) {
        if (str_starts_with($method->getName(), 'test_')) {
            $totalTests++;
            $testName = $method->getName();
            try {
                // Call setUp if present
                $setupMethod = $reflection->getMethod('setUp');
                $setupMethod->setAccessible(true);
                $setupMethod->invoke($instance);

                $method->invoke($instance);
                $passedTests++;
                echo "  [PASS] {$testName}\n";
            } catch (\Throwable $e) {
                $failedTests++;
                $failures[] = "{$class}::{$testName} -> " . $e->getMessage();
                echo "  [FAIL] {$testName}: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\n";
}

echo "=====================================================\n";
echo "Test Results: {$passedTests} passed, {$failedTests} failed (Total {$totalTests})\n";
echo "=====================================================\n";

if ($failedTests > 0) {
    echo "\nFailures Summary:\n";
    foreach ($failures as $failure) {
        echo " - {$failure}\n";
    }
    exit(1);
}

echo "All test suites executed successfully!\n";
exit(0);
