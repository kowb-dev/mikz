(function($) {
    'use strict';

    const MKXBadges = {
        init: function() {
            this.version = '1.4.0';
            this.bindEvents();
            this.updateBadges();
            this.observePageChanges();
        },

        bindEvents: function() {
            const self = this;

            $(document.body).on('updated_cart_totals added_to_cart removed_from_cart', function() {
                self.updateBadges();
            });

            $(document.body).on('added_to_wishlist', function() {
                setTimeout(() => self.updateBadges(), 100);
                setTimeout(() => self.updateBadges(), 500);
            });

            $(document.body).on('removed_from_wishlist', function() {
                setTimeout(() => self.updateBadges(), 100);
                setTimeout(() => self.updateBadges(), 500);
            });

            $(document.body).on('yith_woocompare_product_added', function() {
                setTimeout(() => self.updateBadges(), 100);
                setTimeout(() => self.updateBadges(), 500);
            });

            $(document.body).on('yith_woocompare_product_removed', function() {
                setTimeout(() => self.updateBadges(), 100);
                setTimeout(() => self.updateBadges(), 500);
            });

            $(document.body).on('click', '.yith-wcwl-add-to-wishlist a, a.add_to_wishlist', function() {
                setTimeout(() => self.updateBadges(), 600);
                setTimeout(() => self.updateBadges(), 1200);
            });

            $(document.body).on('click', 'a.remove_from_wishlist', function() {
                setTimeout(() => self.updateBadges(), 600);
            });

            $(document.body).on('click', 'a.compare', function() {
                setTimeout(() => self.updateBadges(), 600);
            });

            $(document.body).on('click', 'a.clear_all, .yith-woocompare-widget a.clear-all', function() {
                setTimeout(() => self.updateBadges(), 500);
            });

            $(document.body).on('click', '.woocommerce-cart .remove, .cart_item .remove, a.remove', function(e) {
                setTimeout(() => self.updateBadges(), 100);
                setTimeout(() => self.updateBadges(), 500);
                setTimeout(() => self.updateBadges(), 1000);
            });

            $(document.body).on('click', '#yith-woocompare .remove, table.compare-list .remove', function() {
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
                setTimeout(() => self.updateBadges(), 500);
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

            if ($('body.woocommerce-compare, #yith-woocompare').length) {
                const compareObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList' || mutation.type === 'attributes') {
                            setTimeout(() => self.updateBadges(), 300);
                        }
                    });
                });

                const compareTable = document.querySelector('#yith-woocompare table.compare-list, .yith-woocompare-table');
                if (compareTable) {
                    compareObserver.observe(compareTable, { 
                        childList: true, 
                        subtree: true,
                        attributes: true
                    });
                }
            }

            if ($('body.woocommerce-wishlist, .wishlist_table').length) {
                const wishlistObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                            setTimeout(() => self.updateBadges(), 300);
                        }
                    });
                });

                const wishlistTable = document.querySelector('.wishlist_table, .shop_table.wishlist_table');
                if (wishlistTable) {
                    wishlistObserver.observe(wishlistTable, { childList: true, subtree: true });
                }
            }

            const cookieCheck = setInterval(function() {
                const currentCompare = document.cookie.match(/yith_woocompare_list=([^;]+)/);
                if (self.lastCompareCookie !== (currentCompare ? currentCompare[1] : '')) {
                    self.lastCompareCookie = currentCompare ? currentCompare[1] : '';
                    self.updateBadges();
                }
            }, 1000);
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
