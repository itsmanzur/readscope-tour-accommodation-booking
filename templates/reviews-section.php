<?php
/**
 * Readscope Booking Reviews & Star Rating Section Template
 *
 * @package Readscope Booking
 * @var int $postId
 */

if (!defined('ABSPATH')) {
    exit;
}

$readscopePostId = isset($readscopePostId) ? (int) $readscopePostId : (isset($postId) ? (int) $postId : (int) get_the_ID());
if (!$readscopePostId) {
    return;
}

$readscopeReviewService = new \Readscope\Services\ReviewService();
$readscopeBreakdown     = $readscopeReviewService->getRatingBreakdown($readscopePostId);
$readscopeComments      = get_comments([
    'post_id' => $readscopePostId,
    'status'  => 'approve',
    'type'    => 'comment',
    'order'   => 'DESC',
]);
?>

<div class="readscope-block-section readscope-reviews-block" id="readscope-reviews">
    <h2 class="section-heading">
        ⭐ <?php esc_html_e('Traveler Reviews & Ratings', 'the-readscope-tour-and-accommodation-booking'); ?>
        <?php if ($readscopeBreakdown['total'] > 0) : ?>
            <span class="reviews-total-badge">(<?php echo esc_html((string) $readscopeBreakdown['total']); ?>)</span>
        <?php endif; ?>
    </h2>

    <!-- Rating Summary Header -->
    <div class="readscope-rating-overview">
        <div class="rating-score-box">
            <span class="score-number"><?php echo esc_html(number_format($readscopeBreakdown['average'], 1)); ?></span>
            <div class="score-stars">
                <?php
                $readscopeFilledStars = (int) round($readscopeBreakdown['average']);
                for ($readscopeStarIdx = 1; $readscopeStarIdx <= 5; $readscopeStarIdx++) {
                    echo $readscopeStarIdx <= $readscopeFilledStars ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
                }
                ?>
            </div>
            <span class="score-subtext">
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: %d: Number of reviews */
                        _n('Based on %d review', 'Based on %d reviews', $readscopeBreakdown['total'], 'the-readscope-tour-and-accommodation-booking'),
                        $readscopeBreakdown['total']
                    )
                );
                ?>
            </span>
        </div>

        <div class="rating-bars-box">
            <?php for ($readscopeStarRating = 5; $readscopeStarRating >= 1; $readscopeStarRating--) : 
                $readscopePct   = $readscopeBreakdown['percentages'][$readscopeStarRating] ?? 0;
                $readscopeCount = $readscopeBreakdown['stars'][$readscopeStarRating] ?? 0;
            ?>
                <div class="rating-bar-row">
                    <span class="bar-star-label"><?php echo esc_html((string) $readscopeStarRating); ?> ★</span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?php echo esc_attr((string) $readscopePct); ?>%;"></div>
                    </div>
                    <span class="bar-count-label"><?php echo esc_html((string) $readscopeCount); ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="readscope-reviews-list">
        <?php if (!empty($readscopeComments)) : ?>
            <?php foreach ($readscopeComments as $readscopeComment) : 
                $readscopeCommentRating   = (int) get_comment_meta((int) $readscopeComment->comment_ID, \Readscope\Services\ReviewService::META_RATING, true);
                $readscopeIsVerifiedUser = $readscopeReviewService->isVerifiedTraveler($readscopeComment->comment_author_email, $readscopePostId);
            ?>
                <div class="readscope-review-card" id="comment-<?php echo esc_attr((string) $readscopeComment->comment_ID); ?>">
                    <div class="review-header">
                        <div class="review-author-avatar">
                            <?php echo get_avatar($readscopeComment, 48); ?>
                        </div>
                        <div class="review-author-info">
                            <h4 class="author-name">
                                <?php echo esc_html($readscopeComment->comment_author); ?>
                                <?php if ($readscopeIsVerifiedUser) : ?>
                                    <span class="verified-badge" title="<?php esc_attr_e('Verified Booking Traveler', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                        ✓ <?php esc_html_e('Verified Traveler', 'the-readscope-tour-and-accommodation-booking'); ?>
                                    </span>
                                <?php endif; ?>
                            </h4>
                            <span class="review-date"><?php echo esc_html(get_comment_date('F j, Y', $readscopeComment)); ?></span>
                        </div>
                        <?php if ($readscopeCommentRating > 0) : ?>
                            <div class="review-stars">
                                <?php 
                                for ($readscopeRatingIdx = 1; $readscopeRatingIdx <= 5; $readscopeRatingIdx++) {
                                    echo $readscopeRatingIdx <= $readscopeCommentRating ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="review-content">
                        <?php echo wp_kses_post(wpautop($readscopeComment->comment_content)); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="no-reviews-msg" style="color: #64748b; padding: 20px 0;">
                <?php esc_html_e('No reviews yet. Be the first to share your travel experience!', 'the-readscope-tour-and-accommodation-booking'); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Leave a Review Form -->
    <div class="readscope-leave-review-form-wrap">
        <h3>✍️ <?php esc_html_e('Write a Review', 'the-readscope-tour-and-accommodation-booking'); ?></h3>
        
        <?php if (comments_open($readscopePostId)) : ?>
            <form action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" class="readscope-review-form">
                <!-- Interactive Star Selector -->
                <div class="form-row rating-picker-row">
                    <label><strong><?php esc_html_e('Your Rating *', 'the-readscope-tour-and-accommodation-booking'); ?></strong></label>
                    <div class="readscope-star-picker" id="readscope-star-picker">
                        <span class="picker-star" data-val="1">★</span>
                        <span class="picker-star" data-val="2">★</span>
                        <span class="picker-star" data-val="3">★</span>
                        <span class="picker-star" data-val="4">★</span>
                        <span class="picker-star" data-val="5">★</span>
                    </div>
                    <input type="hidden" name="readscope_rating" id="readscope_rating_input" value="5" required>
                </div>

                <div class="form-row-group" style="display: flex; gap: 14px;">
                    <div class="form-row" style="flex: 1;">
                        <label for="author"><?php esc_html_e('Your Name *', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="text" name="author" id="author" required value="<?php echo esc_attr(wp_get_current_user()->display_name ?? ''); ?>">
                    </div>
                    <div class="form-row" style="flex: 1;">
                        <label for="email"><?php esc_html_e('Your Email *', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                        <input type="email" name="email" id="email" required value="<?php echo esc_attr(wp_get_current_user()->user_email ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row" style="margin-top: 12px;">
                    <label for="comment"><?php esc_html_e('Your Review / Feedback *', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <textarea name="comment" id="comment" rows="4" required placeholder="<?php esc_attr_e('What did you love about this trip? How was the guide, accommodation, and itinerary?', 'the-readscope-tour-and-accommodation-booking'); ?>"></textarea>
                </div>

                <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr((string) $readscopePostId); ?>">
                <input type="hidden" name="comment_parent" id="comment_parent" value="0">

                <div class="form-row form-submit-row" style="margin-top: 14px;">
                    <button type="submit" class="readscope-btn readscope-btn-primary">
                        🚀 <?php esc_html_e('Submit Review', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </button>
                </div>
            </form>
        <?php else : ?>
            <p style="color: #64748b;"><?php esc_html_e('Comments and reviews are closed for this trip.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
        <?php endif; ?>
    </div>
</div>
