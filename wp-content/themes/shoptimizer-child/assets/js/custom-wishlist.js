(function($) {
    'use strict';

    const MKXWishlistHandler = {
        init: function() {
            this.version = '1.0.0';
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            $(document).on('click', '.mkx-wishlist-btn', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const productId = $button.data('product-id');
                
                if ($button.hasClass('processing')) {
                    return;
                }
                
                $button.addClass('processing');
                
                const isAdded = $button.hasClass('added');
                const action = isAdded ? 'mkx_wishlist_remove' : 'mkx_wishlist_add';
                
                $.ajax({
                    url: mkxWishlist.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: action,
                        nonce: mkxWishlist.nonce,
                        product_id: productId
                    },
                    success: function(response) {
                        $button.removeClass('processing');
                        
                        if (response.success) {
                            if (isAdded) {
                                $button.removeClass('added');
                                $button.attr('title', 'Добавить в избранное');
                                $button.find('span').text('Добавить в избранное');
                                $(document.body).trigger('mkx_removed_from_wishlist', [productId, response.data.count]);
                            } else {
                                $button.addClass('added');
                                $button.attr('title', 'Удалить из избранного');
                                $button.find('span').text('Удалить из избранного');
                                $(document.body).trigger('mkx_added_to_wishlist', [productId, response.data.count]);
                            }
                        }
                    },
                    error: function() {
                        $button.removeClass('processing');
                    }
                });
            });

            $(document).on('click', '.mkx-wishlist-remove', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const productId = $button.data('product-id');
                const $item = $button.closest('.mkx-wishlist-item');
                
                if ($button.hasClass('processing')) {
                    return;
                }
                
                $button.addClass('processing');
                
                $.ajax({
                    url: mkxWishlist.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'mkx_wishlist_remove',
                        nonce: mkxWishlist.nonce,
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            $item.fadeOut(300, function() {
                                $item.remove();
                                
                                if ($('.mkx-wishlist-list .mkx-wishlist-item').length === 0) {
                                    location.reload();
                                }
                            });
                            
                            $(document.body).trigger('mkx_removed_from_wishlist', [productId, response.data.count]);
                        }
                        $button.removeClass('processing');
                    },
                    error: function() {
                        $button.removeClass('processing');
                    }
                });
            });
        }
    };

    $(document).ready(function() {
        MKXWishlistHandler.init();
    });

})(jQuery);
