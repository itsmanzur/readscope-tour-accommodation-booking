<?php
/**
 * Readscope Booking Base Email Template Layout
 *
 * @package Readscope Booking
 * @var string $emailHeading
 * @var string $emailBodyContent
 * @var string $siteName
 * @var string $primaryColor
 * @var bool   $isTest
 */

if (!defined('ABSPATH')) {
    exit;
}

$readscopeSiteName     = isset($siteName) && is_string($siteName) ? $siteName : get_bloginfo('name');
$readscopePrimaryColor = isset($primaryColor) && is_string($primaryColor) ? $primaryColor : '#0d9488';
$readscopeHeading      = isset($emailHeading) && is_string($emailHeading) ? $emailHeading : '';
$readscopeIsTest       = !empty($isTest);
$readscopeLang         = 'en';
if (function_exists('readscope_current_locale')) {
    $readscopeLocale = readscope_current_locale();
    $readscopeLang = str_starts_with($readscopeLocale, 'bn') ? 'bn' : (substr($readscopeLocale, 0, 2) ?: 'en');
}
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr($readscopeLang); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($readscopeHeading ?: $readscopeSiteName); ?></title>
    <style>
        body {
            /* Bengali faces come before the generic family: many mail apps stop at the first generic name. */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, 'Noto Sans Bengali', 'Nirmala UI', 'Kohinoor Bangla', 'Vrinda', 'SolaimanLipi', sans-serif;
            line-height: 1.6;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background-color: #f1f5f9;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        .readscope-email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .readscope-email-header {
            background-color: <?php echo esc_attr($readscopePrimaryColor); ?>;
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .readscope-email-header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }
        .readscope-email-header p {
            margin: 6px 0 0 0;
            opacity: 0.92;
            font-size: 14px;
            color: #ffffff;
        }
        .readscope-email-content {
            padding: 28px 24px;
        }
        .readscope-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 18px;
            margin: 20px 0;
        }
        .readscope-info-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .readscope-info-row:last-child {
            border-bottom: none;
        }
        .readscope-info-label {
            color: #64748b;
            font-weight: 600;
        }
        .readscope-info-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .readscope-btn {
            display: inline-block;
            background-color: <?php echo esc_attr($readscopePrimaryColor); ?>;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 16px;
            text-align: center;
        }
        .readscope-test-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .readscope-email-footer {
            text-align: center;
            font-size: 13px;
            color: #64748b;
            padding: 20px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .readscope-email-footer a {
            color: <?php echo esc_attr($readscopePrimaryColor); ?>;
            text-decoration: none;
        }
        .readscope-highlight-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 16px 0;
            color: #166534;
            font-size: 14px;
        }
        .readscope-warning-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 16px 0;
            color: #92400e;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="readscope-email-wrapper">
        <div class="readscope-email-header">
            <?php if ($readscopeIsTest) : ?>
                <div><span class="readscope-test-badge"><?php esc_html_e('TEST NOTIFICATION', 'the-readscope-tour-and-accommodation-booking'); ?></span></div>
            <?php endif; ?>
            <h1><?php echo esc_html($readscopeHeading); ?></h1>
            <p><?php echo esc_html($readscopeSiteName); ?></p>
        </div>

        <div class="readscope-email-content">
            <?php
            // Escape late: the body is filtered here, at output, with an allowlist made for HTML email.
            // The extra inline CSS properties are allowed only for this one wp_kses() call.
            add_filter('safe_style_css', [\Readscope\Support\Html::class, 'emailSafeCss']);
            echo wp_kses((string) $emailBodyContent, \Readscope\Support\Html::emailAllowedHtml());
            remove_filter('safe_style_css', [\Readscope\Support\Html::class, 'emailSafeCss']);
            ?>
        </div>

        <div class="readscope-email-footer">
            <p style="margin: 0 0 6px 0;">&copy; <?php echo esc_html(gmdate('Y')); ?> <strong><?php echo esc_html($readscopeSiteName); ?></strong>. <?php esc_html_e('All rights reserved.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
            <p style="margin: 0;"><a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_url(home_url('/')); ?></a></p>
        </div>
    </div>
</body>
</html>
