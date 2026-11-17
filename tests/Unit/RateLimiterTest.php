<?php
/**
 * RateLimiter Unit Tests: atomic fixed-window counter (DB path), window reset, clean-up and failure modes.
 */

declare(strict_types=1);

namespace Readscope\Tests\Unit;

use Readscope\Support\RateLimiter;
use Readscope\Tests\TestCase;

class RateLimiterTest extends TestCase
{
    private int $now = 1_800_000_000;

    protected function setUp(): void
    {
        parent::setUp();
        global $wpdb;
        $wpdb->rl_rows = [];
        $wpdb->rl_fail = false;
        $GLOBALS['readscope_mock_ext_object_cache'] = false;
        $GLOBALS['readscope_current_user_id']       = 0;
        unset($GLOBALS['readscope_mock_user_caps']);
        RateLimiter::$clock = fn (): int => $this->now;
    }

    protected function tearDown(): void
    {
        global $wpdb;
        $wpdb->rl_rows = [];
        $wpdb->rl_fail = false;
        RateLimiter::$clock = null;
        $GLOBALS['readscope_current_user_id'] = 0;
        unset($GLOBALS['readscope_mock_user_caps']);
        parent::tearDown();
    }

    public function test_twenty_sequential_calls_with_limit_five_allow_exactly_five(): void
    {
        $allowed = 0;
        for ($i = 0; $i < 20; $i++) {
            if (RateLimiter::hit('t_rl_seq', 5, 600)) {
                $allowed++;
            }
        }

        $this->assertEquals(5, $allowed);
    }

    public function test_counter_resets_after_the_window_passes(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue(RateLimiter::hit('t_rl_win', 5, 60));
        }
        $this->assertFalse(RateLimiter::hit('t_rl_win', 5, 60));

        $this->now += 59;
        $this->assertFalse(RateLimiter::hit('t_rl_win', 5, 60), 'Still inside the window.');

        $this->now += 1; // expiry reached
        $this->assertTrue(RateLimiter::hit('t_rl_win', 5, 60), 'First hit of a new window.');
        for ($i = 0; $i < 4; $i++) {
            $this->assertTrue(RateLimiter::hit('t_rl_win', 5, 60));
        }
        $this->assertFalse(RateLimiter::hit('t_rl_win', 5, 60));
    }

    public function test_buckets_are_independent_and_reset_clears_one(): void
    {
        $this->assertTrue(RateLimiter::hit('t_a', 1, 60));
        $this->assertFalse(RateLimiter::hit('t_a', 1, 60));
        $this->assertTrue(RateLimiter::hit('t_b', 1, 60));

        RateLimiter::reset('t_a');
        $this->assertTrue(RateLimiter::hit('t_a', 1, 60));
    }

    public function test_purge_removes_only_expired_rows(): void
    {
        RateLimiter::hit('t_old', 5, 60);
        RateLimiter::hit('t_new', 5, 600);
        $this->now += 120;

        $this->assertEquals(1, RateLimiter::purgeExpired());
        global $wpdb;
        $this->assertCount(1, $wpdb->rl_rows);
    }

    public function test_database_failure_fails_closed_for_anonymous_requests(): void
    {
        global $wpdb;
        $wpdb->rl_fail = true;

        $this->assertFalse(RateLimiter::hit('t_fail', 5, 60));
    }

    public function test_database_failure_fails_open_for_administrators_only(): void
    {
        global $wpdb;
        $wpdb->rl_fail = true;
        $GLOBALS['readscope_current_user_id'] = 7;

        $GLOBALS['readscope_mock_user_caps'] = ['manage_options' => true];
        $this->assertTrue(RateLimiter::hit('t_fail_admin', 5, 60));

        $GLOBALS['readscope_mock_user_caps'] = ['manage_options' => false];
        $this->assertFalse(RateLimiter::hit('t_fail_user', 5, 60), 'A logged-in non-admin is still limited.');
    }

    public function test_object_cache_path_is_used_when_available(): void
    {
        $GLOBALS['readscope_mock_ext_object_cache'] = true;

        $allowed = 0;
        for ($i = 0; $i < 20; $i++) {
            if (RateLimiter::hit('t_rl_cache', 5, 600)) {
                $allowed++;
            }
        }

        global $wpdb;
        $this->assertEquals(5, $allowed);
        $this->assertCount(0, $wpdb->rl_rows, 'No database row when the cache can increment.');
    }
}
