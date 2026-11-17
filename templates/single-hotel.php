<?php
/**
 * Single Hotel Template
 *
 * This template can be overridden by copying it to yourtheme/readscope/single-hotel.php.
 *
 * @package Readscope Booking
 */

if (!defined('ABSPATH')) {
    exit;
}

readscope_template_header();

$readscopeHotelId = (int) get_the_ID();
$readscopeHotel   = new \Readscope\Models\Hotel($readscopeHotelId);
$readscopeRooms   = $readscopeHotel->getRooms();
$readscopeSymbol  = (string) apply_filters('readscope/currency_symbol', '$');
?>

<div id="readscope-single-hotel-<?php the_ID(); ?>" <?php post_class('readscope-single-wrapper readscope-single-hotel'); ?>>
    <div class="readscope-container">
        <?php readscope_place_search(); ?>
        <!-- Top Breadcrumbs / Back link -->
        <div class="readscope-top-meta">
            <a href="<?php echo esc_url(get_post_type_archive_link('readscope_hotel')); ?>" class="back-link">&larr; <?php echo esc_html(_x('Hotels', 'public archive title', 'the-readscope-tour-and-accommodation-booking')); ?></a>
        </div>

        <header class="readscope-header-section">
            <div class="readscope-stars-row">
                <?php echo esc_html(str_repeat('★', $readscopeHotel->getStarRating())); ?>
            </div>
            <h1 class="readscope-title"><?php the_title(); ?></h1>
            <div class="readscope-sub-meta">
                <?php if ($readscopeHotel->getAddress()) : ?>
                    <span class="meta-tag"><span class="dashicons dashicons-location"></span> <?php echo esc_html($readscopeHotel->getAddress()); ?>, <?php echo esc_html($readscopeHotel->getCity()); ?></span>
                <?php endif; ?>

                <?php if ($readscopeHotel->getPhone()) : ?>
                    <span class="meta-tag"><span class="dashicons dashicons-phone"></span> <?php echo esc_html($readscopeHotel->getPhone()); ?></span>
                <?php endif; ?>

                <span class="meta-tag"><span class="dashicons dashicons-clock"></span> <?php
                echo esc_html(
                    sprintf(
                        /* translators: 1: Check-in time, 2: Check-out time */
                        __('Check-in: %1$s | Check-out: %2$s', 'the-readscope-tour-and-accommodation-booking'),
                        $readscopeHotel->getCheckInTime(),
                        $readscopeHotel->getCheckOutTime()
                    )
                );
                ?></span>
                <?php if ($readscopeHotel->getReviewCount() > 0) : ?>
                    <a href="#readscope-reviews" class="meta-tag meta-rating-link">
                        <span class="rating-star">★</span> <?php echo esc_html(number_format($readscopeHotel->getAverageRating(), 1)); ?> (<?php echo esc_html((string)$readscopeHotel->getReviewCount()); ?> <?php esc_html_e('reviews', 'the-readscope-tour-and-accommodation-booking'); ?>)
                    </a>
                <?php endif; ?>
                <button type="button" class="readscope-wishlist-toggle-single" data-id="<?php echo esc_attr((string)$readscopeHotelId); ?>" data-type="hotel">
                    <span class="dashicons dashicons-heart"></span> <span class="wishlist-btn-label"><?php esc_html_e('Save', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                </button>
            </div>
        </header>

        <!-- Featured Banner -->
        <?php if (has_post_thumbnail()) : ?>
            <div class="readscope-featured-banner">
                <?php the_post_thumbnail('large', ['class' => 'readscope-hero-img']); ?>
            </div>
        <?php endif; ?>

        <!-- Amenities -->
        <?php 
        $readscopeAmenities = $readscopeHotel->getAmenities();
        if (!empty($readscopeAmenities)) : 
        ?>
            <div class="readscope-block-section">
                <h2 class="section-heading"><?php esc_html_e('Property Amenities & Highlights', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                <div class="readscope-amenities-tags">
                    <?php foreach ($readscopeAmenities as $readscopeAmenity) : ?>
                        <span class="amenity-badge"><span class="dashicons dashicons-yes"></span> <?php echo esc_html($readscopeAmenity->name); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hotel Overview -->
        <div class="readscope-block-section">
            <h2 class="section-heading"><?php esc_html_e('About this Property', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </div>

        <!-- Available Rooms Section -->
        <div class="readscope-block-section">
            <h2 class="section-heading"><?php esc_html_e('Available Room Types & Rates', 'the-readscope-tour-and-accommodation-booking'); ?></h2>

            <?php if (!empty($readscopeRooms)) : ?>
                <div class="readscope-rooms-list">
                    <?php foreach ($readscopeRooms as $readscopeRoom) : ?>
                        <div class="readscope-room-card" id="room-<?php echo esc_attr((string)$readscopeRoom->getId()); ?>">
                            <div class="room-thumb-col">
                                <?php if ($readscopeRoom->getThumbnailUrl()) : ?>
                                    <img src="<?php echo esc_url($readscopeRoom->getThumbnailUrl('readscope-card')); ?>" alt="<?php echo esc_attr($readscopeRoom->getTitle()); ?>">
                                <?php else : ?>
                                    <div class="readscope-placeholder-img"><span class="dashicons dashicons-admin-home"></span></div>
                                <?php endif; ?>
                            </div>

                            <div class="room-info-col">
                                <h3 class="room-title"><?php echo esc_html($readscopeRoom->getTitle()); ?></h3>
                                <div class="room-specs">
                                    <span class="spec-item"><span class="dashicons dashicons-groups"></span> <?php
                                    echo esc_html(
                                        sprintf(
                                            /* translators: %d: Maximum guests */
                                            __('Up to %d Guests', 'the-readscope-tour-and-accommodation-booking'),
                                            $readscopeRoom->getMaxGuests()
                                        )
                                    );
                                    ?></span>
                                    <?php if ($readscopeRoom->getBedType()) : ?>
                                        <span class="spec-item"><span class="spec-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" focusable="false"><path d="M2 6h2v7h16a2 2 0 0 1 2 2v4h-2v-2H4v2H2V6zm5 1.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM10 8h7a3 3 0 0 1 3 3v1H10V8z"/></svg></span> <?php echo esc_html($readscopeRoom->getBedType()); ?></span>
                                    <?php endif; ?>
                                    <?php if ($readscopeRoom->getRoomSize()) : ?>
                                        <span class="spec-item"><span class="dashicons dashicons-editor-expand"></span> <?php echo esc_html($readscopeRoom->getRoomSize()); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="room-desc">
                                    <?php echo esc_html($readscopeRoom->getExcerpt(20)); ?>
                                </div>
                            </div>

                            <div class="room-price-col">
                                <div class="price-tag">
                                    <span class="amount"><?php echo esc_html($readscopeRoom->getFormattedPrice()); ?></span>
                                    <span class="unit">/ <?php esc_html_e('night', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                                </div>
                                <div class="room-booking-widget-wrap">
                                    <details class="room-reserve-dropdown">
                                        <summary class="readscope-btn readscope-btn-primary"><?php esc_html_e('Reserve Room', 'the-readscope-tour-and-accommodation-booking'); ?></summary>
                                        <div class="room-dropdown-content">
                                            <?php 
                                            readscope_get_template('booking-panel.php', [
                                                'readscopeItemId'   => $readscopeRoom->getId(),
                                                'readscopeItemType' => 'hotel_room',
                                            ]); 
                                            ?>
                                        </div>
                                    </details>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="no-rooms-msg"><?php esc_html_e('No rooms currently available for this property.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Reviews & Star Ratings Section -->
        <?php 
        readscope_get_template('reviews-section.php', ['readscopePostId' => $readscopeHotelId]); 
        ?>
        <?php readscope_related_place(); ?>
    </div>
</div>

<?php readscope_template_footer();
