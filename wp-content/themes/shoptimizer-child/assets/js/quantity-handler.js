// Ensure this script only runs once.
if (!window.mkxQuantityHandlerInitialized) {
    window.mkxQuantityHandlerInitialized = true;

    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            if (e.target.matches('.quantity .plus, .quantity .minus')) {
                e.preventDefault();
                e.stopPropagation();

                const quantityWrapper = e.target.closest('.quantity');
                if (!quantityWrapper) return;

                const input = quantityWrapper.querySelector('.qty, input[type="number"]');
                if (!input) return;

                const step = parseInt(input.step, 10) || 1;
                const min = parseInt(input.min, 10) || 1;
                const max = input.max ? parseInt(input.max, 10) : Infinity;
                let currentValue = parseInt(input.value, 10) || min;

                if (e.target.matches('.plus')) {
                    currentValue = Math.min(currentValue + step, max);
                } else {
                    currentValue = Math.max(currentValue - step, min);
                }

                input.value = currentValue;

                const changeEvent = new Event('change', { bubbles: true });
                input.dispatchEvent(changeEvent);

                const productItem = e.target.closest('li.product, .mkz-product-list-item');
                if (productItem) {
                    const addToCartButtons = productItem.querySelectorAll('.ajax_add_to_cart, .add_to_cart_button');
                    addToCartButtons.forEach(button => {
                        button.dataset.quantity = currentValue;
                    });
                }
            }
        }, true);
    });
}
