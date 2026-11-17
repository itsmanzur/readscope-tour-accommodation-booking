<?php

declare(strict_types=1);

namespace Readscope\Elementor;

use Readscope\Elementor\Widgets\BookingPanelWidget;
use Readscope\Elementor\Widgets\HotelGridWidget;
use Readscope\Elementor\Widgets\SearchBarWidget;
use Readscope\Elementor\Widgets\TourGridWidget;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ElementorManager
 *
 * Integrates Readscope Booking with Elementor Page Builder.
 *
 * @package Readscope\Elementor
 */
class ElementorManager
{
    /**
     * Check if Elementor is installed and loaded.
     *
     * @return bool
     */
    public static function isElementorActive(): bool
    {
        return did_action('elementor/loaded') || class_exists('\Elementor\Plugin');
    }

    /**
     * Register Readscope Booking custom category in Elementor widget panel.
     *
     * @param \Elementor\Elements_Manager $elementsManager
     * @return void
     */
    public static function registerCategories($elementsManager): void
    {
        $elementsManager->add_category(
            'readscope-elements',
            [
                'title' => esc_html__('Readscope Booking Elements', 'the-readscope-tour-and-accommodation-booking'),
                'icon'  => 'fa fa-plug',
            ]
        );
    }

    /**
     * Register Elementor Widgets.
     *
     * @param \Elementor\Widgets_Manager $widgetsManager
     * @return void
     */
    public static function registerWidgets($widgetsManager): void
    {
        if (!class_exists('\Elementor\Widget_Base')) {
            return;
        }

        $widgets = [
            TourGridWidget::class,
            HotelGridWidget::class,
            BookingPanelWidget::class,
            SearchBarWidget::class,
        ];

        foreach ($widgets as $widgetClass) {
            if (class_exists($widgetClass)) {
                $widgetsManager->register(new $widgetClass());
            }
        }
    }
}
