<?php
/**
 * Upgrade check for 1.0.x -> 1.1.0 on a real WordPress site.
 *
 * 1. Restore a database that was written by 1.0.x (names starting with "tourivo").
 * 2. Put the 1.1.0 plugin folder in place WITHOUT deactivating anything.
 *    Optional extras to seed with SQL first: a nav_menu_item post whose _menu_item_object meta is "tourivo_destination",
 *    and an option named "tourivo_rl_<md5>".
 * 3. Run:  php tests/manual/upgrade_from_1_0_x.php /path/to/wp-load.php
 *    Loading WordPress runs the migration; the script then asserts the result.
 *
 * It also verifies that tokens generated the way 1.0.x did still pass, that a second run changes nothing and
 * that no "tourivo" name is left in the tables, options, post types, meta, capabilities and cron events it renames.
 * Exit code 0 = every check held.
 */

declare(strict_types=1);

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

[$self, $wpLoad] = array_pad($argv, 2, '');
if (!is_file($wpLoad)) {
    fwrite(STDERR, "Usage: php upgrade_from_1_0_x.php /path/to/wp-load.php\n");
    exit(2);
}

$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require $wpLoad;

global $wpdb;
$fail = 0;
$ok   = static function (bool $cond, string $label) use (&$fail): void {
    echo ($cond ? '[PASS] ' : '[FAIL] ') . $label . "\n";
    if (!$cond) {
        $fail++;
    }
};

$count = static function (string $sql) use ($wpdb): int {
    return (int) $wpdb->get_var($sql);
};
$like = static function (string $s) use ($wpdb): string {
    return $wpdb->esc_like($s) . '%';
};

$ok(get_option('readscope_legacy_migrated') !== false, 'migration marked done');
$ok(get_option('readscope_legacy_migrating') === false, 'lock released');

foreach (['bookings', 'booking_items', 'inquiries', 'inventories', 'logs'] as $t) {
    $old = $wpdb->prefix . 'tourivo_' . $t;
    $ok(!$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $old)), "old table {$t} is gone");
    $ok((bool) $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->prefix . 'readscope_' . $t)), "new table {$t} exists");
}

$ok($count($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type LIKE %s", $like('tourivo_'))) === 0, 'no tourivo_* post type left');
$ok($count($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy LIKE %s", $like('tourivo_'))) === 0, 'no tourivo_* taxonomy left');
$ok($count($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s OR meta_key LIKE %s", $like('_tourivo_'), $like('tourivo_'))) === 0, 'no tourivo meta key left');
$ok($count($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s", $like('tourivo_'))) === 0, 'no tourivo_* option left (rate-limit rows and caches included)');
$ok($count($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s", '%' . $wpdb->esc_like('[tourivo_') . '%')) === 0, 'no [tourivo_*] shortcode left in content');
$ok($count($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_menu_item_object' AND meta_value LIKE %s", $like('tourivo_'))) === 0, 'nav menu items point at the new post types and taxonomies');

$role = get_role('administrator');
$ok($role && $role->has_cap('manage_readscope') && !isset($role->capabilities['manage_tourivo']), 'administrator capability renamed');

$stale = [];
foreach ((array) _get_cron_array() as $hooks) {
    foreach ((array) $hooks as $hook => $events) {
        if (str_starts_with((string) $hook, 'tourivo_')) {
            $stale[] = $hook;
        }
        foreach ((array) $events as $event) {
            if (is_array($event) && str_starts_with((string) ($event['schedule'] ?? ''), 'tourivo_')) {
                $stale[] = 'schedule ' . $event['schedule'];
            }
        }
    }
}
$ok(!$stale, 'cron events and recurrences renamed' . ($stale ? ' (left: ' . implode(', ', $stale) . ')' : ''));

// Old shortcode tags keep rendering.
$ok(shortcode_exists('tourivo_tours') && shortcode_exists('readscope_tours'), 'old and new shortcode tags both registered');

// Tokens made by 1.0.x still validate; new ones use the new prefix.
$salt   = wp_salt('nonce');
$row    = $wpdb->get_row("SELECT id, customer_email, access_key FROM {$wpdb->prefix}readscope_bookings ORDER BY id ASC LIMIT 1");
if ($row) {
    $key    = (string) ($row->access_key ?? '');
    $legacy = $key !== '' ? hash_hmac('sha256', "tourivo_voucher_{$row->id}_key_{$key}", $salt) : hash_hmac('sha256', "tourivo_voucher_{$row->id}_{$row->customer_email}", $salt);
    $ok(\Readscope\Shortcodes\BookingLookupShortcode::verifyVoucherToken((int) $row->id, (string) $row->customer_email, $key, $legacy), 'voucher / ICS link emailed by 1.0.x still opens');
    $issued = time() - 120;
    $ty     = $issued . '.' . hash_hmac('sha256', "tourivo_thankyou_{$row->id}_{$issued}", $salt);
    $ok(\Readscope\Shortcodes\ThankYouShortcode::verifyThankYouToken((int) $row->id, $ty), 'thank-you link emailed by 1.0.x still opens');
} else {
    echo "[SKIP] no booking row to test tokens against\n";
}

// A second run changes nothing.
$snapshot = static function () use ($wpdb): string {
    return md5(serialize([
        $wpdb->get_results("SELECT * FROM {$wpdb->prefix}readscope_bookings ORDER BY id", ARRAY_A),
        $wpdb->get_results("SELECT meta_key, COUNT(*) c FROM {$wpdb->postmeta} WHERE meta_key LIKE '\\_readscope\\_%' GROUP BY meta_key", ARRAY_A),
        $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type LIKE 'readscope\\_%'"),
    ]));
};
$before = $snapshot();
delete_option('readscope_legacy_migrated');
\Readscope\Database\LegacyMigrator::maybeRun();
$ok($before === $snapshot(), 'running the migration again changes nothing');

echo $fail === 0 ? "\nAll upgrade checks held.\n" : "\n{$fail} check(s) FAILED.\n";
exit($fail === 0 ? 0 : 1);
