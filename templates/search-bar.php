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

if (function_exists('readscope_load_plugin_textdomain')) {
    readscope_load_plugin_textdomain();
}

$readscopeDestinations = get_terms([
    'taxonomy'   => 'readscope_destination',
    'hide_empty' => false,
]);

$readscopeSelectedDest = '';
$readscopeQueriedPlace = get_queried_object();
if ($readscopeQueriedPlace instanceof \WP_Term && $readscopeQueriedPlace->taxonomy === 'readscope_destination') {
    $readscopeSelectedDest = $readscopeQueriedPlace->slug;
} elseif (\Readscope\Support\PublicSearch::destination() !== '') {
    $readscopeSelectedDest = \Readscope\Support\PublicSearch::destination();
} elseif (
    $readscopeQueriedPlace instanceof \WP_Post
    && in_array($readscopeQueriedPlace->post_type, ['readscope_tour', 'readscope_hotel'], true)
) {
    $readscopePlaceTerms = get_the_terms($readscopeQueriedPlace, 'readscope_destination');
    if (is_array($readscopePlaceTerms) && isset($readscopePlaceTerms[0]) && $readscopePlaceTerms[0] instanceof \WP_Term) {
        $readscopeSelectedDest = $readscopePlaceTerms[0]->slug;
    }
}
$readscopeSelectedDate   = \Readscope\Support\PublicSearch::date();
$readscopeSelectedGuests = \Readscope\Support\PublicSearch::guests(2);
$readscopeBanglaDigits = (function_exists('readscope_is_bengali') && readscope_is_bengali())
    || (bool) \Readscope\Config\Config::get('use_bangla_digits', false);

$readscopeDateLabel = __('Any day', 'the-readscope-tour-and-accommodation-booking');
if ($readscopeSelectedDate !== '') {
    $readscopeStamp = strtotime($readscopeSelectedDate . ' 12:00:00') ?: 0;
    $readscopeMonths = [
        __('January', 'the-readscope-tour-and-accommodation-booking'), __('February', 'the-readscope-tour-and-accommodation-booking'), __('March', 'the-readscope-tour-and-accommodation-booking'),
        __('April', 'the-readscope-tour-and-accommodation-booking'), __('May', 'the-readscope-tour-and-accommodation-booking'), __('June', 'the-readscope-tour-and-accommodation-booking'),
        __('July', 'the-readscope-tour-and-accommodation-booking'), __('August', 'the-readscope-tour-and-accommodation-booking'), __('September', 'the-readscope-tour-and-accommodation-booking'),
        __('October', 'the-readscope-tour-and-accommodation-booking'), __('November', 'the-readscope-tour-and-accommodation-booking'), __('December', 'the-readscope-tour-and-accommodation-booking'),
    ];
    if ($readscopeStamp > 0) {
        $readscopeDateLabel = (int) wp_date('j', $readscopeStamp) . ' ' . ($readscopeMonths[(int) wp_date('n', $readscopeStamp) - 1] ?? '');
        if ($readscopeBanglaDigits && function_exists('readscope_bn_number')) {
            $readscopeDateLabel = readscope_bn_number($readscopeDateLabel);
        }
    }
}
?>

<div class="readscope-search-bar-wrap">
    <form class="readscope-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <div class="search-field destination-field">
            <label><span class="dashicons dashicons-location"></span> <?php esc_html_e('Where to?', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <select name="readscope_destination">
                <option value=""><?php esc_html_e('All Destinations', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                <?php if (!empty($readscopeDestinations) && !is_wp_error($readscopeDestinations)) : foreach ($readscopeDestinations as $readscopeDest) : ?>
                    <option value="<?php echo esc_attr($readscopeDest->slug); ?>" <?php selected($readscopeSelectedDest, $readscopeDest->slug); ?>><?php echo esc_html($readscopeDest->name); ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="search-field date-field readscope-search-date">
            <label><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e('When?', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <button type="button" class="readscope-search-date-btn" aria-expanded="false" aria-haspopup="dialog">
                <span class="readscope-search-date-label"><?php echo esc_html($readscopeDateLabel); ?></span>
            </button>
            <input type="date" class="readscope-search-native" name="readscope_date" value="<?php echo esc_attr($readscopeSelectedDate); ?>" min="<?php echo esc_attr(wp_date('Y-m-d')); ?>">
            <div class="readscope-search-cal" hidden data-today="<?php echo esc_attr(wp_date('Y-m-d')); ?>">
                <div class="readscope-cal" role="dialog" aria-label="<?php esc_attr_e('When?', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    <div class="readscope-cal-nav">
                        <button type="button" class="readscope-cal-prev" aria-label="<?php esc_attr_e('Previous month', 'the-readscope-tour-and-accommodation-booking'); ?>">&lsaquo;</button>
                        <strong class="readscope-cal-title"></strong>
                        <button type="button" class="readscope-cal-next" aria-label="<?php esc_attr_e('Next month', 'the-readscope-tour-and-accommodation-booking'); ?>">&rsaquo;</button>
                    </div>
                    <div class="readscope-cal-weekdays"></div>
                    <div class="readscope-cal-grid"></div>
                    <p class="readscope-search-cal-hint"><?php esc_html_e('Each date shows open tours, then open hotels.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                </div>
            </div>
        </div>

        <div class="search-field guests-field">
            <label><span class="dashicons dashicons-groups"></span> <?php esc_html_e('Travelers', 'the-readscope-tour-and-accommodation-booking'); ?></label>
            <div class="readscope-guest-stepper">
                <button type="button" class="readscope-guest-minus" aria-label="<?php esc_attr_e('Fewer travelers', 'the-readscope-tour-and-accommodation-booking'); ?>">−</button>
                <?php if ($readscopeBanglaDigits) : ?>
                    <input type="text" class="readscope-guest-digits" inputmode="numeric" autocomplete="off" value="<?php echo esc_attr(function_exists('readscope_bn_number') ? readscope_bn_number((string) $readscopeSelectedGuests) : (string) $readscopeSelectedGuests); ?>">
                <?php else : ?>
                    <input type="number" name="readscope_guests" min="1" max="50" value="<?php echo esc_attr((string) $readscopeSelectedGuests); ?>">
                <?php endif; ?>
                <button type="button" class="readscope-guest-plus" aria-label="<?php esc_attr_e('More travelers', 'the-readscope-tour-and-accommodation-booking'); ?>">+</button>
            </div>
            <?php if ($readscopeBanglaDigits) : ?>
                <input type="hidden" name="readscope_guests" value="<?php echo esc_attr((string) $readscopeSelectedGuests); ?>">
            <?php endif; ?>
        </div>

        <input type="hidden" name="post_type" value="readscope_tour">

        <div class="search-btn-field">
            <button type="submit" class="readscope-btn readscope-btn-search">
                <span class="dashicons dashicons-search"></span> <?php esc_html_e('Search', 'the-readscope-tour-and-accommodation-booking'); ?>
            </button>
        </div>
    </form>
</div>
