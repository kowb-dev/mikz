(function($) {
    'use strict';

    const MKXNotifications = {
        container: null,
        duration: 5000,

        init: function() {
            this.container = $('#mkx-notification-container');
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            $(document.body).on('added_to_cart', function(e, fragments, cart_hash, $button) {
                const $productItem = $button ? $button.closest('.product, li.product, .mkz-product-list-item') : null;
                let productName = 'Товар';
                
                if ($productItem && $productItem.length) {
                    const $title = $productItem.find('.woocommerce-loop-product__title, .product_title, .mkz-product-list-item__title-link');
                    if ($title.length) {
                        productName = $title.text().trim();
                    }
                }
                
                if ($button && $button.data('product_name')) {
                    productName = $button.data('product_name');
                }
                
                self.show('cart', mkxNotifications.addedToCart, productName);
            });

            $(document).on('added_to_wishlist', function(e) {
                const $target = $(e.target).closest('.product, li.product, .mkz-product-list-item');
                const productName = $target.find('.woocommerce-loop-product__title, .product_title, .mkz-product-list-item__title-link').text().trim() || 'Товар';
                self.show('wishlist', mkxNotifications.addedToWishlist, productName);
            });

            $(document).on('removed_from_wishlist', function(e) {
                self.show('wishlist', mkxNotifications.removedFromWishlist, 'Товар');
            });

            $(document).on('yith_woocompare_product_added', function(e) {
                self.show('compare', mkxNotifications.addedToCompare, 'Товар');
            });

            $(document).on('click', '.mkx-notification-close', function() {
                self.close($(this).closest('.mkx-notification'));
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
