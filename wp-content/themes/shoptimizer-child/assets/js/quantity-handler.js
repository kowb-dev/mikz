(function($) {
    'use strict';

    if (window.mkxQuantityHandlerInitialized) return;
    window.mkxQuantityHandlerInitialized = true;

    /**
     * Quantity buttons handler (+/-)
     */
    $(document.body).on('click', '.quantity .plus, .quantity .minus', function(e) {
        e.preventDefault();
        
        var $qty = $(this).closest('.quantity').find('.qty');
        var currentVal = parseFloat($qty.val());
        var max = parseFloat($qty.attr('max'));
        var min = parseFloat($qty.attr('min'));
        var step = $qty.attr('step');

        if (!currentVal || currentVal === '' || currentVal === 'NaN') currentVal = 0;
        if (max === '' || max === 'NaN') max = '';
        if (min === '' || min === 'NaN') min = 0;
        if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN') step = 1;

        if ($(this).is('.plus')) {
            if (max && (max == currentVal || currentVal > max)) {
                $qty.val(max);
            } else {
                $qty.val(currentVal + parseFloat(step));
            }
        } else {
            if (min && (min == currentVal || currentVal < min)) {
                $qty.val(min);
            } else if (currentVal > 0) {
                $qty.val(currentVal - parseFloat(step));
            }
        }

        $qty.trigger('change');
        
        // Update data-quantity on buttons (legacy/archive support)
        var $product = $(this).closest('.product, .mkz-product-list-item, .product-type-simple');
        if ($product.length) {
             $product.find('.ajax_add_to_cart, .add_to_cart_button').attr('data-quantity', $qty.val());
        }
    });

    /**
     * Single Product AJAX Add to Cart
     * Replaces standard form submission with AJAX to prevent reloads and resubmission warnings.
     */
    $(document).on('click', '.single_add_to_cart_button', function(e) {
        var $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart');

        if ($form.length === 0 || $thisbutton.hasClass('disabled') || $thisbutton.hasClass('wc-variation-selection-needed')) {
            return;
        }

        e.preventDefault();

        $thisbutton.removeClass('added').addClass('loading');

        var formData = $form.serializeArray();
        var productId = $thisbutton.data('product_id') || $form.find('input[name="product_id"]').val() || $thisbutton.val() || $form.find('input[name="add-to-cart"]').val();
        
        // Ensure required fields are present in data
        var hasAddToCart = false;
        var hasProductId = false;
        $.each(formData, function(i, field) {
            if (field.name === 'add-to-cart') hasAddToCart = true;
            if (field.name === 'product_id') hasProductId = true;
        });

        if (!hasAddToCart) {
            if ($thisbutton.val()) {
                formData.push({name: 'add-to-cart', value: $thisbutton.val()});
            } else {
                var $addToCartInput = $form.find('input[name="add-to-cart"]');
                if($addToCartInput.length) {
                    formData.push({name: 'add-to-cart', value: $addToCartInput.val()});
                }
            }
        }

        if (!hasProductId && productId) {
            formData.push({name: 'product_id', value: productId});
        }

        // Determine AJAX URL
        var ajaxUrl = '/?wc-ajax=add_to_cart';
        if (typeof wc_add_to_cart_params !== 'undefined') {
            ajaxUrl = wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart');
        }

        $.ajax({
            url: ajaxUrl,
            data: formData,
            type: 'POST',
            success: function(response) {
                if (!response) {
                    $thisbutton.removeClass('loading');
                    $form[0].submit();
                    return;
                }

                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }

                // Redirect support
                if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.cart_redirect_after_add === 'yes') {
                    window.location = wc_add_to_cart_params.cart_url;
                    return;
                }

                $thisbutton.removeClass('loading').addClass('added');

                // Trigger fragments update
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                
                // Trigger fragment refresh explicitly
                $(document.body).trigger('wc_fragment_refresh');
            },
            error: function() {
                $thisbutton.removeClass('loading');
                // Fallback to native submit if AJAX fails
                $form[0].submit();
            }
        });
    });

})(jQuery);
