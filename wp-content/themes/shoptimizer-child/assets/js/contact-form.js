document.addEventListener('DOMContentLoaded', function () {
    // --- Floating Label Logic ---
    const formGroups = document.querySelectorAll('.mkz-floating-form .form-group');

    formGroups.forEach(group => {
        const input = group.querySelector('.wpcf7-form-control');
        if (!input) return;

        const updateState = () => {
            if (input.value.trim() !== '') {
                group.classList.add('has-value');
            } else {
                group.classList.remove('has-value');
            }
        };

        input.addEventListener('focus', () => group.classList.add('is-focused'));
        input.addEventListener('blur', () => {
            group.classList.remove('is-focused');
            updateState(); // Re-check value on blur
        });

        // Initial check for autofill or pre-filled values
        updateState();
    });

    // --- Input Mask Logic ---
    const phoneField = document.querySelector('.mkz-floating-form input[name="your-tel"]');
    if (phoneField) {
        const interval = setInterval(() => {
            if (typeof IMask !== 'undefined') {
                clearInterval(interval);
                const phoneMask = IMask(phoneField, {
                    mask: '+{7} (000) 000-00-00',
                    lazy: true  // Start lazy
                });

                phoneField.addEventListener('focus', function() {
                    // On focus, make the mask non-lazy to show the placeholder
                    phoneMask.updateOptions({ lazy: false });
                });

                phoneField.addEventListener('blur', function() {
                    // On blur, if the input is empty, make it lazy again and clear the value
                    if (phoneMask.unmaskedValue === '') {
                        phoneMask.updateOptions({ lazy: true });
                        phoneMask.value = '';
                    }
                });
            }
        }, 100);
    }
});
