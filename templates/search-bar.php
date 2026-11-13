<?php
/**
 * Readscope Search Bar Template
 *
 * @package Readscope Booking
 * @var array<\WP_Term> $destinations
 */

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('tourivo_load_plugin_textdomain')) {
    tourivo_load_plugin_textdomain();
}

$tourivoDestinations = get_terms([
    'taxonomy'   => 'tourivo_destination',
    'hide_empty' => false,
]);

$tourivoSelectedDest = '';
$tourivoQueriedPlace = get_queried_object();
if ($tourivoQueriedPlace instanceof \WP_Term && $tourivoQueriedPlace->taxonomy === 'tourivo_destination') {
    $tourivoSelectedDest = $tourivoQueriedPlace->slug;
} elseif (\Tourivo\Support\PublicSearch::destination() !== '') {
    $tourivoSelectedDest = \Tourivo\Support\PublicSearch::destination();
} elseif (
    $tourivoQueriedPlace instanceof \WP_Post
    && in_array($tourivoQueriedPlace->post_type, ['tourivo_tour', 'tourivo_hotel'], true)
) {
    $tourivoPlaceTerms = get_the_terms($tourivoQueriedPlace, 'tourivo_destination');
    if (is_array($tourivoPlaceTerms) && isset($tourivoPlaceTerms[0]) && $tourivoPlaceTerms[0] instanceof \WP_Term) {
        $tourivoSelectedDest = $tourivoPlaceTerms[0]->slug;
    }
}
$tourivoSelectedDate   = \Tourivo\Support\PublicSearch::date();
$tourivoSelectedGuests = \Tourivo\Support\PublicSearch::guests(2);
$tourivoBanglaDigits = (function_exists('tourivo_is_bengali') && tourivo_is_bengali())
    || (bool) \Tourivo\Config\Config::get('use_bangla_digits', false);

$tourivoDateLabel = __('Any day', 'the-readscope-tour-and-accommodation-booking');
if ($tourivoSelectedDate !== '') {
    $tourivoStamp = strtotime($tourivoSelectedDate . ' 12:00:00') ?: 0;
    $tourivoMonths = [
        __('January', 'the-readscope-tour-and-accommodation-booking'), __('February', 'the-readscope-tour-and-accommodation-booking'), __('March', 'the-readscope-tour-and-accommodation-booking'),
        __('April', 'the-readscope-tour-and-accommodation-booking'), __('May', 'the-readscope-tour-and-accommodation-booking'), __('June', 'the-readscope-tour-and-accommodation-booking'),
        __('July', 'the-readscope-tour-and-accommodation-booking'), __('August', 'the-readscope-tour-and-accommodation-booking'), __('September', 'the-readscope-tour-and-accommodation-booking'),
        __('October', 'the-readscope-tour-and-accommodation-booking'), __('November', 'the-readscope-tour-and-accommodation-booking'), __('December', 'the-readscope-tour-and-accommodation-booking'),
    ];
    if ($tourivoStamp > 0) {
        $tourivoDateLabel = (int) wp_date('j', $tourivoStamp) . ' ' . ($tourivoMonths[(int) wp_date('n', $tourivoStamp) - 1] ?? '');
        if ($tourivoBanglaDigits && function_exists('tourivo_bn_number')) {
            $tourivoDateLabel = tourivo_bn_number($tourivoDateLabel);
        }
    }
}
?>

<div class="tourivo-search-bar-wrap">
    <form class="tourivo-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <div class="search-field destination-field">
            <label><span class="dashicons dashicons-location"></span> <?php esc_html_e('Where to?', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <select name="tourivo_destination">
                <option value=""><?php esc_html_e('All Destinations', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                <?php if (!empty($tourivoDestinations) && !is_wp_error($tourivoDestinations)) : foreach ($tourivoDestinations as $tourivoDest) : ?>
                    <option value="<?php echo esc_attr($tourivoDest->slug); ?>" <?php selected($tourivoSelectedDest, $tourivoDest->slug); ?>><?php echo esc_html($tourivoDest->name); ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="search-field date-field tourivo-search-date">
            <label><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e('When?', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <button type="button" class="tourivo-search-date-btn" aria-expanded="false" aria-haspopup="dialog">
                <span class="tourivo-search-date-label"><?php echo esc_html($tourivoDateLabel); ?></span>
            </button>
            <input type="date" class="tourivo-search-native" name="tourivo_date" value="<?php echo esc_attr($tourivoSelectedDate); ?>" min="<?php echo esc_attr(wp_date('Y-m-d')); ?>">
            <div class="tourivo-search-cal" hidden data-today="<?php echo esc_attr(wp_date('Y-m-d')); ?>">
                <div class="tourivo-cal" role="dialog" aria-label="<?php esc_attr_e('When?', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    <div class="tourivo-cal-nav">
                        <button type="button" class="tourivo-cal-prev" aria-label="<?php esc_attr_e('Previous month', 'the-readscope-tour-and-accommodation-booking'); ?>">&lsaquo;</button>
                        <strong class="tourivo-cal-title"></strong>
                        <button type="button" class="tourivo-cal-next" aria-label="<?php esc_attr_e('Next month', 'the-readscope-tour-and-accommodation-booking'); ?>">&rsaquo;</button>
                    </div>
                    <div class="tourivo-cal-weekdays"></div>
                    <div class="tourivo-cal-grid"></div>
                    <p class="tourivo-search-cal-hint"><?php esc_html_e('Each date shows open tours, then open hotels.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                </div>
            </div>
        </div>

        <div class="search-field guests-field">
            <label><span class="dashicons dashicons-groups"></span> <?php esc_html_e('Travelers', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <div class="tourivo-guest-stepper">
                <button type="button" class="tourivo-guest-minus" aria-label="<?php esc_attr_e('Fewer travelers', 'the-readscope-tour-and-accommodation-booking'); ?>">−</button>
                <?php if ($tourivoBanglaDigits) : ?>
                    <input type="text" class="tourivo-guest-digits" inputmode="numeric" autocomplete="off" value="<?php echo esc_attr(function_exists('tourivo_bn_number') ? tourivo_bn_number((string) $tourivoSelectedGuests) : (string) $tourivoSelectedGuests); ?>">
                <?php else : ?>
                    <input type="number" name="tourivo_guests" min="1" max="50" value="<?php echo esc_attr((string) $tourivoSelectedGuests); ?>">
                <?php endif; ?>
                <button type="button" class="tourivo-guest-plus" aria-label="<?php esc_attr_e('More travelers', 'the-readscope-tour-and-accommodation-booking'); ?>">+</button>
            </div>
            <?php if ($tourivoBanglaDigits) : ?>
                <input type="hidden" name="tourivo_guests" value="<?php echo esc_attr((string) $tourivoSelectedGuests); ?>">
            <?php endif; ?>
        </div>

        <input type="hidden" name="post_type" value="tourivo_tour">

        <div class="search-btn-field">
            <button type="submit" class="tourivo-btn tourivo-btn-search">
                <span class="dashicons dashicons-search"></span> <?php esc_html_e('Search', 'the-readscope-tour-and-accommodation-booking'); ?>
            </button>
        </div>
    </form>
</div>
