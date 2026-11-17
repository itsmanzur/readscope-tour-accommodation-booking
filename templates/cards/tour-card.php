<?php
/**
 * Tour Card Template
 *
 * @package Readscope Booking
 * @var \Readscope\Models\Tour $tour
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($tour) || !($tour instanceof \Readscope\Models\Tour)) {
    return;
}
?>
<div class="readscope-card readscope-tour-card">
    <div class="readscope-card-thumb">
        <a href="<?php echo esc_url($tour->getPermalink()); ?>">
            <?php if ($tour->getThumbnailUrl()) : ?>
                <img src="<?php echo esc_url($tour->getThumbnailUrl('readscope-card')); ?>" alt="<?php echo esc_attr($tour->getTitle()); ?>" loading="lazy">
            <?php else : ?>
                <div class="readscope-placeholder-img"><span class="dashicons dashicons-palmtree"></span></div>
            <?php endif; ?>
        </a>

        <!-- Wishlist Button -->
        <button type="button" class="readscope-wishlist-toggle" data-id="<?php echo esc_attr((string)$tour->getId()); ?>" data-type="tour" title="<?php esc_attr_e('Save to Wishlist', 'the-readscope-tour-and-accommodation-booking'); ?>" aria-label="<?php esc_attr_e('Save to Wishlist', 'the-readscope-tour-and-accommodation-booking'); ?>">
            <span class="dashicons dashicons-heart"></span>
        </button>

        <?php if ($tour->getBadge()) : ?>
            <span class="readscope-ribbon"><?php echo esc_html($tour->getBadge()); ?></span>
        <?php endif; ?>

        <?php 
        $readscopeDestinations = $tour->getDestinations();
        if (!empty($readscopeDestinations)) : 
        ?>
            <span class="readscope-dest-tag"><span class="dashicons dashicons-location"></span> <?php echo esc_html($readscopeDestinations[0]->name); ?></span>
        <?php endif; ?>
    </div>

    <div class="readscope-card-body">
        <div class="readscope-card-meta">
            <?php if ($tour->getDuration()) : ?>
                <span class="meta-item"><span class="dashicons dashicons-clock"></span> <?php echo esc_html($tour->getDuration()); ?></span>
            <?php endif; ?>
            <span class="meta-item"><span class="dashicons dashicons-groups"></span> <?php
            echo esc_html(
                sprintf(
                    /* translators: %d: Maximum guests allowed */
                    __('Max: %d', 'the-readscope-tour-and-accommodation-booking'),
                    $tour->getMaxGuests()
                )
            );
            ?></span>
            <?php if ($tour->getReviewCount() > 0) : ?>
                <span class="meta-item meta-rating" title="<?php
                echo esc_attr(
                    sprintf(
                        /* translators: %s: Rating score out of 5 */
                        __('%s out of 5 stars', 'the-readscope-tour-and-accommodation-booking'),
                        $tour->getAverageRating()
                    )
                );
                ?>">
                    <span class="rating-star">★</span> <?php echo esc_html(number_format($tour->getAverageRating(), 1)); ?> <span class="rating-count">(<?php echo esc_html((string)$tour->getReviewCount()); ?>)</span>
                </span>
            <?php endif; ?>
        </div>

        <h3 class="readscope-card-title">
            <a href="<?php echo esc_url($tour->getPermalink()); ?>"><?php echo esc_html($tour->getTitle()); ?></a>
        </h3>

        <div class="readscope-card-footer">
            <div class="readscope-price-box">
                <span class="price-from"><?php esc_html_e('From', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="price-val"><?php echo esc_html($tour->getFormattedPrice()); ?></span>
                <?php if ($tour->isOnSale()) : ?>
                    <del class="price-original"><?php echo esc_html($tour->getFormattedRegularPrice()); ?></del>
                <?php endif; ?>
            </div>
            <a href="<?php echo esc_url($tour->getPermalink()); ?>" class="readscope-btn readscope-btn-sm"><?php esc_html_e('Explore', 'the-readscope-tour-and-accommodation-booking'); ?> &rarr;</a>
        </div>
    </div>
</div>
