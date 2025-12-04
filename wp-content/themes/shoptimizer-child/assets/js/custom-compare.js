(function($) {
    'use strict';

    const MKXCompareHandler = {
        init: function() {
            this.version = '1.0.0';
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            $(document).on('click', '.mkx-compare-btn', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const productId = $button.data('product-id');
                
                if ($button.hasClass('processing')) {
                    return;
                }
                
                $button.addClass('processing');
                
                const isAdded = $button.hasClass('added');
                const action = isAdded ? 'mkx_compare_remove' : 'mkx_compare_add';
                
                $.ajax({
                    url: mkxCompare.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: action,
                        nonce: mkxCompare.nonce,
                        product_id: productId
                    },
                    success: function(response) {
                        $button.removeClass('processing');
                        
                        if (response.success) {
                            if (isAdded) {
                                $button.removeClass('added');
                                $button.attr('title', 'Добавить к сравнению');
                                $button.find('span').text('Добавить к сравнению');
                                $(document.body).trigger('mkx_removed_from_compare', [productId, response.data.count]);
                            } else {
                                $button.addClass('added');
                                $button.attr('title', 'Удалить из сравнения');
                                $button.find('span').text('Удалить из сравнения');
                                $(document.body).trigger('mkx_added_to_compare', [productId, response.data.count]);
                            }
                        } else if (response.data && response.data.limit_reached) {
                            $(document.body).trigger('mkx_compare_limit_reached', [mkxCompare.limitMessage]);
                        }
                    },
                    error: function() {
                        $button.removeClass('processing');
                    }
                });
            });

            $(document).on('click', '.mkx-compare-remove', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const productId = $button.data('product-id');
                const $cell = $button.closest('td, .mkx-compare-product-cell');
                
                if ($button.hasClass('processing')) {
                    return;
                }
                
                $button.addClass('processing');
                
                $.ajax({
                    url: mkxCompare.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'mkx_compare_remove',
                        nonce: mkxCompare.nonce,
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            const columnIndex = $cell.index();
                            
                            $('.mkx-compare-table tr').each(function() {
                                $(this).find('td, th').eq(columnIndex).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                            
                            setTimeout(function() {
                                if ($('.mkx-compare-table tbody tr:first td').length === 0) {
                                    location.reload();
                                }
                            }, 400);
                            
                            $(document.body).trigger('mkx_removed_from_compare', [productId, response.data.count]);
                        }
                        $button.removeClass('processing');
                    },
                    error: function() {
                        $button.removeClass('processing');
                    }
                });
            });

            $(document).on('click', '.mkx-compare-clear-all', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                
                if ($button.hasClass('processing')) {
                    return;
                }
                
                if (!confirm('Вы уверены, что хотите очистить список сравнения?')) {
                    return;
                }
                
                $button.addClass('processing');
                
                $.ajax({
                    url: mkxCompare.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'mkx_compare_clear',
                        nonce: mkxCompare.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $(document.body).trigger('mkx_compare_cleared', [0]);
                            location.reload();
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
        MKXCompareHandler.init();
    });

})(jQuery);
