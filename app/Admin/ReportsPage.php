<?php

declare(strict_types=1);

namespace Readscope\Admin;

use Readscope\Common\Container;
use Readscope\Services\ReportingService;
use Readscope\Support\Money;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ReportsPage
 *
 * Renders the Admin Business Analytics, Sales, Tax, Occupancy, and Performance Reports hub.
 *
 * @package Readscope\Admin
 */
class ReportsPage
{
    /**
     * Render the admin reports and analytics page.
     *
     * @return void
     */
    public static function render(): void
    {
        if (!current_user_can('manage_readscope_bookings')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'the-readscope-tour-and-accommodation-booking'), 403);
        }

        /** @var ReportingService $reporting */
        $reporting = Container::getInstance()->get(ReportingService::class);

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only report filters; current_user_can() is checked at the top of render().
        $period    = isset($_GET['period']) ? sanitize_text_field(wp_unslash($_GET['period'])) : 'this_month';
        $startDate = isset($_GET['start_date']) ? sanitize_text_field(wp_unslash($_GET['start_date'])) : null;
        $endDate   = isset($_GET['end_date']) ? sanitize_text_field(wp_unslash($_GET['end_date'])) : null;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $range    = $reporting->resolveDateRange($period, $startDate, $endDate);
        $metrics  = $reporting->getKpiMetrics($range['start'], $range['end']);
        $occupancy= $reporting->getOccupancyRate($range['start'], $range['end']);
        $topItems = $reporting->getTopPerformingItems($range['start'], $range['end'], 5);
        $timeline = $reporting->getTimelineData($range['start'], $range['end']);

        $currency = (string) apply_filters('readscope/currency_symbol', '$');
        $adminUrl = admin_url('admin.php?page=readscope-reports');

        $presets = [
            'today'       => __('Today', 'the-readscope-tour-and-accommodation-booking'),
            'last_7_days' => __('Last 7 Days', 'the-readscope-tour-and-accommodation-booking'),
            'this_month'  => __('This Month', 'the-readscope-tour-and-accommodation-booking'),
            'last_month'  => __('Last Month', 'the-readscope-tour-and-accommodation-booking'),
            'this_year'   => __('This Year', 'the-readscope-tour-and-accommodation-booking'),
        ];
        ?>
        <div class="wrap readscope-admin-wrap readscope-reports-wrap">
            <!-- Header & Filter Bar -->
            <div class="readscope-page-header readscope-reports-header">
                <div class="readscope-page-header__text">
                    <h1><?php esc_html_e('Analytics & Business Reports', 'the-readscope-tour-and-accommodation-booking'); ?></h1>
                    <p class="readscope-subtitle">
                        <?php
                        printf(
                            /* translators: 1: Filter date range label */
                            esc_html__('Monitoring sales, occupancy rates, and tax filings for: %s', 'the-readscope-tour-and-accommodation-booking'),
                            '<strong>' . esc_html($range['label']) . '</strong>'
                        );
                        ?>
                    </p>
                </div>
                <div class="header-actions">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="readscope-inline-form">
                        <input type="hidden" name="action" value="readscope_export_accounting_csv">
                        <input type="hidden" name="period" value="<?php echo esc_attr($range['period']); ?>">
                        <input type="hidden" name="start_date" value="<?php echo esc_attr(substr($range['start'], 0, 10)); ?>">
                        <input type="hidden" name="end_date" value="<?php echo esc_attr(substr($range['end'], 0, 10)); ?>">
                        <?php wp_nonce_field('readscope_reports_action', 'readscope_reports_nonce'); ?>
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-media-spreadsheet" aria-hidden="true"></span>
                            <?php esc_html_e('Export Accounting & Tax CSV', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </button>
                    </form>
                </div>
            </div>
            <hr class="wp-header-end">

            <!-- Date Range Filter Toolbar -->
            <div class="readscope-card-box readscope-reports-filter-box">
                <div class="readscope-filter-row">
                    <div class="readscope-preset-pills">
                        <?php foreach ($presets as $pKey => $pLabel) : ?>
                            <a href="<?php echo esc_url(add_query_arg('period', $pKey, $adminUrl)); ?>"
                               class="readscope-preset-pill <?php echo ($range['period'] === $pKey) ? 'active' : ''; ?>">
                                <?php echo esc_html($pLabel); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="readscope-custom-date-form">
                        <input type="hidden" name="page" value="readscope-reports">
                        <input type="hidden" name="period" value="custom">
                        <div class="date-inputs-wrap">
                            <label for="reports-start-date"><?php esc_html_e('From:', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                            <input type="date" id="reports-start-date" name="start_date" value="<?php echo esc_attr(substr($range['start'], 0, 10)); ?>" required>
                            <label for="reports-end-date"><?php esc_html_e('To:', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                            <input type="date" id="reports-end-date" name="end_date" value="<?php echo esc_attr(substr($range['end'], 0, 10)); ?>" required>
                            <button type="submit" class="button button-secondary"><?php esc_html_e('Filter', 'the-readscope-tour-and-accommodation-booking'); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary KPI Grid -->
            <div class="readscope-kpi-grid readscope-reports-kpi-grid">
                <!-- Total Bookings -->
                <div class="kpi-card">
                    <div class="kpi-icon icon-bookings"><span class="dashicons dashicons-calendar-alt"></span></div>
                    <div class="kpi-info">
                        <span class="kpi-title"><?php esc_html_e('Total Bookings', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span class="kpi-number"><?php echo esc_html((string) $metrics['total_bookings']); ?></span>
                        <span class="kpi-subtext">
                            <?php
                            printf(
                                /* translators: 1: Confirmed bookings count, 2: Pending count */
                                esc_html__('%1$d confirmed &bull; %2$d pending', 'the-readscope-tour-and-accommodation-booking'),
                                (int) $metrics['confirmed_bookings'],
                                (int) $metrics['pending_bookings']
                            );
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Gross Revenue -->
                <div class="kpi-card">
                    <div class="kpi-icon icon-revenue"><span class="dashicons dashicons-chart-line"></span></div>
                    <div class="kpi-info">
                        <span class="kpi-title"><?php esc_html_e('Gross Revenue', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span class="kpi-number"><?php echo esc_html(Money::format($metrics['gross_revenue'])); ?></span>
                        <span class="kpi-subtext">
                            <?php
                            printf(
                                /* translators: 1: Net Revenue amount */
                                esc_html__('Net: %s', 'the-readscope-tour-and-accommodation-booking'),
                                esc_html(Money::format($metrics['net_revenue']))
                            );
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Tax Collection -->
                <div class="kpi-card">
                    <div class="kpi-icon icon-tax"><span class="dashicons dashicons-vault"></span></div>
                    <div class="kpi-info">
                        <span class="kpi-title"><?php esc_html_e('Tax Collected', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span class="kpi-number"><?php echo esc_html(Money::format($metrics['total_tax'])); ?></span>
                        <span class="kpi-subtext">
                            <?php
                            printf(
                                /* translators: 1: Discounts applied */
                                esc_html__('Discounts: %s', 'the-readscope-tour-and-accommodation-booking'),
                                esc_html(Money::format($metrics['total_discount']))
                            );
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Occupancy Rate -->
                <div class="kpi-card">
                    <div class="kpi-icon icon-occupancy"><span class="dashicons dashicons-building"></span></div>
                    <div class="kpi-info">
                        <span class="kpi-title"><?php esc_html_e('Occupancy Rate', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span class="kpi-number"><?php echo esc_html(number_format($occupancy['rate'], 1)); ?>%</span>
                        <div class="readscope-progress-bar-wrap" title="<?php echo esc_attr(sprintf('%d booked / %d total capacity', $occupancy['total_booked'], $occupancy['total_capacity'])); ?>">
                            <div class="readscope-progress-bar" style="width: <?php echo esc_attr((string) min(100.0, $occupancy['rate'])); ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Travelers -->
                <div class="kpi-card">
                    <div class="kpi-icon icon-travelers"><span class="dashicons dashicons-groups"></span></div>
                    <div class="kpi-info">
                        <span class="kpi-title"><?php esc_html_e('Total Guests / Travelers', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span class="kpi-number"><?php echo esc_html((string) $metrics['total_travelers']); ?></span>
                        <span class="kpi-subtext"><?php esc_html_e('Adults & children served', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                    </div>
                </div>

                <!-- Average Order Value (AOV) -->
                <div class="kpi-card">
                    <div class="kpi-icon icon-aov"><span class="dashicons dashicons-tag"></span></div>
                    <div class="kpi-info">
                        <span class="kpi-title"><?php esc_html_e('Average Booking Value', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                        <span class="kpi-number"><?php echo esc_html(Money::format($metrics['average_order_value'])); ?></span>
                        <span class="kpi-subtext">
                            <?php
                            printf(
                                /* translators: 1: Outstanding due amount */
                                esc_html__('Unpaid due: %s', 'the-readscope-tour-and-accommodation-booking'),
                                esc_html(Money::format($metrics['due_amount']))
                            );
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sales & Bookings Timeline Visualizer -->
            <div class="readscope-card-box readscope-chart-card">
                <div class="box-header">
                    <h2><?php esc_html_e('Sales & Booking Volume Timeline', 'the-readscope-tour-and-accommodation-booking'); ?></h2>
                    <span class="readscope-chart-legend">
                        <span class="legend-dot"></span> <?php esc_html_e('Daily Revenue', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </span>
                </div>
                <div class="readscope-timeline-chart-container">
                    <?php if (empty($timeline)) : ?>
                        <div class="readscope-no-data-notice">
                            <p><?php esc_html_e('No booking transactions recorded during this selected date range.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                        </div>
                    <?php else :
                        $maxRev = 1.0;
                        foreach ($timeline as $dayData) {
                            if ($dayData['revenue'] > $maxRev) {
                                $maxRev = $dayData['revenue'];
                            }
                        }
                    ?>
                        <div class="readscope-bar-chart">
                            <?php foreach ($timeline as $dayData) :
                                $heightPct = min(100.0, max(8.0, ($dayData['revenue'] / $maxRev) * 100.0));
                            ?>
                                <div class="bar-col" title="<?php echo esc_attr(sprintf('%s: %s (%d bookings)', $dayData['label'], Money::format($dayData['revenue']), $dayData['booking_count'])); ?>">
                                    <div class="bar-fill-wrap">
                                        <div class="bar-fill" style="height: <?php echo esc_attr((string) round($heightPct)); ?>%;">
                                            <span class="bar-tooltip"><?php echo esc_html(Money::format($dayData['revenue'])); ?></span>
                                        </div>
                                    </div>
                                    <span class="bar-label"><?php echo esc_html($dayData['label']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Performing Products Layout -->
            <div class="readscope-dashboard-layout readscope-top-items-grid">
                <!-- Top Tour Packages -->
                <div class="readscope-card-box">
                    <div class="box-header">
                        <h2>
                            <span class="dashicons dashicons-palmtree"></span>
                            <?php esc_html_e('Top Tour Packages', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </h2>
                    </div>
                    <?php if (empty($topItems['tours'])) : ?>
                        <p class="readscope-empty-text"><?php esc_html_e('No tour bookings found in this period.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    <?php else :
                        $maxTourSales = max(1.0, (float) ($topItems['tours'][0]['total_sales'] ?? 1.0));
                    ?>
                        <div class="readscope-ranked-list">
                            <?php foreach ($topItems['tours'] as $idx => $tour) :
                                $share = min(100.0, round(($tour['total_sales'] / $maxTourSales) * 100.0));
                            ?>
                                <div class="ranked-item">
                                    <div class="ranked-header">
                                        <div class="ranked-title">
                                            <span class="rank-badge">#<?php echo esc_html((string) ($idx + 1)); ?></span>
                                            <strong><?php echo esc_html($tour['item_title']); ?></strong>
                                        </div>
                                        <div class="ranked-sales">
                                            <strong><?php echo esc_html(Money::format($tour['total_sales'])); ?></strong>
                                        </div>
                                    </div>
                                    <div class="ranked-progress">
                                        <div class="ranked-progress-fill" style="width: <?php echo esc_attr((string) $share); ?>%;"></div>
                                    </div>
                                    <div class="ranked-meta">
                                        <span><?php echo esc_html(sprintf(/* translators: %d: count */ _n('%d ticket sold', '%d tickets sold', $tour['units_sold'], 'the-readscope-tour-and-accommodation-booking'), $tour['units_sold'])); ?></span>
                                        <span>&bull;</span>
                                        <span><?php echo esc_html(sprintf(/* translators: %d: count */ _n('%d traveler', '%d travelers', $tour['traveler_count'], 'the-readscope-tour-and-accommodation-booking'), $tour['traveler_count'])); ?></span>
                                        <span>&bull;</span>
                                        <span><?php echo esc_html(sprintf(/* translators: %d: count */ _n('%d booking', '%d bookings', $tour['bookings_count'], 'the-readscope-tour-and-accommodation-booking'), $tour['bookings_count'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Top Hotel Stays / Rooms -->
                <div class="readscope-card-box">
                    <div class="box-header">
                        <h2>
                            <span class="dashicons dashicons-admin-home"></span>
                            <?php esc_html_e('Top Hotel Stays & Rooms', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </h2>
                    </div>
                    <?php if (empty($topItems['rooms'])) : ?>
                        <p class="readscope-empty-text"><?php esc_html_e('No hotel room bookings found in this period.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                    <?php else :
                        $maxRoomSales = max(1.0, (float) ($topItems['rooms'][0]['total_sales'] ?? 1.0));
                    ?>
                        <div class="readscope-ranked-list">
                            <?php foreach ($topItems['rooms'] as $idx => $room) :
                                $share = min(100.0, round(($room['total_sales'] / $maxRoomSales) * 100.0));
                            ?>
                                <div class="ranked-item">
                                    <div class="ranked-header">
                                        <div class="ranked-title">
                                            <span class="rank-badge rank-badge-room">#<?php echo esc_html((string) ($idx + 1)); ?></span>
                                            <strong><?php echo esc_html($room['item_title']); ?></strong>
                                        </div>
                                        <div class="ranked-sales">
                                            <strong><?php echo esc_html(Money::format($room['total_sales'])); ?></strong>
                                        </div>
                                    </div>
                                    <div class="ranked-progress">
                                        <div class="ranked-progress-fill ranked-fill-room" style="width: <?php echo esc_attr((string) $share); ?>%;"></div>
                                    </div>
                                    <div class="ranked-meta">
                                        <span><?php echo esc_html(sprintf(/* translators: %d: count */ _n('%d night/unit', '%d nights/units', $room['units_sold'], 'the-readscope-tour-and-accommodation-booking'), $room['units_sold'])); ?></span>
                                        <span>&bull;</span>
                                        <span><?php echo esc_html(sprintf(/* translators: %d: count */ _n('%d guest', '%d guests', $room['traveler_count'], 'the-readscope-tour-and-accommodation-booking'), $room['traveler_count'])); ?></span>
                                        <span>&bull;</span>
                                        <span><?php echo esc_html(sprintf(/* translators: %d: count */ _n('%d booking', '%d bookings', $room['bookings_count'], 'the-readscope-tour-and-accommodation-booking'), $room['bookings_count'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
