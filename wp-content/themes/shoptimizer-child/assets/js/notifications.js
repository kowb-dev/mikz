(function($) {
    'use strict';

    const MKXNotifications = {
        container: null,
        duration: 5000,
        productNameCache: {},
        notificationDebounce: {},

        init: function() {
            this.version = '1.5.0';
            this.container = $('#mkx-notification-container');
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            $(document.body).on('added_to_cart', function(e, fragments, cart_hash, $button) {
                if (!$button) {
                    return;
                }
                
                const productId = $button.data('product_id') || $button.val();
                const key = 'cart_' + productId;
                
                if (self.notificationDebounce[key]) {
                    return;
                }
                
                self.notificationDebounce[key] = true;
                setTimeout(() => delete self.notificationDebounce[key], 1000);
                
                self.getProductName(productId, function(productName) {
                    self.show('cart', mkxNotifications.addedToCart, productName);
                });
            });

            $(document.body).on('mkx_added_to_wishlist', function(e, productId, count) {
                const key = 'wishlist_' + productId;
                if (self.notificationDebounce[key]) {
                    return;
                }
                
                self.notificationDebounce[key] = true;
                setTimeout(() => delete self.notificationDebounce[key], 1500);
                
                self.getProductName(productId, function(productName) {
                    self.show('wishlist', mkxNotifications.addedToWishlist, productName);
                });
            });

            $(document.body).on('mkx_removed_from_wishlist', function(e, productId, count) {
                self.getProductName(productId, function(productName) {
                    self.show('wishlist', mkxNotifications.removedFromWishlist, productName);
                });
            });

            $(document.body).on('mkx_added_to_compare', function(e, productId, count) {
                const key = 'compare_' + productId;
                if (self.notificationDebounce[key]) {
                    return;
                }
                
                self.notificationDebounce[key] = true;
                setTimeout(() => delete self.notificationDebounce[key], 1500);
                
                self.getProductName(productId, function(productName) {
                    self.show('compare', mkxNotifications.addedToCompare, productName);
                });
            });

            $(document.body).on('mkx_removed_from_compare', function(e, productId, count) {
                self.getProductName(productId, function(productName) {
                    self.show('compare', mkxNotifications.removedFromCompare, productName);
                });
            });

            $(document.body).on('mkx_compare_limit_reached', function(e, message) {
                self.show('compare', 'Внимание', message);
            });

            $(document).on('click', '.woocommerce-cart-form .remove, .cart_item .product-remove a', function(e) {
                const $link = $(this);
                const $cartItem = $link.closest('.cart_item, tr');
                const productName = $cartItem.find('.product-name a, td.product-name a').text().trim();
                
                setTimeout(function() {
                    if (productName) {
                        self.show('cart', mkxNotifications.removedFromCart, productName);
                    }
                }, 100);
            });

            $(document).on('click', '.mkx-notification-close', function() {
                self.close($(this).closest('.mkx-notification'));
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

            const $productItem = $(`[data-product_id="${productId}"], [data-product-id="${productId}"]`).closest('.product, li.type-product');
            if ($productItem.length) {
                const productName = $productItem.find('.woocommerce-loop-product__title, h2.woocommerce-loop-product__title, .product_title').first().text().trim();
                if (productName) {
                    self.productNameCache[productId] = productName;
                    callback(productName);
                    return;
                }
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

            const actions = {
                cart: `<a class="mkx-notification-cta" href="/cart">${mkxNotifications.cartCta || 'К КОРЗИНЕ'}</a>`,
                wishlist: '',
                compare: ''
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
                        ${actions[type] || ''}
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