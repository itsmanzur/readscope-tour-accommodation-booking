<?php

declare(strict_types=1);

namespace Tourivo\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stylesheets and scripts of the customer pages (thank-you, my bookings, lookup, voucher) and the setup wizard.
 *
 * Every asset is registered once on `wp_enqueue_scripts` / `admin_enqueue_scripts` and enqueued by the
 * template that needs it, so nothing is printed inline. Values that come from PHP (nonces, URLs,
 * translated strings, booking data) are handed over as one JSON object per script.
 *
 * @package Tourivo\Support
 */
final class PortalAssets
{
    /**
     * Asset definitions: handle, files (relative to assets/) and the JavaScript global that receives the data.
     *
     * @var array<string, array{style?: string, script?: string, global?: string}>
     */
    private const ASSETS = [
        'thank-you'    => ['style' => 'css/tourivo-thank-you.css', 'script' => 'js/tourivo-thank-you.js', 'global' => 'tourivoThankYouConfig'],
        'my-bookings'  => ['style' => 'css/tourivo-my-bookings.css', 'script' => 'js/tourivo-my-bookings.js', 'global' => 'tourivoMyBookingsConfig'],
        'lookup'       => ['style' => 'css/tourivo-booking-lookup.css', 'script' => 'js/tourivo-booking-lookup.js', 'global' => 'tourivoLookupConfig'],
        'voucher'      => ['style' => 'css/tourivo-voucher.css', 'script' => 'js/tourivo-voucher.js'],
        'setup-wizard' => ['style' => 'css/tourivo-setup-wizard.css', 'script' => 'js/tourivo-setup-wizard.js', 'global' => 'tourivoWizardConfig'],
    ];

    /**
     * Register the assets on the front end and in wp-admin.
     *
     * @return void
     */
    public static function boot(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'registerAll']);
        add_action('admin_enqueue_scripts', [self::class, 'registerAll']);
    }

    /**
     * Register every asset (no output until one is enqueued).
     *
     * @return void
     */
    public static function registerAll(): void
    {
        foreach (array_keys(self::ASSETS) as $key) {
            self::register($key);
        }
    }

    /**
     * Enqueue one asset group and attach its data.
     *
     * @param string               $key  Asset key, for example `lookup`.
     * @param array<string, mixed> $data Values for the script's JavaScript global.
     * @return void
     */
    public static function enqueue(string $key, array $data = []): void
    {
        if (!isset(self::ASSETS[$key])) {
            return;
        }

        self::register($key);

        $def = self::ASSETS[$key];

        if (isset($def['style'])) {
            wp_enqueue_style(self::handle($key));
        }

        if (isset($def['script'])) {
            wp_enqueue_script(self::handle($key));

            if (isset($def['global'])) {
                $script = self::inlineConfigScript($def['global'], $data);
                if ($script !== '') {
                    wp_add_inline_script(self::handle($key), $script, 'before');
                }
            }
        }
    }

    /**
     * Build the inline `var Global = {...};` statement for a script's data.
     *
     * Values are JSON-encoded with the HEX flags (<, >, &, ' and " become \uXXXX escapes) so they cannot
     * close the script tag or break out of a string. The global name is a hard-coded identifier from
     * ASSETS, validated here anyway; an invalid name emits nothing.
     *
     * @param string               $global JavaScript global name.
     * @param array<string, mixed> $data   Values for the global.
     * @return string Script source, or '' when the name is not a valid identifier.
     */
    public static function inlineConfigScript(string $global, array $data): string
    {
        if (preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $global) !== 1) {
            return '';
        }

        $json = wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if (!is_string($json) || $json === '') {
            $json = '{}';
        }

        return 'var ' . $global . ' = ' . $json . ';';
    }

    /**
     * Handle shared by an asset group's style and script.
     *
     * @param string $key Asset key.
     * @return string
     */
    public static function handle(string $key): string
    {
        return 'tourivo-' . $key;
    }

    /**
     * Register one asset group once.
     *
     * @param string $key Asset key.
     * @return void
     */
    private static function register(string $key): void
    {
        $def    = self::ASSETS[$key];
        $handle = self::handle($key);

        if (isset($def['style']) && !wp_style_is($handle, 'registered')) {
            wp_register_style($handle, TOURIVO_PLUGIN_URL . 'assets/' . $def['style'], [], TOURIVO_VERSION);
        }

        if (isset($def['script']) && !wp_script_is($handle, 'registered')) {
            wp_register_script($handle, TOURIVO_PLUGIN_URL . 'assets/' . $def['script'], [], TOURIVO_VERSION, true);
        }
    }
}
