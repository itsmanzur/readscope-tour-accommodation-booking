<?php

declare(strict_types=1);

namespace Readscope\Providers;

use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Database\LegacyMigrator;
use Readscope\Database\Schema;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DatabaseServiceProvider
 *
 * Registers database services and manages automatic schema migrations on updates.
 *
 * @package Readscope\Providers
 */
class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(Schema::class, fn () => new Schema());
    }

    public function boot(): void
    {
        // Check if database upgrade is needed on admin init
        $this->addAction('admin_init', [$this, 'maybeRunMigrations']);

        // Updates replace the files without running activation, so keep the stored version current.
        $this->addAction('admin_init', [$this, 'syncStoredVersion']);

        // 1.0.x data move that could not finish: tell the site owner and offer a retry.
        $this->addAction('admin_notices', [$this, 'renderMigrationNotice']);
        $this->addAction('admin_post_readscope_retry_migration', [$this, 'handleRetryMigration']);

        // Run migrations when Readscope Booking plugin is updated
        $this->addAction('upgrader_process_complete', [$this, 'onUpgraderProcessComplete'], 10, 2);
    }

    /**
     * Run migrations automatically if database version is outdated.
     *
     * @return void
     */
    public function maybeRunMigrations(): void
    {
        $installedVersion = get_option(Schema::DB_VERSION_OPTION, '0.0.0');

        if (version_compare((string) $installedVersion, Schema::CURRENT_DB_VERSION, '<')) {
            Schema::migrate();
        }
    }

    /**
     * Store the running plugin version when it differs (in-place updates never run activation).
     *
     * @return void
     */
    public function syncStoredVersion(): void
    {
        if (get_option('readscope_version') !== READSCOPE_VERSION) {
            update_option('readscope_version', READSCOPE_VERSION);
        }
    }

    /**
     * Warn administrators when moving the 1.0.x data did not finish.
     *
     * @return void
     */
    public function renderMigrationNotice(): void
    {
        if (!current_user_can('manage_options') || !LegacyMigrator::hasFailed()) {
            return;
        }

        $retryUrl = wp_nonce_url(admin_url('admin-post.php?action=readscope_retry_migration'), 'readscope_retry_migration');
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php esc_html_e('Readscope: your earlier booking data has not finished moving yet.', 'the-readscope-tour-and-accommodation-booking'); ?></strong>
                <?php esc_html_e('Nothing was deleted. The move is retried automatically; you can also retry it now.', 'the-readscope-tour-and-accommodation-booking'); ?>
            </p>
            <p><code><?php echo esc_html(LegacyMigrator::lastError()); ?></code></p>
            <p><a class="button button-primary" href="<?php echo esc_url($retryUrl); ?>"><?php esc_html_e('Retry now', 'the-readscope-tour-and-accommodation-booking'); ?></a></p>
        </div>
        <?php
    }

    /**
     * Retry the 1.0.x data move on request.
     *
     * @return void
     */
    public function handleRetryMigration(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', 'the-readscope-tour-and-accommodation-booking'), 403);
        }

        check_admin_referer('readscope_retry_migration');
        LegacyMigrator::retry();

        wp_safe_redirect(admin_url('index.php'));
        exit;
    }

    /**
     * Run migrations on plugin upgrade completion.
     *
     * @param mixed $upgrader
     * @param array<string, mixed> $options
     * @return void
     */
    public function onUpgraderProcessComplete(mixed $upgrader, array $options): void
    {
        if (
            isset($options['action'], $options['type']) &&
            $options['action'] === 'update' &&
            $options['type'] === 'plugin'
        ) {
            $plugins = isset($options['plugins']) && is_array($options['plugins']) ? $options['plugins'] : [];
            if (in_array(plugin_basename(READSCOPE_PLUGIN_FILE), $plugins, true)) {
                Schema::migrate();
            }
        }
    }
}
