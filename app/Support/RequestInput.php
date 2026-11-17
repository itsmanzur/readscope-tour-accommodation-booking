<?php

declare(strict_types=1);

namespace Readscope\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds a clean array from a request, taking only the keys a handler knows about.
 *
 * Each key gets one sanitizer, so raw `$_POST` / `$_GET` data never travels further than the handler
 * that read it, and unexpected keys are dropped.
 *
 * @package Readscope\Support
 */
final class RequestInput
{
    /**
     * Pick and sanitize the listed keys.
     *
     * Types: `text`, `key`, `email`, `textarea`, `int` (absint), `float`, `bool` (1 or 0).
     * Keys that are absent from the request are left out of the result.
     *
     * @param array<string, mixed>  $source Request data, still slashed (as PHP delivers it).
     * @param array<string, string> $schema Map of key => type.
     * @return array<string, mixed>
     */
    public static function pick(array $source, array $schema): array
    {
        $clean = [];

        foreach ($schema as $key => $type) {
            if (!isset($source[$key]) || is_array($source[$key])) {
                continue;
            }

            $value = wp_unslash((string) $source[$key]);

            switch ($type) {
                case 'int':
                    $clean[$key] = absint($value);
                    break;
                case 'float':
                    $clean[$key] = (float) $value;
                    break;
                case 'bool':
                    $clean[$key] = in_array(strtolower($value), ['', '0', 'false', 'off', 'no'], true) ? 0 : 1;
                    break;
                case 'key':
                    $clean[$key] = sanitize_key($value);
                    break;
                case 'email':
                    $clean[$key] = sanitize_email($value);
                    break;
                case 'textarea':
                    $clean[$key] = sanitize_textarea_field($value);
                    break;
                default:
                    $clean[$key] = sanitize_text_field($value);
            }
        }

        return $clean;
    }
}
