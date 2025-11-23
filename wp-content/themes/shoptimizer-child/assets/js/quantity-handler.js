// Ensure this script only runs once.
if (!window.mkxQuantityHandlerInitialized) {
    window.mkxQuantityHandlerInitialized = true;

    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            // Handle plus/minus button clicks within a .products grid
            if (e.target.closest('.products') && e.target.matches('.quantity .plus, .quantity .minus')) {
                e.preventDefault();
                e.stopPropagation(); // Stop the event from bubbling up.

                const quantityWrapper = e.target.closest('.quantity');
                if (!quantityWrapper) return;

                const input = quantityWrapper.querySelector('.qty');
                if (!input) return;

                const step = parseInt(input.step, 10) || 1;
                const min = parseInt(input.min, 10) || 0;
                const max = input.max ? parseInt(input.max, 10) : Infinity;
                let currentValue = parseInt(input.value, 10) || 0;

                if (e.target.matches('.plus')) {
                    currentValue = Math.min(currentValue + step, max);
                } else {
                    currentValue = Math.max(currentValue - step, min);
                }

                input.value = currentValue;

                // Trigger a 'change' event to let other scripts (like WooCommerce) know the value has changed.
                const changeEvent = new Event('change', { bubbles: true });
                input.dispatchEvent(changeEvent);

                // Update the data-quantity attribute on the add-to-cart button
                const actionsWrapper = e.target.closest('.mkx-product-actions');
                if (actionsWrapper) {
                    const addToCartButton = actionsWrapper.querySelector('.ajax_add_to_cart');
                    if (addToCartButton) {
                        addToCartButton.dataset.quantity = currentValue;
                    }
                }
            }
        }, true); // Use capture phase to catch the event early.
    });
}
