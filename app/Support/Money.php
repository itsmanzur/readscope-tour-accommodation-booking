<?php

declare(strict_types=1);

namespace Readscope\Support;

use Readscope\Config\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Money
 *
 * Centralized currency and price formatting helper according to global Readscope Booking settings.
 *
 * @package Readscope\Support
 */
class Money
{
    /**
     * Format a price float with configured currency symbol, decimal places, and position.
     *
     * @param float|int|string $amount
     * @param string|null $currencySymbol Currency glyph such as "$". This is not an ISO code.
     * @return string
     */
    public static function format(float|int|string $amount, ?string $currencySymbol = null): string
    {
        $num       = (float) $amount;
        $defSymbol = $currencySymbol ?: (string) Config::get('currency_symbol', '$');
        $symbol    = (string) apply_filters('readscope/currency_symbol', $defSymbol);
        $position  = (string) apply_filters('readscope/currency_position', (string) Config::get('currency_position', 'left'));
        $decimals  = (int) apply_filters('readscope/number_of_decimals', (int) Config::get('number_of_decimals', 2));
        $decPoint  = (string) apply_filters('readscope/decimal_separator', (string) Config::get('decimal_separator', '.'));
        $thousands = (string) apply_filters('readscope/thousand_separator', (string) Config::get('thousand_separator', ','));

        $formattedNum = number_format($num, $decimals, $decPoint, $thousands);

        $useBangla = (bool) apply_filters('readscope/use_bangla_digits', (bool) Config::get('use_bangla_digits', false));
        if ($useBangla && function_exists('readscope_bn_number')) {
            $formattedNum = readscope_bn_number($formattedNum);
        }

        return match ($position) {
            'right'       => $formattedNum . $symbol,
            'left_space'  => $symbol . ' ' . $formattedNum,
            'right_space' => $formattedNum . ' ' . $symbol,
            default       => $symbol . $formattedNum,
        };
    }
}
