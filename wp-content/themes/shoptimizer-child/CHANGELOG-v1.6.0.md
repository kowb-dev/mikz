# Changelog v1.6.0 - Полная адаптация под мобильные устройства

## Кастомные страницы WooCommerce

### My Account (/my-account)
- ✅ Кастомная форма входа/регистрации с переключением вкладок
- ✅ Поле номера телефона при регистрации
- ✅ Валидация и сохранение телефона
- ✅ Адаптивный дизайн mobile-first

**Файлы:**
- `woocommerce/myaccount/form-login.php` - шаблон формы
- `assets/css/custom-account.css` - стили
- `assets/js/custom-account.js` - переключение вкладок и валидация
- `inc/features/custom-account.php` - обработка регистрации

### Cart (/cart)
- ✅ Адаптивная таблица корзины
- ✅ Карточный вид на мобильных устройствах
- ✅ Улучшенные стили итогов заказа

**Файлы:**
- `assets/css/custom-cart.css` - стили корзины

### Checkout (/checkout)
- ✅ Двухколоночная форма оформления на десктопе
- ✅ Одноколоночная на мобильных
- ✅ Улучшенная валидация полей
- ✅ Стилизация способов оплаты

**Файлы:**
- `assets/css/custom-checkout.css` - стили оформления

## Wishlist & Compare - Мобильная адаптация

### Wishlist (/wishlist)
- ✅ Отображение в 2 колонки на мобильных (grid)
- ✅ Адаптивные карточки товаров
- ✅ Оптимизация изображений

### Compare (/compare)
- ✅ Горизонтальная прокрутка только для товаров (не всей страницы)
- ✅ Sticky заголовки столбцов
- ✅ Кнопка "В корзину" с иконкой cart (без текста)
- ✅ Адаптивная таблица сравнения

**Файлы:**
- `inc/features/custom-compare.php` v1.6.0 - обновлена структура таблицы
- `inc/features/custom-wishlist.php` v1.6.0
- `assets/css/custom-compare.css` - добавлены стили кнопки корзины
- `assets/css/custom-wishlist.css` - 2-колоночный grid на мобильных

## Кнопки Wishlist/Compare на карточках товаров

### Shop Page (/shop)
- ✅ Восстановлены кнопки Wishlist/Compare в list view
- ✅ На мобильных - всегда видны (position: absolute, top-right)
- ✅ На десктопе - появляются при наведении (hover)
- ✅ Обновлен markup: заменен `.mkz-product-list-item__yith-buttons` на `.mkz-product-list-item__action-buttons`

**Файлы:**
- `woocommerce/content-product-list.php` v1.6.0 - обновлен блок кнопок
- `assets/css/shop-list-view.css` - новые стили для action-buttons
- `assets/css/responsive-mobile.css` v1.1.0 - кнопки всегда видны на мобильных

### Single Product Page
- ✅ Добавлены кнопки Wishlist/Compare в блок `.product-actions`
- ✅ Стилизованные кнопки с иконками и текстом
- ✅ Hover эффекты

**Файлы:**
- `assets/css/woo_single.css` - добавлены стили `.product-actions`

## Технические улучшения

### Enqueue система
- Подключение стилей cart/checkout через условия `is_cart()` / `is_checkout()`
- Обновлена версия до 1.6.0 во всех модулях

### Модули
- `inc/features/custom-account.php` - новый модуль для My Account
- `inc/core/enqueue.php` v1.6.0 - добавлены условия для cart/checkout
- `functions.php` v1.6.0 - подключен custom-account.php

## Итоги

**Обновленные файлы:** 15  
**Новые файлы:** 5  
**Версия темы:** 1.6.0  

Все страницы проекта теперь полностью адаптированы под мобильные устройства с использованием mobile-first подхода.
