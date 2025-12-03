(function($) {
    'use strict';

    const MKXBadges = {
        init: function() {
            this.bindEvents();
            this.updateBadges();
        },

        bindEvents: function() {
            const self = this;

            $(document.body).on('updated_cart_totals added_to_cart removed_from_cart', function() {
                self.updateBadges();
            });

            $(document).on('added_to_wishlist removed_from_wishlist', function() {
                self.updateBadges();
            });

            $(document).on('yith_woocompare_product_added yith_woocompare_product_removed', function() {
                self.updateBadges();
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
                        MKXBadges.updateBadge('a[href*="wishlist"]', response.data.wishlist, 'mkx-wishlist-count');
                        MKXBadges.updateBadge('a[href*="compare"]', response.data.compare, 'mkx-compare-count');
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
