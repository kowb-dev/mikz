/**
 * Admin JavaScript with Progress Bar and All Button Handlers
 *
 * @package MoySklad_WC_Sync
 * @version 2.2.1
 * 
 * FILE: admin.js
 * PATH: /wp-content/plugins/moysklad-wc-sync/assets/js/admin.js
 */

(function($) {
	'use strict';

	/**
	 * Admin Controller Class with Progress Tracking
	 */
	class MsWcSyncAdmin {
		constructor() {
			this.messageDiv = $('#ms-wc-sync-message');
			this.progressBar = null;
			this.progressInterval = null;
			this.init();
		}

		/**
		 * Initialize admin controller
		 */
		init() {
			this.bindEvents();
			this.createProgressBar();
			
			// Проверяем, не запущена ли синхронизация
			if (msWcSync.is_locked) {
				this.startProgressPolling();
			}
			
			// Показываем сообщение WordPress о сохранении настроек
			this.checkSettingsSaved();
		}
		
		/**
		 * Check if settings were just saved and show message
		 */
		checkSettingsSaved() {
			const urlParams = new URLSearchParams(window.location.search);
			if (urlParams.get('settings-updated') === 'true') {
				this.showMessage('Settings saved successfully!', 'success', 5000);
			}
		}

		/**
		 * Bind DOM events
		 */
		bindEvents() {
			$('#ms-wc-sync-manual').on('click', (e) => this.handleManualSync(e));
			$('#ms-wc-sync-test-connection').on('click', (e) => this.handleTestConnection(e));
			$('#ms-wc-sync-reset-lock').on('click', (e) => this.handleResetLock(e));
			$('#ms-wc-sync-reschedule').on('click', (e) => this.handleReschedule(e));
			$('#ms-wc-sync-stock-manual').on('click', (e) => this.handleStockSync(e));
			$('#ms-wc-sync-register-webhooks').on('click', (e) => this.handleRegisterWebhooks(e));
		}

		/**
		 * Create progress bar HTML
		 */
		createProgressBar() {
			const progressHtml = `
				<div id="ms-wc-sync-progress-container" style="display: none; margin: 20px 0;">
					<div style="background: #f0f0f1; border: 1px solid #c3c4c7; border-radius: 4px; padding: 15px;">
						<div style="margin-bottom: 10px;">
							<strong id="ms-wc-sync-progress-message">Подготовка...</strong>
						</div>
						<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 3px; height: 24px; overflow: hidden; position: relative;">
							<div id="ms-wc-sync-progress-bar" style="background: linear-gradient(90deg, #2271b1 0%, #135e96 100%); height: 100%; width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px;">
								<span id="ms-wc-sync-progress-percent">0%</span>
							</div>
						</div>
						<div style="margin-top: 8px; font-size: 12px; color: #646970;" id="ms-wc-sync-progress-details">
							Ожидание...
						</div>
					</div>
				</div>
			`;
			
			// Insert after first form or message div
			if (this.messageDiv.length) {
				this.messageDiv.after(progressHtml);
			} else {
				$('.ms-wc-sync-dashboard').prepend(progressHtml);
			}
			this.progressBar = $('#ms-wc-sync-progress-container');
		}

		/**
		 * Show message to user
		 * 
		 * @param {string} message - Message text
		 * @param {string} type - Message type (success|error|warning)
		 * @param {number} duration - Display duration in ms
		 */
		showMessage(message, type = 'success', duration = 5000) {
			const noticeClass = type === 'success' ? 'notice-success' : 
			                   type === 'warning' ? 'notice-warning' : 'notice-error';
			
			const $notice = $(`
				<div class="notice ${noticeClass} is-dismissible">
					<p>${message}</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">Dismiss this notice.</span>
					</button>
				</div>
			`);

			this.messageDiv.html($notice);

			// Handle dismiss button
			$notice.find('.notice-dismiss').on('click', function() {
				$(this).parent().fadeOut(300, function() {
					$(this).remove();
				});
			});

			// Auto-hide after duration
			if (duration > 0) {
				setTimeout(() => {
					$notice.fadeOut(300, function() {
						$(this).remove();
					});
				}, duration);
			}
		}

		/**
		 * Show progress bar
		 */
		showProgressBar() {
			this.progressBar.slideDown(300);
			this.updateProgress(0, 'Инициализация...', '');
		}

		/**
		 * Hide progress bar
		 */
		hideProgressBar() {
			this.progressBar.slideUp(300);
		}

		/**
		 * Update progress bar
		 * 
		 * @param {number} percent - Progress percentage (0-100)
		 * @param {string} message - Status message
		 * @param {string} details - Additional details
		 */
		updateProgress(percent, message, details = '') {
			const $bar = $('#ms-wc-sync-progress-bar');
			const $percent = $('#ms-wc-sync-progress-percent');
			const $message = $('#ms-wc-sync-progress-message');
			const $details = $('#ms-wc-sync-progress-details');

			// Обработка ошибки
			if (percent < 0) {
				$bar.css({
					'background': 'linear-gradient(90deg, #dc3232 0%, #b32d2e 100%)',
					'width': '100%'
				});
				$percent.text('Ошибка');
				$message.text(message);
				$details.text(details);
				return;
			}

			// Нормальный прогресс
			percent = Math.min(100, Math.max(0, percent));
			$bar.css('width', percent + '%');
			$percent.text(percent + '%');
			$message.text(message);
			
			if (details) {
				$details.text(details);
			}

			// Зеленый цвет при завершении
			if (percent === 100) {
				$bar.css('background', 'linear-gradient(90deg, #00a32a 0%, #008a20 100%)');
			}
		}

		/**
		 * Start polling for progress updates
		 */
		startProgressPolling() {
			this.showProgressBar();
			
			this.progressInterval = setInterval(async () => {
				try {
					const response = await $.ajax({
						url: msWcSync.ajax_url,
						type: 'POST',
						data: {
							action: 'ms_wc_sync_get_progress',
							nonce: msWcSync.nonce
						}
					});

					if (response.success && response.data) {
						const { percent, message, timestamp } = response.data;
						
						// Проверяем свежесть данных (не старше 30 секунд)
						const age = Math.floor(Date.now() / 1000) - timestamp;
						
						if (age > 30) {
							// Данные устарели - возможно синхронизация завершилась
							this.stopProgressPolling();
							return;
						}

						this.updateProgress(percent, message, `Обновлено ${age} сек. назад`);

						// Если завершено
						if (percent === 100 || percent < 0) {
							setTimeout(() => {
								this.stopProgressPolling();
								if (percent === 100) {
									location.reload();
								}
							}, 2000);
						}
					}
				} catch (error) {
					console.error('Progress polling error:', error);
				}
			}, 2000); // Обновление каждые 2 секунды
		}

		/**
		 * Stop polling for progress updates
		 */
		stopProgressPolling() {
			if (this.progressInterval) {
				clearInterval(this.progressInterval);
				this.progressInterval = null;
			}
			
			setTimeout(() => {
				this.hideProgressBar();
			}, 3000);
		}

		/**
		 * Handle manual sync button click
		 * 
		 * @param {Event} e - Click event
		 */
		async handleManualSync(e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const originalText = $button.text();

			// Disable button
			$button.prop('disabled', true).text(msWcSync.strings.sync_in_progress);

			// Show progress bar
			this.showProgressBar();
			this.startProgressPolling();

			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_manual',
						nonce: msWcSync.nonce
					}
				});

				if (response.success) {
					const results = response.data;
					const message = `${msWcSync.strings.sync_complete}: ${results.success} success, ${results.failed} failed (${results.created} created, ${results.updated} updated)`;
					
					this.showMessage(message, 'success', 0); // Не скрывать автоматически
					
					// Reload page after 3 seconds
					setTimeout(() => {
						location.reload();
					}, 3000);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(`${msWcSync.strings.sync_error}: ${errorMessage}`, 'error', 0);
					this.updateProgress(-1, 'Ошибка', errorMessage);
					this.stopProgressPolling();
				}
			} catch (error) {
				console.error('Sync error:', error);
				this.showMessage(msWcSync.strings.sync_error, 'error');
				this.updateProgress(-1, 'Ошибка соединения', error.toString());
				this.stopProgressPolling();
			} finally {
				$button.prop('disabled', false).text(originalText);
			}
		}

		/**
		 * Handle stock sync button click
		 * 
		 * @param {Event} e - Click event
		 */
		async handleStockSync(e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const originalText = $button.text();

			$button.prop('disabled', true).text('Syncing stock...');

			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_stock_manual',
						nonce: msWcSync.nonce
					}
				});

				if (response.success) {
					const results = response.data;
					const message = `Stock sync complete: ${results.updated} updated, ${results.skipped} skipped`;
					this.showMessage(message, 'success');
					
					// Reload after 2 seconds
					setTimeout(() => {
						location.reload();
					}, 2000);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(`Stock sync failed: ${errorMessage}`, 'error');
				}
			} catch (error) {
				console.error('Stock sync error:', error);
				this.showMessage('Stock sync failed', 'error');
			} finally {
				$button.prop('disabled', false).text(originalText);
			}
		}

		/**
		 * Handle register webhooks button click
		 * 
		 * @param {Event} e - Click event
		 */
		async handleRegisterWebhooks(e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const originalText = $button.text();

			$button.prop('disabled', true).text('Registering webhooks...');

			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_register_webhooks',
						nonce: msWcSync.nonce
					}
				});

				if (response.success) {
					const count = response.data?.count || 0;
					this.showMessage(`Webhooks registered successfully! Total: ${count}`, 'success');
					
					// Reload after 2 seconds
					setTimeout(() => {
						location.reload();
					}, 2000);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(`Ошибка регистрации вебхуков: ${errorMessage}`, 'error');
				}
			} catch (error) {
				console.error('Register webhooks error:', error);
				this.showMessage('Failed to register webhooks', 'error');
			} finally {
				$button.prop('disabled', false).text(originalText);
			}
		}

		/**
		 * Handle test connection button click
		 * 
		 * @param {Event} e - Click event
		 */
		async handleTestConnection(e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const originalText = $button.text();

			$button.prop('disabled', true).text('Testing...');

			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_test_connection',
						nonce: msWcSync.nonce
					}
				});

				if (response.success) {
					this.showMessage(
						`${msWcSync.strings.test_success}: ${response.data.message}`,
						'success'
					);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(
						`${msWcSync.strings.test_failed}: ${errorMessage}`,
						'error'
					);
				}
			} catch (error) {
				console.error('Connection test error:', error);
				this.showMessage(msWcSync.strings.test_failed, 'error');
			} finally {
				$button.prop('disabled', false).text(originalText);
			}
		}

		/**
		 * Handle reset lock button click
		 * 
		 * @param {Event} e - Click event
		 */
		async handleResetLock(e) {
			e.preventDefault();

			if (!confirm(msWcSync.strings.reset_confirm)) {
				return;
			}

			const $button = $(e.currentTarget);
			const originalText = $button.text();

			$button.prop('disabled', true).text('Stopping...');

			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_reset_lock',
						nonce: msWcSync.nonce
					}
				});

				if (response.success) {
					this.showMessage(msWcSync.strings.reset_success, 'success');
					this.hideProgressBar();
					this.stopProgressPolling();
					
					// Reload after 1 second
					setTimeout(() => {
						location.reload();
					}, 1000);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(
						`${msWcSync.strings.reset_failed}: ${errorMessage}`,
						'error'
					);
				}
			} catch (error) {
				console.error('Reset lock error:', error);
				this.showMessage(msWcSync.strings.reset_failed, 'error');
			} finally {
				$button.prop('disabled', false).text(originalText);
			}
		}
		
		/**
		 * Handle reschedule cron button click
		 * 
		 * @param {Event} e - Click event
		 */
		async handleReschedule(e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const originalText = $button.text();

			$button.prop('disabled', true).text('Scheduling...');

			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_reschedule',
						nonce: msWcSync.nonce
					}
				});

				if (response.success) {
					this.showMessage(
						`${response.data.message}. Next run: ${response.data.next_run}`,
						'success'
					);
					
					// Reload after 2 seconds
					setTimeout(() => {
						location.reload();
					}, 2000);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(`Failed to schedule: ${errorMessage}`, 'error');
				}
			} catch (error) {
				console.error('Reschedule error:', error);
				this.showMessage('Failed to reschedule cron', 'error');
			} finally {
				$button.prop('disabled', false).text(originalText);
			}
		}
	}

	// Initialize when DOM is ready
	$(document).ready(() => {
		new MsWcSyncAdmin();
	});

})(jQuery);