<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\PostTypes\HotelPostType;
use Readscope\PostTypes\RoomPostType;
use Readscope\PostTypes\TourPostType;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class PostTypeServiceProvider
 *
 * Registers all Readscope Booking custom post types and taxonomies.
 *
 * @package Readscope\Providers
 */
class PostTypeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings if needed
    }

    public function boot(): void
    {
        $this->addAction('init', [$this, 'registerPostTypesAndTaxonomies'], 5);
        $this->addAction('init', [$this, 'maybeFlushRewriteRules'], 99);
    }

    /**
     * Register post types and taxonomies.
     *
     * @return void
     */
    public function registerPostTypesAndTaxonomies(): void
    {
        TourPostType::register();
        HotelPostType::register();
        RoomPostType::register();
    }

    /**
     * Safely flush rewrite rules once after CPT registration if flagged during activation.
     *
     * @return void
     */
    public function maybeFlushRewriteRules(): void
    {
        if (get_option('readscope_flush_rewrite')) {
            flush_rewrite_rules(false);
            delete_option('readscope_flush_rewrite');
        }
    }
}
