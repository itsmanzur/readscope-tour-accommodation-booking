<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Admin\MetaBoxes\AvailabilityMetaBox;
use Readscope\Admin\MetaBoxes\HotelMetaBox;
use Readscope\Admin\MetaBoxes\RoomMetaBox;
use Readscope\Admin\MetaBoxes\TourMetaBox;
use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Config\Config;
use Readscope\Models\Hotel;
use Readscope\Models\Room;
use Readscope\Models\Tour;
use Readscope\PostTypes\HotelPostType;
use Readscope\PostTypes\RoomPostType;
use Readscope\PostTypes\TourPostType;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MetaBoxServiceProvider
 *
 * Registers metaboxes, admin scripts/styles for metaboxes, and custom columns for post listings.
 *
 * @package Readscope\Providers
 */
class MetaBoxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(TourMetaBox::class, fn () => new TourMetaBox());
        $this->container->singleton(HotelMetaBox::class, fn () => new HotelMetaBox());
        $this->container->singleton(RoomMetaBox::class, fn () => new RoomMetaBox());
        $this->container->singleton(AvailabilityMetaBox::class, fn () => new AvailabilityMetaBox());
    }

    public function boot(): void
    {
        if (!is_admin()) {
            return;
        }

        // Initialize MetaBoxes
        $this->container->get(TourMetaBox::class);
        $this->container->get(HotelMetaBox::class);
        $this->container->get(RoomMetaBox::class);
        $this->container->get(AvailabilityMetaBox::class);

        // Assets
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueMetaBoxAssets']);

        // Custom Admin List Columns - Tour
        $this->addFilter('manage_' . TourPostType::POST_TYPE . '_posts_columns', [$this, 'setTourColumns']);
        $this->addAction('manage_' . TourPostType::POST_TYPE . '_posts_custom_column', [$this, 'renderTourColumn'], 10, 2);

        // Custom Admin List Columns - Hotel
        $this->addFilter('manage_' . HotelPostType::POST_TYPE . '_posts_columns', [$this, 'setHotelColumns']);
        $this->addAction('manage_' . HotelPostType::POST_TYPE . '_posts_custom_column', [$this, 'renderHotelColumn'], 10, 2);

        // Custom Admin List Columns - Room
        $this->addFilter('manage_' . RoomPostType::POST_TYPE . '_posts_columns', [$this, 'setRoomColumns']);
        $this->addAction('manage_' . RoomPostType::POST_TYPE . '_posts_custom_column', [$this, 'renderRoomColumn'], 10, 2);
    }

    /**
     * Enqueue assets on post edit screens.
     *
     * @param string $hook
     * @return void
     */
    public function enqueueMetaBoxAssets(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, [TourPostType::POST_TYPE, HotelPostType::POST_TYPE, RoomPostType::POST_TYPE], true)) {
            return;
        }

        wp_enqueue_style(
            'readscope-admin-metabox',
            READSCOPE_PLUGIN_URL . 'assets/css/admin-metabox.css',
            ['dashicons'],
            READSCOPE_VERSION
        );

        \Readscope\Support\Typography::enqueue();

        wp_enqueue_script(
            'readscope-admin-metabox',
            READSCOPE_PLUGIN_URL . 'assets/js/admin-metabox.js',
            [],
            READSCOPE_VERSION,
            true
        );

        wp_localize_script('readscope-admin-metabox', 'readscopeAvailabilityConfig', [
            'ajaxUrl'          => admin_url('admin-ajax.php'),
            'nonce'            => wp_create_nonce('readscope_admin_nonce'),
            'isBengali'        => function_exists('readscope_is_bengali') && readscope_is_bengali(),
            'useBengaliDigits' => (bool) Config::get('use_bangla_digits', false),
            'monthNames'       => [
                __('January', 'the-readscope-tour-and-accommodation-booking'),
                __('February', 'the-readscope-tour-and-accommodation-booking'),
                __('March', 'the-readscope-tour-and-accommodation-booking'),
                __('April', 'the-readscope-tour-and-accommodation-booking'),
                __('May', 'the-readscope-tour-and-accommodation-booking'),
                __('June', 'the-readscope-tour-and-accommodation-booking'),
                __('July', 'the-readscope-tour-and-accommodation-booking'),
                __('August', 'the-readscope-tour-and-accommodation-booking'),
                __('September', 'the-readscope-tour-and-accommodation-booking'),
                __('October', 'the-readscope-tour-and-accommodation-booking'),
                __('November', 'the-readscope-tour-and-accommodation-booking'),
                __('December', 'the-readscope-tour-and-accommodation-booking'),
            ],
            'dayNamesShort'    => [
                __('Mon', 'the-readscope-tour-and-accommodation-booking'),
                __('Tue', 'the-readscope-tour-and-accommodation-booking'),
                __('Wed', 'the-readscope-tour-and-accommodation-booking'),
                __('Thu', 'the-readscope-tour-and-accommodation-booking'),
                __('Fri', 'the-readscope-tour-and-accommodation-booking'),
                __('Sat', 'the-readscope-tour-and-accommodation-booking'),
                __('Sun', 'the-readscope-tour-and-accommodation-booking'),
            ],
            'i18n'             => [
                'loading'         => __('Loading availability calendar...', 'the-readscope-tour-and-accommodation-booking'),
                'applying'        => __('Applying changes...', 'the-readscope-tour-and-accommodation-booking'),
                'noDatesSelected' => __('Please select at least one date from the calendar.', 'the-readscope-tour-and-accommodation-booking'),
                'noChanges'       => __('Please specify at least one change to apply (Status, Capacity, or Price).', 'the-readscope-tour-and-accommodation-booking'),
                /* translators: %d: number of selected dates */
                'selectedCount'   => __('%d date(s) selected', 'the-readscope-tour-and-accommodation-booking'),
                'statusAvailable' => __('Available', 'the-readscope-tour-and-accommodation-booking'),
                'statusBlocked'   => __('Blocked', 'the-readscope-tour-and-accommodation-booking'),
                'statusSoldOut'   => __('Sold Out', 'the-readscope-tour-and-accommodation-booking'),
                'confirmForce'    => __('Selected dates contain active bookings. Are you sure you want to force block future bookings on these dates?', 'the-readscope-tour-and-accommodation-booking'),
            ],
        ]);
    }

    /**
     * Tour custom columns.
     *
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public function setTourColumns(array $columns): array
    {
        $newColumns = [];
        foreach ($columns as $key => $title) {
            $newColumns[$key] = $title;
            if ($key === 'title') {
                $newColumns['tour_type'] = __('Type', 'the-readscope-tour-and-accommodation-booking');
                $newColumns['price']     = __('Price', 'the-readscope-tour-and-accommodation-booking');
                $newColumns['duration']  = __('Duration', 'the-readscope-tour-and-accommodation-booking');
            }
        }
        return $newColumns;
    }

    /**
     * Render Tour custom column value.
     *
     * @param string $column
     * @param int    $postId
     * @return void
     */
    public function renderTourColumn(string $column, int $postId): void
    {
        $tour = new Tour($postId);

        switch ($column) {
            case 'tour_type':
                echo esc_html(ucwords(str_replace('_', ' ', $tour->getTourType())));
                break;
            case 'price':
                echo '<strong>' . esc_html($tour->getFormattedPrice()) . '</strong>';
                if ($tour->getSalePrice()) {
                    echo ' <del style="color:#94a3b8; font-size: var(--trv-admin-text-xs);">$' . esc_html((string)$tour->getPrice()) . '</del>';
                }
                break;
            case 'duration':
                echo esc_html($tour->getDuration() ?: '—');
                break;
        }
    }

    /**
     * Hotel custom columns.
     *
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public function setHotelColumns(array $columns): array
    {
        $newColumns = [];
        foreach ($columns as $key => $title) {
            $newColumns[$key] = $title;
            if ($key === 'title') {
                $newColumns['star_rating'] = __('Rating', 'the-readscope-tour-and-accommodation-booking');
                $newColumns['city']        = __('City', 'the-readscope-tour-and-accommodation-booking');
                $newColumns['room_count']  = __('Rooms', 'the-readscope-tour-and-accommodation-booking');
            }
        }
        return $newColumns;
    }

    /**
     * Render Hotel custom column value.
     *
     * @param string $column
     * @param int    $postId
     * @return void
     */
    public function renderHotelColumn(string $column, int $postId): void
    {
        $hotel = new Hotel($postId);

        switch ($column) {
            case 'star_rating':
                echo esc_html(str_repeat('★', $hotel->getStarRating()));
                break;
            case 'city':
                echo esc_html($hotel->getCity() ?: '—');
                break;
            case 'room_count':
                echo esc_html((string) count($hotel->getRooms()));
                break;
        }
    }

    /**
     * Room custom columns.
     *
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public function setRoomColumns(array $columns): array
    {
        $newColumns = [];
        foreach ($columns as $key => $title) {
            $newColumns[$key] = $title;
            if ($key === 'title') {
                $newColumns['parent_hotel'] = esc_html__('Parent Hotel', 'the-readscope-tour-and-accommodation-booking');
                $newColumns['nightly_rate'] = esc_html__('Price / Night', 'the-readscope-tour-and-accommodation-booking');
                $newColumns['capacity']     = esc_html__('Capacity', 'the-readscope-tour-and-accommodation-booking');
            }
        }
        return $newColumns;
    }

    /**
     * Render Room custom column value.
     *
     * @param string $column
     * @param int    $postId
     * @return void
     */
    public function renderRoomColumn(string $column, int $postId): void
    {
        $room = new Room($postId);

        switch ($column) {
            case 'parent_hotel':
                $hotel = $room->getHotel();
                if ($hotel) {
                    echo '<a href="' . esc_url(get_edit_post_link($hotel->getId())) . '"><strong>' . esc_html($hotel->getTitle()) . '</strong></a>';
                } else {
                    echo '<span style="color:#ef4444;">' . esc_html__('Unassigned', 'the-readscope-tour-and-accommodation-booking') . '</span>';
                }
                break;
            case 'nightly_rate':
                echo '<strong>' . esc_html($room->getFormattedPrice()) . '</strong>';
                break;
            case 'capacity':
                echo esc_html(
                    sprintf(
                        /* translators: 1: Adults count, 2: Children count */
                        __('%1$d Adults, %2$d Children', 'the-readscope-tour-and-accommodation-booking'),
                        $room->getMaxAdults(),
                        $room->getMaxChildren()
                    )
                );
                break;
        }
    }
}
