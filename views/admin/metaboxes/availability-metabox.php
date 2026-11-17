<?php
/**
 * Readscope Booking Admin Availability & Pricing Calendar MetaBox View
 *
 * @package Readscope\Views\Admin
 * @var \WP_Post $post
 * @var int      $itemId
 * @var string   $itemType 'tour' or 'room'
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="readscope-availability-app" id="readscope-availability-app" data-item-id="<?php echo esc_attr((string) $itemId); ?>" data-item-type="<?php echo esc_attr($itemType); ?>">
    <div class="readscope-avail-header">
        <div class="readscope-avail-nav-controls">
            <button type="button" class="button button-secondary readscope-avail-nav-prev" aria-label="<?php esc_attr_e('Previous Month', 'the-readscope-tour-and-accommodation-booking'); ?>">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </button>
            <h3 class="readscope-avail-current-month" aria-live="polite">
                <!-- Dynamically populated (e.g. November 2026) -->
            </h3>
            <button type="button" class="button button-secondary readscope-avail-nav-next" aria-label="<?php esc_attr_e('Next Month', 'the-readscope-tour-and-accommodation-booking'); ?>">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
            <button type="button" class="button button-secondary readscope-avail-nav-today">
                <?php esc_html_e('Today', 'the-readscope-tour-and-accommodation-booking'); ?>
            </button>
        </div>

        <div class="readscope-avail-header-meta">
            <span class="readscope-avail-meta-badge">
                <span class="dashicons dashicons-groups"></span>
                <strong><?php esc_html_e('Default Capacity:', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                <span class="readscope-avail-def-cap">—</span>
            </span>
            <span class="readscope-avail-meta-badge">
                <span class="dashicons dashicons-tag"></span>
                <strong><?php esc_html_e('Base Price:', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                <span class="readscope-avail-def-price">—</span>
            </span>
        </div>
    </div>

    <div class="readscope-avail-body">
        <!-- Main Calendar Area -->
        <div class="readscope-avail-calendar-wrap">
            <div class="readscope-avail-weekdays" role="row">
                <div class="readscope-avail-weekday" role="columnheader"><?php esc_html_e('Mon', 'the-readscope-tour-and-accommodation-booking'); ?></div>
                <div class="readscope-avail-weekday" role="columnheader"><?php esc_html_e('Tue', 'the-readscope-tour-and-accommodation-booking'); ?></div>
                <div class="readscope-avail-weekday" role="columnheader"><?php esc_html_e('Wed', 'the-readscope-tour-and-accommodation-booking'); ?></div>
                <div class="readscope-avail-weekday" role="columnheader"><?php esc_html_e('Thu', 'the-readscope-tour-and-accommodation-booking'); ?></div>
                <div class="readscope-avail-weekday" role="columnheader"><?php esc_html_e('Fri', 'the-readscope-tour-and-accommodation-booking'); ?></div>
                <div class="readscope-avail-weekday readscope-weekend" role="columnheader"><?php esc_html_e('Sat', 'the-readscope-tour-and-accommodation-booking'); ?></div>
                <div class="readscope-avail-weekday readscope-weekend" role="columnheader"><?php esc_html_e('Sun', 'the-readscope-tour-and-accommodation-booking'); ?></div>
            </div>

            <div class="readscope-avail-grid" id="readscope-avail-grid" role="grid" aria-label="<?php esc_attr_e('Availability Calendar', 'the-readscope-tour-and-accommodation-booking'); ?>" tabindex="0">
                <!-- Days dynamically rendered here -->
            </div>

            <div class="readscope-avail-legend">
                <span class="readscope-legend-item"><span class="legend-dot status-available"></span> <?php esc_html_e('Available', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="readscope-legend-item"><span class="legend-dot status-blocked"></span> <?php esc_html_e('Blocked', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="readscope-legend-item"><span class="legend-dot status-sold-out"></span> <?php esc_html_e('Sold Out', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="readscope-legend-item"><span class="legend-dot status-override"></span> <?php esc_html_e('Custom Price Override', 'the-readscope-tour-and-accommodation-booking'); ?></span>
                <span class="readscope-legend-hint">
                    <span class="dashicons dashicons-info-outline"></span>
                    <?php esc_html_e('Click a date to select, or hold Shift to select a date range. Use Arrow keys to navigate.', 'the-readscope-tour-and-accommodation-booking'); ?>
                </span>
            </div>
        </div>

        <!-- Bulk Action Panel -->
        <div class="readscope-avail-panel">
            <div class="readscope-panel-header">
                <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">
                    <?php esc_html_e('Bulk Availability & Pricing', 'the-readscope-tour-and-accommodation-booking'); ?>
                </h4>
                <div class="readscope-selection-badge" id="readscope-selection-badge">
                    <?php esc_html_e('0 dates selected', 'the-readscope-tour-and-accommodation-booking'); ?>
                </div>
            </div>

            <!-- Quick Weekday Filters -->
            <div class="readscope-panel-section">
                <label class="readscope-panel-label"><?php esc_html_e('Filter Selected Dates by Day:', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                <div class="readscope-dow-checkboxes">
                    <label><input type="checkbox" class="readscope-dow-check" value="1" checked> <?php esc_html_e('Mo', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <label><input type="checkbox" class="readscope-dow-check" value="2" checked> <?php esc_html_e('Tu', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <label><input type="checkbox" class="readscope-dow-check" value="3" checked> <?php esc_html_e('We', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <label><input type="checkbox" class="readscope-dow-check" value="4" checked> <?php esc_html_e('Th', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <label><input type="checkbox" class="readscope-dow-check" value="5" checked> <?php esc_html_e('Fr', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <label><input type="checkbox" class="readscope-dow-check" value="6" checked> <?php esc_html_e('Sa', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <label><input type="checkbox" class="readscope-dow-check" value="7" checked> <?php esc_html_e('Su', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                </div>
            </div>

            <!-- Changes Form -->
            <div class="readscope-panel-section">
                <div class="readscope-form-group" style="margin-bottom: 12px;">
                    <label for="readscope-bulk-status" class="readscope-panel-label"><?php esc_html_e('Status Action:', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <select id="readscope-bulk-status" class="readscope-panel-select">
                        <option value=""><?php esc_html_e('— No Status Change —', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                        <option value="available"><?php esc_html_e('🟢 Mark as Available (Open)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                        <option value="blocked"><?php esc_html_e('🔴 Mark as Blocked (Closed)', 'the-readscope-tour-and-accommodation-booking'); ?></option>
                    </select>
                </div>

                <div class="readscope-form-group" style="margin-bottom: 12px;">
                    <label for="readscope-bulk-capacity" class="readscope-panel-label"><?php esc_html_e('Adjust Total Capacity:', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <input type="number" id="readscope-bulk-capacity" min="0" step="1" class="readscope-panel-input" placeholder="<?php esc_attr_e('Leave blank to keep current', 'the-readscope-tour-and-accommodation-booking'); ?>">
                </div>

                <div class="readscope-form-group" style="margin-bottom: 12px;">
                    <label for="readscope-bulk-price" class="readscope-panel-label"><?php esc_html_e('Date Price Override:', 'the-readscope-tour-and-accommodation-booking'); ?></label>
                    <input type="number" id="readscope-bulk-price" min="0" step="0.01" class="readscope-panel-input" placeholder="<?php esc_attr_e('e.g. 199.00', 'the-readscope-tour-and-accommodation-booking'); ?>">
                    <label style="display: block; margin-top: 6px; font-size: 12px; color: #64748b;">
                        <input type="checkbox" id="readscope-bulk-reset-price" value="1">
                        <?php esc_html_e('Reset price override to default base price', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </label>
                </div>

                <div class="readscope-form-group" style="margin-bottom: 16px; background: #fffbeb; padding: 10px; border-radius: 6px; border: 1px solid #fef3c7;">
                    <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 12px; color: #92400e; cursor: pointer;">
                        <input type="checkbox" id="readscope-bulk-force" value="1" style="margin-top: 2px;">
                        <span>
                            <strong><?php esc_html_e('Force Block Booked Dates', 'the-readscope-tour-and-accommodation-booking'); ?></strong><br>
                            <?php esc_html_e('Allows closing dates that already contain bookings (existing reservations remain valid).', 'the-readscope-tour-and-accommodation-booking'); ?>
                        </span>
                    </label>
                </div>

                <div class="readscope-panel-actions">
                    <button type="button" class="button button-primary readscope-bulk-apply-btn" id="readscope-bulk-apply-btn">
                        <span class="dashicons dashicons-saved" style="margin-top: 3px;"></span>
                        <?php esc_html_e('Apply to Selected Dates', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </button>
                    <button type="button" class="button button-secondary readscope-bulk-clear-btn" id="readscope-bulk-clear-btn">
                        <?php esc_html_e('Clear Selection', 'the-readscope-tour-and-accommodation-booking'); ?>
                    </button>
                </div>

                <div class="readscope-panel-alert" id="readscope-panel-alert" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
