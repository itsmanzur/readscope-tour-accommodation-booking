<?php
/**
 * Single Tour Template
 *
 * This template can be overridden by copying it to yourtheme/readscope/single-tour.php.
 *
 * @package Readscope Booking
 */

if (!defined('ABSPATH')) {
    exit;
}

readscope_template_header();

$readscopeTourId = (int) get_the_ID();
$readscopeTour   = new \Readscope\Models\Tour($readscopeTourId);
?>

<div id="readscope-single-tour-<?php the_ID(); ?>" <?php post_class('readscope-single-wrapper readscope-single-tour'); ?>>
    <div class="readscope-container">
        <?php readscope_place_search(); ?>
        <!-- Top Breadcrumbs / Back link -->
        <div class="readscope-top-meta">
            <a href="<?php echo esc_url(get_post_type_archive_link('readscope_tour')); ?>" class="back-link">&larr; <?php echo esc_html(_x('Tours', 'public archive title', 'the-readscope-tour-and-accommodation-booking')); ?></a>
            <?php if ($readscopeTour->getBadge()) : ?>
                <span class="readscope-ribbon-inline"><?php echo esc_html($readscopeTour->getBadge()); ?></span>
            <?php endif; ?>
        </div>

        <header class="readscope-header-section">
            <h1 class="readscope-title"><?php the_title(); ?></h1>
            <div class="readscope-sub-meta">
                <?php 
                $readscopeDestinations = $readscopeTour->getDestinations();
                if (!empty($readscopeDestinations)) : 
                ?>
                    <span class="meta-tag"><span class="dashicons dashicons-location"></span> <?php echo esc_html($readscopeDestinations[0]->name); ?></span>
                <?php endif; ?>

                <?php if ($readscopeTour->getDuration()) : ?>
                    <span class="meta-tag"><span class="dashicons dashicons-clock"></span> <?php echo esc_html($readscopeTour->getDuration()); ?></span>
                <?php endif; ?>

                <span class="meta-tag"><span class="dashicons dashicons-groups"></span> <?php
                echo esc_html(
                    sprintf(
                        /* translators: 1: Minimum guests, 2: Maximum guests */
                        __('Min: %1$d | Max: %2$d Pax', 'the-readscope-tour-and-accommodation-booking'),
                        $readscopeTour->getMinGuests(),
                        $readscopeTour->getMaxGuests()
                    )
                );
                ?></span>
                <span class="meta-tag"><span class="dashicons dashicons-tag"></span> <?php echo esc_html(ucwords(str_replace('_', ' ', $readscopeTour->getTourType()))); ?></span>
                <?php if ($readscopeTour->getReviewCount() > 0) : ?>
                    <a href="#readscope-reviews" class="meta-tag meta-rating-link">
                        <span class="rating-star">★</span> <?php echo esc_html(number_format($readscopeTour->getAverageRating(), 1)); ?> (<?php echo esc_html((string)$readscopeTour->getReviewCount()); ?> <?php esc_html_e('reviews', 'the-readscope-tour-and-accommodation-booking'); ?>)
                    </a>
                <?php endif; ?>
                <button type="button" class="readscope-wishlist-toggle-single" data-id="<?php echo esc_attr((string)$readscopeTourId); ?>" data-type="tour">
                    <span class="dashicons dashicons-heart"></span> <span class="wishlist-btn-label"><?php esc_html_e('Save', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                </button>
            </div>
        </header>

        <!-- Main Layout Grid: Content + Sticky Booking Sidebar -->
        <div class="readscope-layout-grid">
            <!-- Left Column: Tour Details -->
            <div class="readscope-main-content">
                <!-- Featured Image -->
                <?php if (has_post_thumbnail()) : ?>
                    <div class="readscope-featured-banner">
                        <?php the_post_thumbnail('large', ['class' => 'readscope-hero-img']); ?>
                    </div>
                <?php endif; ?>

                <!-- Tour Overview -->
                <div class="readscope-block-section">
                    <h2 class="section-heading"><?php esc_html_e('Tour Overview & Description', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>

                <!-- Itinerary Timeline -->
                <?php 
                $readscopeItinerary = $readscopeTour->getItinerary();
                if (!empty($readscopeItinerary)) : 
                ?>
                    <div class="readscope-block-section">
                        <h2 class="section-heading"><?php esc_html_e('Day by Day Itinerary', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <div class="readscope-itinerary-timeline">
                            <?php foreach ($readscopeItinerary as $readscopeStep) : ?>
                                <div class="timeline-step">
                                    <div class="step-badge"><?php echo esc_html($readscopeStep['day'] ?? '1'); ?></div>
                                    <div class="step-content">
                                        <h3 class="step-title"><?php echo esc_html($readscopeStep['title'] ?? ''); ?></h3>
                                        <?php if (!empty($readscopeStep['meals'])) : ?>
                                            <span class="step-meals"><span class="dashicons dashicons-food"></span> <strong><?php esc_html_e('Meals:', 'the-readscope-tour-and-accommodation-booking'); ?></strong> <?php echo esc_html($readscopeStep['meals']); ?></span>
                                        <?php endif; ?>
                                        <p class="step-desc"><?php echo nl2br(esc_html($readscopeStep['desc'] ?? '')); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Inclusions & Exclusions -->
                <?php 
                $readscopeInclusions = $readscopeTour->getInclusions();
                $readscopeExclusions = $readscopeTour->getExclusions();
                if (!empty($readscopeInclusions) || !empty($readscopeExclusions)) : 
                ?>
                    <div class="readscope-block-section">
                        <h2 class="section-heading"><?php esc_html_e("What's Included & Excluded", 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <div class="readscope-inc-exc-grid">
                            <?php if (!empty($readscopeInclusions)) : ?>
                                <div class="inc-box">
                                    <h3><span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span> <?php esc_html_e('Included', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                    <ul>
                                        <?php foreach ($readscopeInclusions as $readscopeInc) : ?>
                                            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html($readscopeInc); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($readscopeExclusions)) : ?>
                                <div class="exc-box">
                                    <h3><span class="dashicons dashicons-dismiss" style="color:#ef4444;"></span> <?php esc_html_e('Excluded', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
                                    <ul>
                                        <?php foreach ($readscopeExclusions as $readscopeExc) : ?>
                                            <li><span class="dashicons dashicons-no-alt"></span> <?php echo esc_html($readscopeExc); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Meeting & Pickup Locations -->
                <?php if ($readscopeTour->getPickupLocation() || $readscopeTour->getDropoffLocation()) : ?>
                    <div class="readscope-block-section">
                        <h2 class="section-heading"><?php esc_html_e('Meeting & Logistics', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <div class="logistics-box">
                            <?php if ($readscopeTour->getPickupLocation()) : ?>
                                <div class="logistics-item">
                                    <strong><span class="dashicons dashicons-location-alt"></span> <?php esc_html_e('Pickup / Meeting Point:', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    <p><?php echo esc_html($readscopeTour->getPickupLocation()); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($readscopeTour->getDropoffLocation()) : ?>
                                <div class="logistics-item">
                                    <strong><span class="dashicons dashicons-flag"></span> <?php esc_html_e('Drop-off Point:', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                                    <p><?php echo esc_html($readscopeTour->getDropoffLocation()); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- FAQs -->
                <?php 
                $readscopeFaqs = $readscopeTour->getFaqs();
                if (!empty($readscopeFaqs)) : 
                ?>
                    <div class="readscope-block-section">
                        <h2 class="section-heading"><?php esc_html_e('Frequently Asked Questions', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                        <div class="readscope-faqs-accordion">
                            <?php foreach ($readscopeFaqs as $readscopeFaq) : ?>
                                <details class="faq-item">
                                    <summary class="faq-question"><?php echo esc_html($readscopeFaq['question'] ?? ''); ?></summary>
                                    <div class="faq-answer"><?php echo nl2br(esc_html($readscopeFaq['answer'] ?? '')); ?></div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reviews & Star Ratings Section -->
                <?php 
                readscope_get_template('reviews-section.php', ['readscopePostId' => $readscopeTourId]); 
                ?>
            </div>

            <!-- Right Column: Sticky Booking Panel -->
            <div class="readscope-sidebar">
                <div class="readscope-sticky-box">
                    <?php 
                    readscope_get_template('booking-panel.php', [
                        'readscopeItemId'   => $readscopeTourId,
                        'readscopeItemType' => 'tour',
                    ]); 
                    ?>
                </div>
            </div>
        </div>
        <?php readscope_related_place(); ?>
    </div>
</div>

<?php readscope_template_footer();
