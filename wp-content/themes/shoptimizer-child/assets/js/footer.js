/**
 * Footer JavaScript for Shoptimizer Child Theme
 * Handles CF7 placeholder behavior and notifications
 * Author: KB
 * URL: https://kowb.ru
 * Version: 1.0.9
 */

(function() {
    'use strict';

    /**
     * CF7 Placeholder Manager
     * Handles placeholder-like behavior for CF7 email fields
     */
    class CF7PlaceholderManager {
        constructor() {
            this.defaultValue = 'Введите email';
            this.init();
        }

        init() {
            this.attachEventListeners();
        }

        attachEventListeners() {
            const cf7EmailFields = document.querySelectorAll('.mkx-newsletter-form .wpcf7-email');

            cf7EmailFields.forEach(field => {
                this.setupField(field);
            });
        }

        setupField(field) {
            // Handle focus - clear default value
            field.addEventListener('focus', () => {
                if (field.value === this.defaultValue) {
                    field.value = '';
                    field.classList.add('mkx-email-focused');
                }
            });

            // Handle blur - restore default if empty
            field.addEventListener('blur', () => {
                if (field.value.trim() === '') {
                    field.value = this.defaultValue;
                    field.classList.remove('mkx-email-focused');
                } else {
                    field.classList.add('mkx-email-focused');
                }
            });

            // Handle input - ensure proper styling
            field.addEventListener('input', () => {
                if (field.value !== this.defaultValue && field.value.trim() !== '') {
                    field.classList.add('mkx-email-focused');
                }
            });

            // Initial setup
            if (field.value === this.defaultValue) {
                field.classList.remove('mkx-email-focused');
            } else if (field.value.trim() !== '') {
                field.classList.add('mkx-email-focused');
            }
        }

        /**
         * Reset field to default state
         * @param {Element} field Email field element
         */
        resetField(field) {
            if (field) {
                field.value = this.defaultValue;
                field.classList.remove('mkx-email-focused');
            }
        }
    }

    /**
     * CF7 Notifications Manager
     * Handles all CF7 form submission notifications
     */
    class CF7NotificationsManager {
        constructor() {
            this.timeouts = new Map();
            this.init();
        }

        init() {
            // Wait for CF7 to be fully loaded
            if (typeof wpcf7 !== 'undefined') {
                this.attachEventListeners();
            } else {
                // Fallback: wait for document ready and CF7 initialization
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(() => this.attachEventListeners(), 100);
                });
            }
        }

        attachEventListeners() {
            const newsletterForms = document.querySelectorAll('.mkx-newsletter-form .wpcf7, .mkx-newsletter-form form');

            if (newsletterForms.length === 0) {
                return;
            }

            newsletterForms.forEach(form => {
                if (form.classList.contains('wpcf7')) {
                    // CF7 form
                    this.attachCF7FormListeners(form);
                } else {
                    // Fallback HTML form
                    this.attachFallbackFormListeners(form);
                }
            });
        }

        attachCF7FormListeners(form) {
            // Success event
            form.addEventListener('wpcf7mailsent', () => {
                this.handleSuccess(form);
            });

            // Mail failed event
            form.addEventListener('wpcf7mailfailed', () => {
                this.handleError(form, 'mailfailed');
            });

            // Validation failed event
            form.addEventListener('wpcf7invalid', () => {
                this.handleValidationError(form);
            });

            // Spam detected event
            form.addEventListener('wpcf7spam', () => {
                this.handleError(form, 'spam');
            });

            // Aborted event
            form.addEventListener('wpcf7aborted', () => {
                this.handleError(form, 'aborted');
            });
        }

        attachFallbackFormListeners(form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                const emailInput = form.querySelector('input[type="email"]');

                // Simple validation
                if (!emailInput || !emailInput.value.trim()) {
                    this.showSimpleMessage('Пожалуйста, введите email адрес', 'error');
                    return;
                }

                // Show success message
                this.showSimpleMessage('Благодарим за подписку!.', 'success');

                // Reset form
                setTimeout(() => {
                    form.reset();
                }, 2000);
            });
        }

        showSimpleMessage(message, type = 'success') {
            const container = document.querySelector('.mkx-newsletter-form');
            if (!container) return;

            // Remove existing messages
            const existingMessage = container.querySelector('.mkx-simple-notification');
            if (existingMessage) {
                existingMessage.remove();
            }

            // Create message element
            const messageEl = document.createElement('div');
            messageEl.className = `mkx-simple-notification mkx-simple-${type}`;
            messageEl.innerHTML = message;

            // Add styles
            messageEl.style.cssText = `
                background: ${type === 'success' ? 'rgba(61, 205, 119, 0.2)' : 'rgba(239, 68, 68, 0.2)'};
                color: #ffffff;
                padding: 1rem;
                border-radius: 0.5rem;
                margin-top: 1rem;
                border: 2px solid ${type === 'success' ? '#3DCD77' : '#EF4444'};
                text-align: center;
                font-size: 0.9rem;
                animation: slideInFromBottom 0.5s ease forwards;
            `;

            // Insert message
            container.appendChild(messageEl);

            // Auto hide after 5 seconds
            setTimeout(() => {
                if (messageEl && messageEl.parentNode) {
                    messageEl.style.opacity = '0';
                    messageEl.style.transform = 'translateY(-20px)';
                    messageEl.style.transition = 'all 0.3s ease';

                    setTimeout(() => {
                        if (messageEl && messageEl.parentNode) {
                            messageEl.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }

        handleSuccess(form) {
            const responseOutput = form.querySelector('.wpcf7-response-output');

            if (responseOutput) {
                // Remove aria-hidden to show message
                responseOutput.removeAttribute('aria-hidden');
                responseOutput.style.display = 'block';

                this.autoHideMessage(responseOutput, 5000);
                responseOutput.classList.add('mkx-notification-success');
            }

            // Reset form after successful submission
            setTimeout(() => {
                this.resetForm(form);
            }, 2000);
        }

        handleError(form, errorType) {
            const responseOutput = form.querySelector('.wpcf7-response-output');

            if (responseOutput) {
                // Remove aria-hidden to show message
                responseOutput.removeAttribute('aria-hidden');
                responseOutput.style.display = 'block';

                let hideDelay = 8000;

                switch (errorType) {
                    case 'spam':
                        hideDelay = 10000;
                        responseOutput.classList.add('mkx-notification-spam');
                        break;
                    case 'mailfailed':
                        hideDelay = 8000;
                        responseOutput.classList.add('mkx-notification-error');
                        break;
                    case 'aborted':
                        hideDelay = 10000;
                        responseOutput.classList.add('mkx-notification-aborted');
                        break;
                }

                this.autoHideMessage(responseOutput, hideDelay);
            }
        }

        handleValidationError(form) {
            const responseOutput = form.querySelector('.wpcf7-response-output');
            const errorTips = form.querySelectorAll('.wpcf7-not-valid-tip');

            if (responseOutput) {
                // Remove aria-hidden to show message
                responseOutput.removeAttribute('aria-hidden');
                responseOutput.style.display = 'block';

                responseOutput.classList.add('mkx-notification-validation-error');
                this.autoHideMessage(responseOutput, 6000);
            }

            errorTips.forEach(tip => {
                tip.style.display = 'block';
                this.autoHideMessage(tip, 6000);
            });
        }

        autoHideMessage(element, delay) {
            // Clear existing timeout for this element
            if (this.timeouts.has(element)) {
                clearTimeout(this.timeouts.get(element));
            }

            const timeoutId = setTimeout(() => {
                if (element && element.parentNode) {
                    element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(-20px)';

                    setTimeout(() => {
                        if (element && element.parentNode) {
                            element.style.display = 'none';
                            element.setAttribute('aria-hidden', 'true');

                            // Reset styles after hiding
                            setTimeout(() => {
                                if (element) {
                                    element.style.opacity = '';
                                    element.style.transform = '';
                                    element.style.transition = '';
                                }
                            }, 100);
                        }
                    }, 300);
                }

                this.timeouts.delete(element);
            }, delay);

            this.timeouts.set(element, timeoutId);
        }

        resetForm(form) {
            const emailField = form.querySelector('.wpcf7-email');
            if (emailField && window.cf7PlaceholderManager) {
                window.cf7PlaceholderManager.resetField(emailField);
            }

            // Remove validation error classes
            const invalidFields = form.querySelectorAll('.wpcf7-not-valid');
            invalidFields.forEach(field => {
                field.classList.remove('wpcf7-not-valid');
            });

            // Hide error messages
            const errorTips = form.querySelectorAll('.wpcf7-not-valid-tip');
            errorTips.forEach(tip => {
                tip.style.display = 'none';
            });

            // Reset form state
            const wpcf7Form = form.querySelector('.wpcf7-form');
            if (wpcf7Form) {
                wpcf7Form.classList.remove('invalid', 'submitting');
                wpcf7Form.setAttribute('data-status', 'init');
            }
        }

        reinitialize() {
            this.attachEventListeners();
        }

        clearTimeouts() {
            this.timeouts.forEach(timeoutId => {
                clearTimeout(timeoutId);
            });
            this.timeouts.clear();
        }
    }

    /**
     * Footer Manager
     * Main class that orchestrates all footer functionality
     */
    class FooterManager {
        constructor() {
            this.placeholderManager = null;
            this.notificationsManager = null;
            this.init();
        }

        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.initializeComponents();
                });
            } else {
                this.initializeComponents();
            }
        }

        initializeComponents() {
            try {
                // Initialize CF7 placeholder management
                this.placeholderManager = new CF7PlaceholderManager();
                window.cf7PlaceholderManager = this.placeholderManager;

                // Initialize CF7 notifications
                this.notificationsManager = new CF7NotificationsManager();
                window.cf7NotificationsManager = this.notificationsManager;

                // Listen for CF7 ready events
                this.attachGlobalListeners();

            } catch (error) {
                console.error('Footer Manager initialization error:', error);
            }
        }

        attachGlobalListeners() {
            // Re-initialize when CF7 forms are dynamically loaded
            document.addEventListener('wpcf7:ready', () => {
                setTimeout(() => {
                    if (this.placeholderManager) {
                        this.placeholderManager.attachEventListeners();
                    }
                    if (this.notificationsManager) {
                        this.notificationsManager.reinitialize();
                    }
                }, 100);
            });

            // Handle page visibility changes
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && this.notificationsManager) {
                    this.notificationsManager.clearTimeouts();
                }
            });
        }

        destroy() {
            if (this.notificationsManager) {
                this.notificationsManager.clearTimeouts();
            }

            // Clean up global references
            if (window.cf7PlaceholderManager) {
                delete window.cf7PlaceholderManager;
            }
            if (window.cf7NotificationsManager) {
                delete window.cf7NotificationsManager;
            }
        }
    }

    /**
     * Initialize Footer Manager
     */
    const footerManager = new FooterManager();

    // Make it globally accessible if needed
    window.footerManager = footerManager;

    /**
     * Cleanup on page unload
     */
    window.addEventListener('beforeunload', () => {
        if (footerManager) {
            footerManager.destroy();
        }
    });

})();