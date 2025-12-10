(() => {
    if (!document.body.classList.contains('woocommerce-checkout')) return;

    const phone = document.querySelector('#billing_phone');
    if (!phone) return;

    const format = (value) => {
        let digits = value.replace(/\D/g, '');
        if (digits.startsWith('7')) {
            digits = digits.slice(1);
        }

        let out = '+7(';
        out += digits.slice(0, 3);
        if (digits.length >= 3) {
            out += ') ' + digits.slice(3, 6);
        }
        if (digits.length >= 6) {
            out += '-' + digits.slice(6, 8);
        }
        if (digits.length >= 8) {
            out += '-' + digits.slice(8, 10);
        }
        return out;
    };

    const setCursorToEnd = (el) => {
        const len = el.value.length;
        el.setSelectionRange(len, len);
    };

    phone.addEventListener('focus', () => {
        if (!phone.value) {
            phone.value = '+7(';
        }
        setTimeout(() => setCursorToEnd(phone), 0);
    });

    phone.addEventListener('input', () => {
        const formatted = format(phone.value);
        phone.value = formatted;
        setCursorToEnd(phone);
    });

    phone.addEventListener('blur', () => {
        if (phone.value === '+7(' || phone.value === '+7') {
            phone.value = '';
        }
    });
})();

