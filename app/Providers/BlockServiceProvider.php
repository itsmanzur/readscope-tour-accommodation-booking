<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Blocks\BlockManager;
use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Shortcodes\BookingLookupShortcode;
use Readscope\Shortcodes\BookingPanelShortcode;
use Readscope\Shortcodes\CurrencySwitcherShortcode;
use Readscope\Shortcodes\FilterSearchShortcode;
use Readscope\Shortcodes\HotelGridShortcode;
use Readscope\Shortcodes\MyBookingsShortcode;
use Readscope\Shortcodes\SearchBarShortcode;
use Readscope\Shortcodes\ThankYouShortcode;
use Readscope\Shortcodes\TourGridShortcode;
use Readscope\Shortcodes\WishlistShortcode;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BlockServiceProvider
 *
 * Registers shortcodes and Gutenberg blocks.
 *
 * @package Readscope\Providers
 */
class BlockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings
    }

    public function boot(): void
    {
        // 1. Register Shortcodes
        BookingPanelShortcode::register();
        BookingLookupShortcode::register();
        MyBookingsShortcode::register();
        ThankYouShortcode::register();
        TourGridShortcode::register();
        HotelGridShortcode::register();
        SearchBarShortcode::register();
        FilterSearchShortcode::register();
        WishlistShortcode::register();
        CurrencySwitcherShortcode::register();

        // 2. Register Gutenberg Blocks on init
        $this->addAction('init', [BlockManager::class, 'registerBlocks'], 20);
    }
}

