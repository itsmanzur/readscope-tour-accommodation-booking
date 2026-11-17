<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Elementor\ElementorManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ElementorServiceProvider
 *
 * Hooks Readscope Booking into Elementor categories and widgets initialization.
 *
 * @package Readscope\Providers
 */
class ElementorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings
    }

    public function boot(): void
    {
        // Elementor Category registration
        $this->addAction('elementor/elements/categories_registered', [ElementorManager::class, 'registerCategories']);

        // Elementor Widgets registration
        $this->addAction('elementor/widgets/register', [ElementorManager::class, 'registerWidgets']);
    }
}
