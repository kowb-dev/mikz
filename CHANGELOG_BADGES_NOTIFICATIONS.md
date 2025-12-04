# Исправления счётчиков и уведомлений v1.2.0

## Исправленные проблемы

### 1. Счётчики корзины и сравнения при удалении
**Проблема:** На страницах `/cart` и `/compare` счётчики не обновлялись при удалении товаров.

**Решение:**
- Добавлен `MutationObserver` для отслеживания изменений DOM на страницах корзины, сравнения и избранного
- Добавлен интервал проверки cookie `yith_woocompare_list` для мгновенного обновления счётчика сравнения
- Добавлены обработчики событий `updated_wc_div` для корзины WooCommerce
- Улучшены селекторы для кнопок удаления: `.cart_item .remove`, `#yith-woocompare .remove`, `table.compare-list .remove`

### 2. Двойные уведомления на страницах /shop и /cat
**Проблема:** При добавлении товара в корзину на страницах магазина и категорий появлялось 2 одинаковых уведомления.

**Решение:**
- Удалены дублирующиеся обработчики клика на `.add_to_cart_button`, `.compare`, `.yith-wcwl-add-button`
- Добавлен механизм debounce для предотвращения повторных уведомлений в течение 1 секунды
- Оставлены только обработчики стандартных событий WooCommerce/YITH

### 3. Отсутствие уведомлений на single product
**Проблема:** На странице товара не появлялись уведомления при добавлении в корзину.

**Решение:**
- Добавлен отдельный обработчик события `submit` для формы `form.cart`
- Исключена обработка single product в обработчике `added_to_cart` для избежания дублирования
- Добавлена задержка 500мс для корректной работы с AJAX

### 4. Счётчик Wishlist не обновлялся
**Проблема:** На страницах `/shop` и `/cat` счётчик избранного обновлялся только после перезагрузки или добавления в сравнение.

**Решение:**
- Уменьшены задержки обновления счётчиков с 500-1200мс до 300-800мс
- Добавлен `MutationObserver` для отслеживания изменений в таблице wishlist
- Добавлена принудительная проверка после действий с wishlist
- Улучшена обработка событий `added_to_wishlist` и `removed_from_wishlist`

## Изменённые файлы

### JavaScript
- `/wp-content/themes/shoptimizer-child/assets/js/notifications.js` (v1.2.0)
- `/wp-content/themes/shoptimizer-child/assets/js/action-badges.js` (v1.2.0)

### PHP
- `/wp-content/themes/shoptimizer-child/inc/features/notifications.php` (v1.2.0)
- `/wp-content/themes/shoptimizer-child/inc/features/action-badges.php` (v1.2.0)

## Технические улучшения

1. **MutationObserver API** - отслеживание изменений DOM в реальном времени
2. **Cookie monitoring** - проверка изменений cookie для compare каждую секунду
3. **Debounce mechanism** - предотвращение дублирования уведомлений
4. **Optimized timeouts** - сокращение задержек обновления с 500-1200мс до 300-800мс
5. **Enhanced event handling** - улучшенная обработка событий WooCommerce и YITH

## Совместимость

- WordPress 5.8+
- WooCommerce 6.0+
- YITH WooCommerce Wishlist
- YITH WooCommerce Compare
- Shoptimizer theme + child theme
