/**
 * Admin JavaScript (Modern ES6+)
 *
 * @package MoySklad_WC_Sync
 * @version 2.0.0
 * 
 * FILE: admin.js
 * PATH: /wp-content/plugins/moysklad-wc-sync/assets/js/admin.js
 */

(function($) {
	'use strict';

	/**
	 * Admin Controller Class
	 */
	class MsWcSyncAdmin {
		constructor() {
			this.messageDiv = $('#ms-wc-sync-message');
			this.init();
		}

		/**
		 * Initialize admin controller
		 */
		init() {
			this.bindEvents();
		}

		/**
		 * Bind DOM events
		 */
		bindEvents() {
			$('#ms-wc-sync-manual').on('click', (e) => this.handleManualSync(e));
			$('#ms-wc-sync-test-connection').on('click', (e) => this.handleTestConnection(e));
		}

		/**
		 * Show message to user
		 * 
		 * @param {string} message - Message text
		 * @param {string} type - Message type (success|error)
		 * @param {number} duration - Display duration in ms
		 */
		showMessage(message, type = 'success', duration = 5000) {
			const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
			
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
			setTimeout(() => {
				$notice.fadeOut(300, function() {
					$(this).remove();
				});
			}, duration);
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
					
					this.showMessage(message, 'success');
					
					// Reload page after 2 seconds
					setTimeout(() => {
						location.reload();
					}, 2000);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					this.showMessage(`${msWcSync.strings.sync_error}: ${errorMessage}`, 'error');
				}
			} catch (error) {
				console.error('Sync error:', error);
				this.showMessage(msWcSync.strings.sync_error, 'error');
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
	}

	// Initialize when DOM is ready
	$(document).ready(() => {
		new MsWcSyncAdmin();
	});

})(jQuery);