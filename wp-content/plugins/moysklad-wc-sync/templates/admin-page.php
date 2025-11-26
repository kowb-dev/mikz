<?php
/**
 * Admin Settings Page Template
 *
 * @package MoySklad_WC_Sync
 * @version 2.0.0
 * 
 * FILE: admin-page.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/templates/admin-page.php
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1><?php echo esc_html__('MoySklad WooCommerce Sync', 'moysklad-wc-sync'); ?></h1>

    <div class="ms-wc-sync-dashboard">
        <div class="ms-wc-sync-stats">
            <div class="ms-wc-sync-stat-card">
                <h3><?php echo esc_html__('Last Sync', 'moysklad-wc-sync'); ?></h3>
                <p class="ms-wc-sync-stat-value">
                    <?php 
                    if ($last_run) {
                        $dt = new DateTime($last_run, new DateTimeZone('UTC'));
                        $dt->setTimezone(wp_timezone());
                        echo esc_html($dt->format('Y-m-d H:i:s'));
                    } else {
                        echo esc_html__('Never', 'moysklad-wc-sync');
                    }
                    ?>
                </p>
            </div>

            <div class="ms-wc-sync-stat-card">
                <h3><?php echo esc_html__('Next Sync', 'moysklad-wc-sync'); ?></h3>
                <p class="ms-wc-sync-stat-value">
                    <?php 
                    if ($next_run) {
                        $dt = new DateTime('@' . $next_run);
                        $dt->setTimezone(wp_timezone());
                        echo esc_html($dt->format('Y-m-d H:i:s'));
                    } else {
                        echo esc_html__('Not scheduled', 'moysklad-wc-sync');
                    }
                    ?>
                </p>
            </div>

            <?php if (!empty($last_results)) : ?>
                <div class="ms-wc-sync-stat-card">
                    <h3><?php echo esc_html__('Last Results', 'moysklad-wc-sync'); ?></h3>
                    <p class="ms-wc-sync-stat-value">
                        <?php
                        printf(
                            esc_html__('%1$d success / %2$d failed', 'moysklad-wc-sync'),
                            absint($last_results['success'] ?? 0),
                            absint($last_results['failed'] ?? 0)
                        );
                        ?>
                    </p>
                </div>

                <div class="ms-wc-sync-stat-card">
                    <h3><?php echo esc_html__('Duration', 'moysklad-wc-sync'); ?></h3>
                    <p class="ms-wc-sync-stat-value">
                        <?php
                        printf(
                            esc_html__('%d seconds', 'moysklad-wc-sync'),
                            absint($last_results['duration'] ?? 0)
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <form method="post" action="options.php" class="ms-wc-sync-settings-form">
            <?php settings_fields('ms_wc_sync_settings'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ms_wc_sync_api_token">
                            <?php echo esc_html__('MoySklad API Token', 'moysklad-wc-sync'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="password"
                            id="ms_wc_sync_api_token"
                            name="ms_wc_sync_api_token"
                            value="<?php echo esc_attr(get_option('ms_wc_sync_api_token')); ?>"
                            class="regular-text"
                            autocomplete="off"
                        />
                        <p class="description">
                            <?php echo esc_html__('Enter your MoySklad API token. You can generate it in your MoySklad account settings.', 'moysklad-wc-sync'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php echo esc_html__('Price Synchronization', 'moysklad-wc-sync'); ?>
                    </th>
                    <td>
                        <p class="description">
                            <?php echo esc_html__('The plugin synchronizes two price types from MoySklad:', 'moysklad-wc-sync'); ?>
                        </p>
                        <ul style="list-style: disc; margin-left: 20px;">
                            <li><strong><?php echo esc_html__('Retail Price (Розница)', 'moysklad-wc-sync'); ?></strong> - <?php echo esc_html__('saved as Regular Price in WooCommerce', 'moysklad-wc-sync'); ?></li>
                            <li><strong><?php echo esc_html__('Wholesale Price (Опт)', 'moysklad-wc-sync'); ?></strong> - <?php echo esc_html__('saved as custom meta field _wholesale_price', 'moysklad-wc-sync'); ?></li>
                        </ul>
                        <p class="description">
                            <?php echo esc_html__('To display wholesale prices on the frontend, use a wholesale pricing plugin or custom code.', 'moysklad-wc-sync'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <?php submit_button(__('Save Settings', 'moysklad-wc-sync'), 'primary', 'submit', false); ?>
                <button type="button" class="button button-secondary" id="ms-wc-sync-test-connection">
                    <?php echo esc_html__('Test Connection', 'moysklad-wc-sync'); ?>
                </button>
                <button type="button" class="button button-secondary" id="ms-wc-sync-manual">
                    <?php echo esc_html__('Run Manual Sync', 'moysklad-wc-sync'); ?>
                </button>
            </p>
        </form>

        <div id="ms-wc-sync-message"></div>

        <h2><?php echo esc_html__('Recent Logs', 'moysklad-wc-sync'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 180px;"><?php echo esc_html__('Time', 'moysklad-wc-sync'); ?></th>
                    <th style="width: 100px;"><?php echo esc_html__('Level', 'moysklad-wc-sync'); ?></th>
                    <th><?php echo esc_html__('Message', 'moysklad-wc-sync'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="3"><?php echo esc_html__('No logs found.', 'moysklad-wc-sync'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($logs as $log) : 
                        $dt = new DateTime($log['log_time'], new DateTimeZone('UTC'));
                        $dt->setTimezone(wp_timezone());
                    ?>
                        <tr>
                            <td><?php echo esc_html($dt->format('Y-m-d H:i:s')); ?></td>
                            <td>
                                <span class="ms-wc-sync-log-level ms-wc-sync-log-<?php echo esc_attr($log['log_level']); ?>">
                                    <?php echo esc_html(ucfirst($log['log_level'])); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log['message']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>