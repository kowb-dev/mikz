/**
 * Admin JavaScript with Progress Bar
 *
 * @package MoySklad_WC_Sync
 * @version 2.1.0
 */

(function($) {
	'use strict';

	class MsWcSyncAdmin {
		constructor() {
			this.messageDiv = $('#ms-wc-sync-message');
			this.progressBar = null;
			this.progressInterval = null;
			this.init();
		}

		init() {
			this.bindEvents();
			this.createProgressBar();
			
			if (msWcSync.is_locked) {
				this.startProgressPolling();
			}
		}

		bindEvents() {
			// Tab navigation
			$('.ms-wc-sync-tabs .nav-tab').on('click', (e) => {
				e.preventDefault();
				
				const target = $(e.currentTarget).attr('href');
				
				$('.ms-wc-sync-tabs .nav-tab').removeClass('nav-tab-active');
				$(e.currentTarget).addClass('nav-tab-active');
				
				$('.ms-wc-sync-tabs .tab-content').removeClass('active');
				$(target).addClass('active');
			});
			
			// Toggle context data
			$('.toggle-context').on('click', (e) => {
				const $context = $(e.currentTarget).next('.context-data');
				
				if ($context.is(':visible')) {
					$context.hide();
					$(e.currentTarget).text('Show');
				} else {
					$context.show();
					$(e.currentTarget).text('Hide');
				}
			});
			
			// Main actions
			$('#run-sync').on('click', (e) => this.handleManualSync(e));
			$('#run-stock-sync').on('click', (e) => this.handleStockSync(e));
			$('#test-connection').on('click', (e) => this.handleTestConnection(e));
			$('#register-webhooks').on('click', (e) => this.handleRegisterWebhooks(e));
			$('#reset-lock').on('click', (e) => this.handleResetLock(e));
			$('#reschedule-cron').on('click', (e) => this.handleReschedule(e));
		}

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
			
			$('.ms-wc-sync-settings-form').after(progressHtml);
			this.progressBar = $('#ms-wc-sync-progress-container');
		}

		showMessage(message, type = 'success', duration = 5000) {
			const noticeClass = type === 'success' ? 'notice-success' : 
			                   type === 'warning' ? 'notice-warning' : 
			                   type === 'info' ? 'notice-info' : 'notice-error';
			
			const $notice = $(`
				<div class="notice ${noticeClass} is-dismissible">
					<p>${message}</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">Dismiss this notice.</span>
					</button>
				</div>
			`);

			this.messageDiv.html($notice);

			$notice.find('.notice-dismiss').on('click', function() {
				$(this).parent().fadeOut(300, function() {
					$(this).remove();
				});
			});

			if (duration > 0) {
				setTimeout(() => {
					$notice.fadeOut(300, function() {
						$(this).remove();
					});
				}, duration);
			}
		}

		showProgressBar() {
			this.progressBar.slideDown(300);
			this.updateProgress(0, 'Инициализация...', '');
		}

		hideProgressBar() {
			this.progressBar.slideUp(300);
		}

		updateProgress(percent, message, details = '') {
			const $bar = $('#ms-wc-sync-progress-bar');
			const $percent = $('#ms-wc-sync-progress-percent');
			const $message = $('#ms-wc-sync-progress-message');
			const $details = $('#ms-wc-sync-progress-details');

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

			percent = Math.min(100, Math.max(0, percent));
			$bar.css('width', percent + '%');
			$percent.text(percent + '%');
			$message.text(message);
			
			if (details) {
				$details.text(details);
			}

			if (percent === 100) {
				$bar.css('background', 'linear-gradient(90deg, #00a32a 0%, #008a20 100%)');
			}
		}

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
						
						const age = Math.floor(Date.now() / 1000) - timestamp;
						
						if (age > 30) {
							this.updateProgress(100, 'Завершено', 'Перезагрузка страницы...');
							setTimeout(() => {
								this.stopProgressPolling();
								location.reload();
							}, 1000);
							return;
						}

						this.updateProgress(percent, message, `Обновлено ${age} сек. назад`);

						if (percent === 100 || percent < 0) {
							setTimeout(() => {
								this.stopProgressPolling();
								location.reload();
							}, 2000);
						}
					}
				} catch (error) {
					console.error('Progress polling error:', error);
					this.stopProgressPolling();
				}
			}, 2000);
		}

		stopProgressPolling() {
			if (this.progressInterval) {
				clearInterval(this.progressInterval);
				this.progressInterval = null;
			}
			
			setTimeout(() => {
				this.hideProgressBar();
			}, 3000);
		}

		async handleManualSync(e) {
			e.preventDefault();

			if (!confirm('Are you sure you want to run a full sync? This may take several minutes.')) {
				return;
			}

			const $button = $(e.currentTarget);
			const originalText = $button.text();

			$button.prop('disabled', true).text(msWcSync.strings.sync_in_progress);

			this.showProgressBar();
			this.startProgressPolling();

			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_manual',
						nonce: msWcSync.nonce
					},
					timeout: 180000
				});

				if (response.success) {
					const results = response.data;
					const message = `${msWcSync.strings.sync_complete}: ${results.success} success, ${results.failed} failed (${results.created} created, ${results.updated} updated)`;
					
					this.showMessage(message, 'success', 0);
					this.updateProgress(100, 'Завершено', message);
					
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
				
				if (error.statusText === 'timeout') {
					this.showMessage('Синхронизация запущена. Обновите страницу через несколько минут.', 'info', 0);
					this.updateProgress(50, 'Выполняется в фоне...', 'Обновите страницу через несколько минут');
				} else {
					this.showMessage(msWcSync.strings.sync_error, 'error');
					this.updateProgress(-1, 'Ошибка соединения', error.toString());
				}
				this.stopProgressPolling();
			} finally {
				$button.prop('disabled', false).text(originalText);
			}
		}

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
		async handleStockSync(e) {
			e.preventDefault();
			
			if (!confirm('Are you sure you want to run a stock sync?')) {
				return;
			}
			
			const $button = $(e.currentTarget);
			const originalText = $button.text();
			
			$button.prop('disabled', true).text('Running stock sync...');
			$button.after('<span class="spinner is-active" style="float: none; margin: 0 5px;"></span>');
			
			try {
				const response = await $.ajax({
					url: msWcSync.ajax_url,
					type: 'POST',
					data: {
						action: 'ms_wc_sync_stock_manual',
						nonce: msWcSync.nonce
					},
					timeout: 60000
				});
				
				if (response.success) {
					const results = response.data;
					const message = `Stock sync completed: ${results.updated} updated, ${results.skipped} skipped (${results.duration.toFixed(2)}s)`;
					
					this.showMessage(message, 'success');
					
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
				$button.next('.spinner').remove();
			}
		}
		
		async handleRegisterWebhooks(e) {
			e.preventDefault();
			
			if (!confirm('Are you sure you want to register webhooks with MoySklad?')) {
				return;
			}
			
			const $button = $(e.currentTarget);
			const originalText = $button.text();
			
			$button.prop('disabled', true).text('Registering webhooks...');
			$button.after('<span class="spinner is-active" style="float: none; margin: 0 5px;"></span>');
			
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
					this.showMessage(`Webhooks registered successfully: ${response.data.count} webhooks`, 'success');
					
					setTimeout(() => {
						location.reload();
					}, 2000);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(`Failed to register webhooks: ${errorMessage}`, 'error');
				}
			} catch (error) {
				console.error('Register webhooks error:', error);
				this.showMessage('Failed to register webhooks', 'error');
			} finally {
				$button.prop('disabled', false).text(originalText);
				$button.next('.spinner').remove();
			}
		}
	}

	$(document).ready(() => {
		new MsWcSyncAdmin();
	});

})(jQuery);