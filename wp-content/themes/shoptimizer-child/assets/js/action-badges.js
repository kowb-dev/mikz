(function($) {
    'use strict';

    const MKXBadges = {
        init: function() {
            this.version = '1.5.0';
            this.bindEvents();
            this.updateBadges();
            this.observePageChanges();
        },

        bindEvents: function() {
            const self = this;

            $(document.body).on('updated_cart_totals added_to_cart removed_from_cart', function() {
                self.updateBadges();
            });

            $(document.body).on('mkx_added_to_wishlist mkx_removed_from_wishlist', function() {
                setTimeout(() => self.updateBadges(), 100);
            });

            $(document.body).on('mkx_added_to_compare mkx_removed_from_compare mkx_compare_cleared', function() {
                setTimeout(() => self.updateBadges(), 100);
            });

            $(document.body).on('click', '.woocommerce-cart .remove, .cart_item .remove', function(e) {
                setTimeout(() => self.updateBadges(), 100);
                setTimeout(() => self.updateBadges(), 500);
            });

            $(document).on('wc_fragments_refreshed wc_fragments_loaded', function(e, data) {
                self.updateBadges();
            });

            $(document.body).on('wc_fragment_refresh', function() {
                setTimeout(() => self.updateBadges(), 100);
            });

            $(document.body).on('updated_wc_div', function() {
                setTimeout(() => self.updateBadges(), 100);
            });

            $(document.body).on('wc_cart_button_updated', function() {
                setTimeout(() => self.updateBadges(), 100);
            });
        },

        observePageChanges: function() {
            const self = this;
            
            if ($('body.woocommerce-cart').length) {
                const cartObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                            setTimeout(() => self.updateBadges(), 300);
                        }
                    });
                });

                const cartTable = document.querySelector('.woocommerce-cart-form__contents');
                if (cartTable) {
                    cartObserver.observe(cartTable, { childList: true, subtree: true });
                }
            }

            if ($('.mkx-compare-page').length) {
                const compareObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList') {
                            setTimeout(() => self.updateBadges(), 300);
                        }
                    });
                });

                const compareTable = document.querySelector('.mkx-compare-table');
                if (compareTable) {
                    compareObserver.observe(compareTable, { 
                        childList: true, 
                        subtree: true
                    });
                }
            }

            if ($('.mkx-wishlist-page').length) {
                const wishlistObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                            setTimeout(() => self.updateBadges(), 300);
                        }
                    });
                });

                const wishlistTable = document.querySelector('.mkx-wishlist-table');
                if (wishlistTable) {
                    wishlistObserver.observe(wishlistTable, { childList: true, subtree: true });
                }
            }
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
                        MKXBadges.updateMobileBadge('.mkx-mobile-nav-item[href*="cart"]', response.data.cart, 'mkx-mobile-nav-cart-count');
                        MKXBadges.updateMobileBadge('.mkx-mobile-nav-item[href*="wishlist"]', response.data.wishlist, 'mkx-mobile-nav-wishlist-count');
                        MKXBadges.updateMobileBadge('.mkx-mobile-nav-item[href*="compare"]', response.data.compare, 'mkx-mobile-nav-compare-count');
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
        },

        updateMobileBadge: function(parentSelector, count, badgeClass) {
            const $parents = $(parentSelector);
            
            $parents.each(function() {
                const $parent = $(this);
                let $badge = $parent.find(`.${badgeClass}`);

                if (count > 0) {
                    if ($badge.length === 0) {
                        $badge = $('<span>', {
                            class: `${badgeClass} mkx-badge-visible`,
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
