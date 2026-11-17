<?php

declare(strict_types=1);

namespace Readscope\Elementor\Widgets;

use Readscope\Shortcodes\SearchBarShortcode;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

/**
 * Class SearchBarWidget
 *
 * Elementor Widget for embedding the Readscope Booking search filter bar.
 *
 * @package Readscope\Elementor\Widgets
 */
class SearchBarWidget extends \Elementor\Widget_Base
{
    public function get_name(): string
    {
        return 'readscope_search_bar';
    }

    public function get_title(): string
    {
        return esc_html__('Search Bar', 'the-readscope-tour-and-accommodation-booking');
    }

    public function get_icon(): string
    {
        return 'eicon-search-results';
    }

    public function get_categories(): array
    {
        return ['readscope-elements'];
    }

    public function get_keywords(): array
    {
        return ['readscope', 'search', 'bar', 'filter', 'destination'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Search Bar Settings', 'the-readscope-tour-and-accommodation-booking'),
            ]
        );

        $this->add_control(
            'info_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw'  => esc_html__('This widget renders the responsive Destination, Date, and Travelers search bar.', 'the-readscope-tour-and-accommodation-booking'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render(): void
    {
        \Readscope\Support\Html::print(SearchBarShortcode::render());
    }
}
