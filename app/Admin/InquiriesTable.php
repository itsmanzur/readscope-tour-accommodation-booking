<?php

declare(strict_types=1);

namespace Readscope\Admin;

use Readscope\Common\Container;
use Readscope\Services\InquiryService;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class InquiriesTable
 *
 * Renders the Admin Trip Inquiries & Consultation Requests management view.
 *
 * @package Readscope\Admin
 */
class InquiriesTable
{
    /**
     * Render Inquiries table page.
     */
    public static function render(): void
    {
        if (!current_user_can('manage_readscope_bookings')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'the-readscope-tour-and-accommodation-booking'), 403);
        }

        $inquiryService = Container::getInstance()->get(InquiryService::class);
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filter; current_user_can() is checked above and the value is whitelisted below.
        $statusFilter   = isset($_GET['status']) ? sanitize_text_field(wp_unslash((string) $_GET['status'])) : '';
        if (!in_array($statusFilter, ['new', 'replied', 'closed', ''], true)) {
            $statusFilter = '';
        }
        $inquiries      = $inquiryService->getInquiries(['status' => $statusFilter, 'limit' => 50]);

        global $wpdb;
        $table = $wpdb->prefix . 'readscope_inquiries';
        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $countAll     = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $countNew     = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'new'");
        $countReplied = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'replied'");
        $countClosed  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'closed'");
        // phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

        $baseUrl = admin_url('admin.php?page=readscope-inquiries');
        ?>
        <div class="wrap readscope-admin-wrap">
            <div class="readscope-page-header">
                <div class="readscope-page-header__text">
                    <h1><?php esc_html_e('Inquiries', 'the-readscope-tour-and-accommodation-booking'); ?></h1>
                    <p class="readscope-subtitle"><?php esc_html_e('Manage leads and custom trip questions submitted by travelers through the "Ask an Expert" inquiry modal.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                </div>
            </div>
            <hr class="wp-header-end">

            <!-- Status Filter Bar -->
            <ul class="subsubsub">
                <li><a href="<?php echo esc_url($baseUrl); ?>" class="<?php echo empty($statusFilter) ? 'current' : ''; ?>"><?php esc_html_e('All', 'the-readscope-tour-and-accommodation-booking'); ?> <span class="count">(<?php echo esc_html((string)$countAll); ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url($baseUrl . '&status=new'); ?>" class="<?php echo $statusFilter === 'new' ? 'current' : ''; ?>"><?php esc_html_e('New Leads', 'the-readscope-tour-and-accommodation-booking'); ?> <span class="count">(<?php echo esc_html((string)$countNew); ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url($baseUrl . '&status=replied'); ?>" class="<?php echo $statusFilter === 'replied' ? 'current' : ''; ?>"><?php esc_html_e('Replied', 'the-readscope-tour-and-accommodation-booking'); ?> <span class="count">(<?php echo esc_html((string)$countReplied); ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url($baseUrl . '&status=closed'); ?>" class="<?php echo $statusFilter === 'closed' ? 'current' : ''; ?>"><?php esc_html_e('Closed', 'the-readscope-tour-and-accommodation-booking'); ?> <span class="count">(<?php echo esc_html((string)$countClosed); ?>)</span></a></li>
            </ul>

            <table class="wp-list-table widefat fixed striped readscope-inquiries-table">
                <thead>
                    <tr>
                        <th style="width: 140px;"><?php esc_html_e('Date', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                        <th style="width: 160px;"><?php esc_html_e('Traveler', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                        <th style="width: 180px;"><?php esc_html_e('Trip / Stay', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                        <th style="width: 140px;"><?php esc_html_e('Travel Date & Party', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                        <th><?php esc_html_e('Inquiry Message', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                        <th style="width: 110px;"><?php esc_html_e('Status', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                        <th style="width: 160px;"><?php esc_html_e('Quick Actions', 'the-readscope-tour-and-accommodation-booking'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)) : ?>
                        <tr>
                            <td colspan="7">
                                <div class="readscope-empty">
                                    <span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
                                    <p><?php esc_html_e('No inquiries found.', 'the-readscope-tour-and-accommodation-booking'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php else : foreach ($inquiries as $inq) :
                        $itemPost = get_post((int) $inq['item_id']);
                        $itemTitle = $itemPost ? $itemPost->post_title : esc_html__('General Inquiry', 'the-readscope-tour-and-accommodation-booking');
                        $mailtoSubject = rawurlencode(
                            sprintf(
                                /* translators: 1: Item title, 2: Site name */
                                __('Re: Your Inquiry for %1$s — %2$s', 'the-readscope-tour-and-accommodation-booking'),
                                $itemTitle,
                                get_bloginfo('name')
                            )
                        );
                        $mailtoUrl = 'mailto:' . esc_attr($inq['customer_email']) . '?subject=' . $mailtoSubject;
                        ?>
                        <tr id="inquiry-row-<?php echo esc_attr($inq['id']); ?>">
                            <td>
                                <strong><?php echo esc_html(gmdate('M d, Y', strtotime($inq['created_at']))); ?></strong>
                                <br><small><?php echo esc_html(gmdate('g:i A', strtotime($inq['created_at']))); ?></small>
                            </td>
                            <td>
                                <strong><?php echo esc_html($inq['customer_name']); ?></strong>
                                <br><a href="mailto:<?php echo esc_attr($inq['customer_email']); ?>"><?php echo esc_html($inq['customer_email']); ?></a>
                                <?php if (!empty($inq['customer_phone'])) : ?>
                                    <br><small class="readscope-inline-icon"><span class="dashicons dashicons-phone" aria-hidden="true"></span><?php echo esc_html($inq['customer_phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($itemPost) : ?>
                                    <a href="<?php echo esc_url(get_edit_post_link($itemPost->ID)); ?>" target="_blank">
                                        <strong><?php echo esc_html($itemTitle); ?></strong>
                                    </a>
                                <?php else : ?>
                                    <span><?php echo esc_html($itemTitle); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="readscope-inline-icon"><span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span><?php echo esc_html($inq['travel_date'] ?: __('Flexible', 'the-readscope-tour-and-accommodation-booking')); ?></span>
                                <br><small class="readscope-inline-icon"><span class="dashicons dashicons-groups" aria-hidden="true"></span><?php echo esc_html((string) $inq['guests']); ?> <?php esc_html_e('Guests', 'the-readscope-tour-and-accommodation-booking'); ?></small>
                            </td>
                            <td>
                                <div class="readscope-inquiry-message">
                                    <?php echo nl2br(esc_html($inq['message'])); ?>
                                </div>
                            </td>
                            <td>
                                <select class="change-inquiry-status-select" data-id="<?php echo esc_attr($inq['id']); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('readscope_inquiry_nonce')); ?>">
                                    <option value="new" <?php selected($inq['status'], 'new'); ?>><?php esc_html_e('New', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    <option value="replied" <?php selected($inq['status'], 'replied'); ?>><?php esc_html_e('Replied', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                    <option value="closed" <?php selected($inq['status'], 'closed'); ?>><?php esc_html_e('Closed', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                                </select>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($mailtoUrl); ?>" class="button button-small button-primary" target="_blank">
                                    <span class="dashicons dashicons-email" aria-hidden="true"></span><?php esc_html_e('Reply', 'the-readscope-tour-and-accommodation-booking'); ?>
                                </a>
                                <button type="button" class="button button-small delete-inquiry-btn readscope-button-danger" data-id="<?php echo esc_attr($inq['id']); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('readscope_inquiry_nonce')); ?>" aria-label="<?php esc_attr_e('Delete', 'the-readscope-tour-and-accommodation-booking'); ?>">
                                    <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
