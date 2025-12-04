(function($) {
    'use strict';

    const MKXBadges = {
        init: function() {
            this.version = '1.0.8';
            this.bindEvents();
            this.updateBadges();
        },

        bindEvents: function() {
            const self = this;

            // WooCommerce Cart Events
            $(document.body).on('updated_cart_totals added_to_cart removed_from_cart', function() {
                self.updateBadges();
            });

            // --- Wishlist Click Handlers ---
            $(document.body).on('click', '.yith-wcwl-add-to-wishlist a, a.add_to_wishlist', function() {
                setTimeout(() => self.updateBadges(), 1200);
            });
            $(document.body).on('click', 'a.remove_from_wishlist', function() {
                setTimeout(() => self.updateBadges(), 1200);
            });

            // --- Compare Click Handlers ---
            $(document.body).on('click', 'a.compare', function() {
                setTimeout(() => self.updateBadges(), 1200);
            });
            $(document.body).on('click', 'a.remove', function() {
                if ($(this).closest('#yith-woocompare').length) {
                    setTimeout(() => self.updateBadges(), 1200);
                }
            });
        },

        updateBadges: function() {
            $.ajax({
                url: mkxBadges.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mkx_get_badge_counts',
                    nonce: mkxBadges.nonce
                },
                success: function(response) {
                    if (response.success) {
                        MKXBadges.updateBadge('.mkx-cart-contents', response.data.cart, 'mkx-cart-count');
                        MKXBadges.updateBadge('.mkx-action-links a[href*="wishlist"]', response.data.wishlist, 'mkx-wishlist-count');
                        MKXBadges.updateBadge('.mkx-action-links a[href*="compare"]', response.data.compare, 'mkx-compare-count');
                    }
                }
            });
        },

        updateBadge: function(parentSelector, count, badgeClass) {
            const $parents = $(parentSelector);
            
            $parents.each(function() {
                const $parent = $(this);
                let $badge = $parent.find(`.${badgeClass}`);

                if (count > 0) {
                    if ($badge.length === 0) {
                        $badge = $('<span>', {
                            class: `mkx-badge-count ${badgeClass} mkx-badge-visible`,
                            text: count
                        });
                        $parent.append($badge);
                    } else {
                        $badge.text(count);
                        
                        if (!$badge.hasClass('mkx-badge-visible')) {
                            $badge.addClass('mkx-badge-visible');
                        }
                    }
                } else {
                    if ($badge.length > 0) {
                        $badge.removeClass('mkx-badge-visible');
                        setTimeout(() => {
                            $badge.remove();
                        }, 300);
                    }
                }
            });
        }
    };

    $(document).ready(function() {
        MKXBadges.init();
    });

})(jQuery);
