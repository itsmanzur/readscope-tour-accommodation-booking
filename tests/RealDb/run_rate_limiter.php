<?php
/**
 * Real-MySQL / MariaDB verification of RateLimiter's atomic options-table counter.
 *
 *   1. 20 sequential hits, limit 5: exactly 5 allowed; after the window passes the counter restarts.
 *   2. WORKERS parallel processes each hit the same bucket PER_WORKER times (limit LIMIT): exactly LIMIT allowed.
 *
 * Usage: php tests/RealDb/run_rate_limiter.php
 * Environment: DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_NAME, DB_CREATE=1, TRV_PHP_FLAGS (pipe separated php -d flags),
 * WORKERS=4, PER_WORKER=25, LIMIT=40.
 * Exit code 0 = every check held.
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

require __DIR__ . '/RealWpdb.php';
require dirname(__DIR__, 2) . '/app/Support/RateLimiter.php';

if (!function_exists('wp_rand')) {
    function wp_rand(int $min = 0, int $max = 0): int
    {
        return mt_rand($min, $max);
    }
}

$host   = getenv('DB_HOST') ?: '127.0.0.1';
$port   = (int) (getenv('DB_PORT') ?: 3306);
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASSWORD') !== false ? (string) getenv('DB_PASSWORD') : 'root';
$dbName = getenv('DB_NAME') ?: 'readscope_test';
$flags  = getenv('TRV_PHP_FLAGS') ? explode('|', (string) getenv('TRV_PHP_FLAGS')) : [];

if (($argv[1] ?? '') === 'worker') {
    $GLOBALS['wpdb'] = $wk = new wpdb($host, $port, $user, $pass, $dbName, 'trvrl_');
    $wk->options = 'trvrl_options';
    $allowed = 0;
    for ($i = 0; $i < (int) $argv[2]; $i++) {
        if (\Readscope\Support\RateLimiter::hit('rl_parallel', (int) $argv[3], 600)) {
            $allowed++;
        }
    }
    echo $allowed;
    exit(0);
}

mysqli_report(MYSQLI_REPORT_OFF);
$admin = new mysqli($host, $user, $pass, '', $port);
if ($admin->connect_errno) {
    fwrite(STDERR, "Cannot connect to MySQL at {$host}:{$port}: {$admin->connect_error}\n");
    exit(2);
}
if (getenv('DB_CREATE') === '1') {
    $admin->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4");
}
$admin->select_db($dbName);
$admin->query('DROP TABLE IF EXISTS trvrl_options');
$admin->query('CREATE TABLE trvrl_options (option_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, option_name VARCHAR(191) NOT NULL DEFAULT \'\', option_value LONGTEXT NOT NULL, autoload VARCHAR(20) NOT NULL DEFAULT \'yes\', PRIMARY KEY (option_id), UNIQUE KEY option_name (option_name)) DEFAULT CHARSET=utf8mb4');

$GLOBALS['wpdb'] = $db = new wpdb($host, $port, $user, $pass, $dbName, 'trvrl_');
$db->options = 'trvrl_options';

$failures = 0;
$check = static function (string $label, bool $ok) use (&$failures): void {
    echo ($ok ? '  [PASS] ' : '  [FAIL] ') . $label . "\n";
    $failures += $ok ? 0 : 1;
};

echo "Server: " . $admin->server_info . "\n";

$now = 1_800_000_000;
\Readscope\Support\RateLimiter::$clock = static function () use (&$now): int {
    return $now;
};

$allowed = 0;
for ($i = 0; $i < 20; $i++) {
    $allowed += \Readscope\Support\RateLimiter::hit('rl_seq', 5, 60) ? 1 : 0;
}
$check('20 sequential calls, limit 5 -> exactly 5 allowed', $allowed === 5);

$now += 60;
$check('first call after the window passes is allowed', \Readscope\Support\RateLimiter::hit('rl_seq', 5, 60));
$allowed = 1;
for ($i = 0; $i < 10; $i++) {
    $allowed += \Readscope\Support\RateLimiter::hit('rl_seq', 5, 60) ? 1 : 0;
}
$check('new window allows exactly 5 again', $allowed === 5);

$now += 120;
$check('purgeExpired removes the expired row', \Readscope\Support\RateLimiter::purgeExpired() === 1);
\Readscope\Support\RateLimiter::$clock = null;

$workers   = max(2, (int) (getenv('WORKERS') ?: 4));
$perWorker = max(1, (int) (getenv('PER_WORKER') ?: 25));
$limit     = (int) (getenv('LIMIT') ?: 40);
$procs     = [];
foreach (range(1, $workers) as $w) {
    $cmd = array_merge([PHP_BINARY], $flags, [__FILE__, 'worker', (string) $perWorker, (string) $limit]);
    $p   = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['DB_NAME' => $dbName]));
    $procs[] = [$p, $pipes];
}
$total = 0;
foreach ($procs as [$p, $pipes]) {
    $total += (int) stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    if ($err !== '') {
        fwrite(STDERR, $err);
    }
    proc_close($p);
}
$check("{$workers} workers x {$perWorker} calls, limit {$limit} -> exactly {$limit} allowed (got {$total})", $total === $limit);

$admin->query('DROP TABLE IF EXISTS trvrl_options');
exit($failures === 0 ? 0 : 1);
