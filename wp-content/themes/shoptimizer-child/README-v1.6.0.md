# Shoptimizer Child Theme v1.6.0

## Что нового

Версия 1.6.0 включает полную мобильную адаптацию всех страниц WooCommerce и улучшенные системы Wishlist/Compare.

### Кастомные страницы

**My Account** - Современная форма входа/регистрации с поддержкой телефона  
**Cart** - Адаптивная корзина с карточным видом на мобильных  
**Checkout** - Оптимизированная форма оформления заказа  

### Wishlist & Compare

- Wishlist: 2-колоночный grid на мобильных
- Compare: горизонтальная прокрутка только товаров, кнопка "В корзину"
- Кнопки на карточках: всегда видны на мобильных, hover на десктопе

### Установка

Все модули подключаются автоматически через `functions.php`. Стили для cart/checkout загружаются условно.

### Требования

- WordPress 5.8+
- WooCommerce 7.0+
- Shoptimizer Theme (parent)
- PHP 7.4+

## Структура файлов

```
shoptimizer-child/
├── woocommerce/
│   ├── myaccount/form-login.php
│   ├── cart/ (использует плагиновый шаблон + CSS)
│   └── checkout/ (использует плагиновый шаблон + CSS)
├── assets/
│   ├── css/
│   │   ├── custom-account.css
│   │   ├── custom-cart.css
│   │   ├── custom-checkout.css
│   │   ├── custom-wishlist.css (обновлен)
│   │   ├── custom-compare.css (обновлен)
│   │   ├── shop-list-view.css (обновлен)
│   │   ├── responsive-mobile.css (обновлен)
│   │   └── woo_single.css (обновлен)
│   └── js/
│       └── custom-account.js
├── inc/
│   ├── features/
│   │   ├── custom-account.php (новый)
│   │   ├── custom-wishlist.php (v1.6.0)
│   │   └── custom-compare.php (v1.6.0)
│   └── core/
│       └── enqueue.php (v1.6.0)
└── functions.php (v1.6.0)
```

## Автор

**KB** - https://kowb.ru
