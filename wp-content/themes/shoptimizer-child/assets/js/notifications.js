(function($) {
    'use strict';

    const MKXNotifications = {
        container: null,
        duration: 5000,
        productNameCache: {},

        init: function() {
            this.version = '1.0.5';
            this.container = $('#mkx-notification-container');
            this.bindEvents();
            this.observeActionButtons();
        },

        observeActionButtons: function() {
            const self = this;
            
            const observerCallback = function(mutations, button, iconClass) {
                const $button = $(button);
                if ($button.hasClass('added') && $button.find('i').length === 0) {
                    $button.html(`<i class="${iconClass}"></i>`);
                }
            };

            // Observer for Compare buttons
            document.querySelectorAll('a.compare').forEach(button => {
                const observer = new MutationObserver(mutations => observerCallback(mutations, button, 'ph ph-chart-bar'));
                observer.observe(button, { childList: true });
            });

            // Observer for Wishlist buttons
            document.querySelectorAll('.yith-wcwl-add-to-wishlist-btn').forEach(button => {
                const observer = new MutationObserver(mutations => observerCallback(mutations, button, 'ph ph-heart'));
                observer.observe(button, { childList: true });
            });
        },

        bindEvents: function() {
            const self = this;

            $(document.body).on('added_to_cart', function(e, fragments, cart_hash, $button) {
                const productId = $button.data('product_id');
                self.getProductName(productId, function(productName) {
                    self.show('cart', mkxNotifications.addedToCart, productName);
                });
            });

            $(document.body).on('added_to_wishlist', function(e, el) {
                const $button = $(el);
                const productId = $button.data('product-id');
                self.getProductName(productId, function(productName) {
                    self.show('wishlist', mkxNotifications.addedToWishlist, productName);
                });
            });

            $(document.body).on('removed_from_wishlist', function() {
                self.show('wishlist', mkxNotifications.removedFromWishlist, 'Товар');
            });

            $(document.body).on('yith_woocompare_product_added', function(e, el) {
                const $button = $(el);
                const productId = $button.data('product_id');
                self.getProductName(productId, function(productName) {
                    self.show('compare', mkxNotifications.addedToCompare, productName);
                });
            });

            $(document).on('click', '.mkx-notification-close', function() {
                self.close($(this).closest('.mkx-notification'));
            });

            // Re-observe buttons after AJAX content is loaded (e.g., pagination)
            $(document.body).on('post-load', function() {
                self.observeActionButtons();
            });
        },

        getProductName: function(productId, callback) {
            const self = this;

            if (!productId) {
                callback('Товар');
                return;
            }

            if (self.productNameCache[productId]) {
                callback(self.productNameCache[productId]);
                return;
            }

            $.ajax({
                url: mkxNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: mkxNotifications.getProductNameAction,
                    nonce: mkxNotifications.nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        self.productNameCache[productId] = response.data.product_name;
                        callback(response.data.product_name);
                    } else {
                        callback('Товар');
                    }
                },
                error: function() {
                    callback('Товар');
                }
            });
        },

        show: function(type, title, productName) {
            const icons = {
                cart: 'ph-shopping-cart',
                wishlist: 'ph-heart',
                compare: 'ph-chart-bar'
            };

            const notification = $('<div>', {
                class: `mkx-notification mkx-notification-${type}`,
                html: `
                    <div class="mkx-notification-icon">
                        <i class="ph ${icons[type]}" aria-hidden="true"></i>
                    </div>
                    <div class="mkx-notification-content">
                        <p class="mkx-notification-title">${title}</p>
                        <p class="mkx-notification-message">${productName}</p>
                    </div>
                    <button type="button" class="mkx-notification-close" aria-label="Закрыть">
                        <i class="ph ph-x" aria-hidden="true"></i>
                    </button>
                `
            });

            this.container.append(notification);

            notification.find('.mkx-notification-close').on('click', () => {
                this.close(notification);
            });

            setTimeout(() => {
                this.close(notification);
            }, this.duration);
        },

        close: function(notification) {
            notification.addClass('mkx-notification-removing');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    };

    $(document).ready(function() {
        MKXNotifications.init();
    });

})(jQuery);