<?php

declare(strict_types=1);

namespace Readscope\Admin\MetaBoxes;

use Readscope\Common\Abstracts\MetaBox;
use Readscope\PostTypes\RoomPostType;
use Readscope\PostTypes\TourPostType;
use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AvailabilityMetaBox
 *
 * Interactive Availability and Pricing Calendar manager metabox for Tour and Room edit screens.
 *
 * @package Readscope\Admin\MetaBoxes
 */
class AvailabilityMetaBox extends MetaBox
{
    protected string $id = 'readscope_availability_manager';
    protected string $title = 'Availability & Pricing Calendar';
    protected string|array $postTypes = [TourPostType::POST_TYPE, RoomPostType::POST_TYPE];
    protected string $context = 'normal';
    protected string $priority = 'high';

    public function getTitle(): string
    {
        return __('Availability & Pricing Calendar', 'the-readscope-tour-and-accommodation-booking');
    }

    public function render(WP_Post $post): void
    {
        $itemType = ($post->post_type === TourPostType::POST_TYPE) ? 'tour' : 'room';

        $this->loadView('admin/metaboxes/availability-metabox.php', [
            'post'     => $post,
            'itemId'   => (int) $post->ID,
            'itemType' => $itemType,
        ]);
    }

    public function save(int $postId, WP_Post $post): void
    {
        // Availability grid changes are saved via direct AJAX bulk actions
    }
}
