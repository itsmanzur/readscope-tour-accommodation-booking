<?php
/**
 * Admin: New Traveler Trip Inquiry Email Template Part
 *
 * @package Readscope Booking
 * @var array  $inquiry
 * @var string $siteName
 * @var string $additionalContent
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included inside a function; these variables are local.

$readscopeInquiry     = isset($inquiry) && is_array($inquiry) ? $inquiry : [];
$readscopeExtra       = isset($additionalContent) && is_string($additionalContent) ? $additionalContent : '';
$adminInquiriesUrl  = admin_url('admin.php?page=readscope-inquiries');
$readscopeDate = static function (string $value): string {
    if ($value === '') {
        return '';
    }

    return function_exists('readscope_format_date') ? readscope_format_date($value) : $value;
};
?>
<p style="font-size: 15px; margin-top: 0;">
    <strong><?php esc_html_e('Hello Administrator,', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
</p>
<p style="color: #475569; font-size: 14px;">
    <?php esc_html_e('You have received a new traveler trip inquiry submitted from your website:', 'the-readscope-tour-and-accommodation-booking'); ?>
</p>

<div class="readscope-info-card">
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Traveler Name:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value"><?php echo esc_html($readscopeInquiry['name'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></span>
    </div>
    <div class="readscope-info-row">
        <span class="readscope-info-label"><?php esc_html_e('Email Address:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
        <span class="readscope-info-value"><a href="mailto:<?php echo esc_attr($readscopeInquiry['email'] ?? ''); ?>"><?php echo esc_html($readscopeInquiry['email'] ?? __('N/A', 'the-readscope-tour-and-accommodation-booking')); ?></a></span>
    </div>
    <?php if (!empty($readscopeInquiry['phone'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Phone / WhatsApp:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeInquiry['phone']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeInquiry['item_title'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Trip / Package:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeInquiry['item_title']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeInquiry['travel_date'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Expected Travel Date:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html($readscopeDate((string) $readscopeInquiry['travel_date'])); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeInquiry['guests'])) : ?>
        <div class="readscope-info-row">
            <span class="readscope-info-label"><?php esc_html_e('Number of Travelers:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <span class="readscope-info-value"><?php echo esc_html((string)$readscopeInquiry['guests']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($readscopeInquiry['message'])) : ?>
        <div style="padding-top: 10px; margin-top: 8px; border-top: 1px solid #e2e8f0;">
            <span class="readscope-info-label" style="display: block; margin-bottom: 4px;"><?php esc_html_e('Inquiry / Question:', 'the-readscope-tour-and-accommodation-booking'); ?></span>
            <div style="color: #334155; font-size: 14px; line-height: 1.5; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px;">
                <?php echo nl2br(esc_html($readscopeInquiry['message'])); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($readscopeExtra)) : ?>
    <div style="margin: 20px 0; font-size: 14px; color: #334155;">
        <?php echo wp_kses_post($readscopeExtra); ?>
    </div>
<?php endif; ?>

<div style="text-align: center; margin-top: 24px;">
    <a href="<?php echo esc_url($adminInquiriesUrl); ?>" class="readscope-btn">
        💬 <?php esc_html_e('View & Reply in Inquiries Manager', 'the-readscope-tour-and-accommodation-booking'); ?>
    </a>
</div>
