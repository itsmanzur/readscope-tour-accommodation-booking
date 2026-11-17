<?php
/**
 * Hotel Card Template
 *
 * @package Readscope Booking
 * @var \Readscope\Models\Hotel $hotel
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($hotel) || !($hotel instanceof \Readscope\Models\Hotel)) {
    return;
}

$readscopeMinPrice = $hotel->getMinPrice();
$readscopeSymbol = (string) apply_filters('readscope/currency_symbol', '$');
?>
<div class="readscope-card readscope-hotel-card">
    <div class="readscope-card-thumb">
        <a href="<?php echo esc_url($hotel->getPermalink()); ?>">
            <?php if ($hotel->getThumbnailUrl()) : ?>
                <img src="<?php echo esc_url($hotel->getThumbnailUrl('readscope-card')); ?>" alt="<?php echo esc_attr($hotel->getTitle()); ?>" loading="lazy">
            <?php else : ?>
                <div class="readscope-placeholder-img"><span class="dashicons dashicons-building"></span></div>
            <?php endif; ?>
        </a>

        <!-- Wishlist Button -->
        <button type="button" class="readscope-wishlist-toggle" data-id="<?php echo esc_attr((string)$hotel->getId()); ?>" data-type="hotel" title="<?php esc_attr_e('Save to Wishlist', 'the-readscope-tour-and-accommodation-booking'); ?>" aria-label="<?php esc_attr_e('Save to Wishlist', 'the-readscope-tour-and-accommodation-booking'); ?>">
            <span class="dashicons dashicons-heart"></span>
        </button>

        <div class="readscope-stars-badge">
            <?php echo esc_html(str_repeat('★', $hotel->getStarRating())); ?>
        </div>

        <?php if ($hotel->getCity()) : ?>
            <span class="readscope-dest-tag"><span class="dashicons dashicons-location"></span> <?php echo esc_html($hotel->getCity()); ?></span>
        <?php endif; ?>
    </div>

    <div class="readscope-card-body">
        <div class="readscope-card-meta">
            <span class="meta-item"><span class="dashicons dashicons-admin-home"></span> <?php
            echo esc_html(
                sprintf(
                    /* translators: %d: Number of room types */
                    __('%d Room Types', 'the-readscope-tour-and-accommodation-booking'),
                    count($hotel->getRooms())
                )
            );
            ?></span>
            <?php if ($hotel->getAddress()) : ?>
                <span class="meta-item text-truncate"><span class="dashicons dashicons-location-alt"></span> <?php echo esc_html($hotel->getAddress()); ?></span>
            <?php endif; ?>
            <?php if ($hotel->getReviewCount() > 0) : ?>
                <span class="meta-item meta-rating" title="<?php
                echo esc_attr(
                    sprintf(
                        /* translators: %s: Rating score out of 5 */
                        __('%s out of 5 stars', 'the-readscope-tour-and-accommodation-booking'),
                        $hotel->getAverageRating()
                    )
                );
                ?>">
                    <span class="rating-star">★</span> <?php echo esc_html(number_format($hotel->getAverageRating(), 1)); ?> <span class="rating-count">(<?php echo esc_html((string)$hotel->getReviewCount()); ?>)</span>
                </span>
            <?php endif; ?>
        </div>

        <h3 class="readscope-card-title">
            <a href="<?php echo esc_url($hotel->getPermalink()); ?>"><?php echo esc_html($hotel->getTitle()); ?></a>
        </h3>

        <div class="readscope-card-footer">
            <div class="readscope-price-box">
                <span class="price-from"><?php esc_html_e('Starts from', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="price-val"><?php echo esc_html(\Readscope\Support\Money::format($readscopeMinPrice)); ?></span>
                <span class="price-unit">/ <?php esc_html_e('night', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            </div>
            <a href="<?php echo esc_url($hotel->getPermalink()); ?>" class="readscope-btn readscope-btn-sm"><?php esc_html_e('View Rooms', 'the-readscope-tour-and-accommodation-booking'); ?> &rarr;</a>
        </div>
    </div>
</div>
