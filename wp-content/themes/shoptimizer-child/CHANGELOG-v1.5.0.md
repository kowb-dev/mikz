# Changelog v1.5.0 - Кастомные системы Wishlist & Compare

## Обзор изменений

Полная замена плагинов YITH WooCommerce Wishlist и YITH WooCommerce Compare на кастомные решения. Исправлены все проблемы с уведомлениями и счетчиками.

## Что было исправлено

### ✅ Проблемы с уведомлениями
- **Добавление в корзину на single product** - теперь работает
- **Добавление в wishlist на всех страницах** - теперь работает
- **Добавление в compare на shop/archive** - теперь работает  
- **Удаление из корзины** - добавлено уведомление с названием товара
- **Дублирование уведомлений** - полностью устранено

### ✅ Проблемы со счетчиками
- **Счетчик корзины** - обновляется при удалении
- **Счетчики wishlist/compare** - работают мгновенно
- **Синхронизация** - все счетчики синхронизированы

### ✅ Проблемы с функционалом
- **Медленное удаление из сравнения** - исправлено (YITH конфликты устранены)
- **Лимит сравнения** - добавлен лимит 4 товара с уведомлением

## Новые файлы

### PHP модули
```
/inc/features/custom-wishlist.php (v1.0.0)
/inc/features/custom-compare.php (v1.0.0)
```

### JavaScript
```
/assets/js/custom-wishlist.js (v1.0.0)
/assets/js/custom-compare.js (v1.0.0)
```

### CSS
```
/assets/css/custom-wishlist.css
/assets/css/custom-compare.css
```

### Документация
```
WISHLIST-COMPARE-SETUP.md - инструкция по настройке
CHANGELOG-v1.5.0.md - этот файл
```

## Обновленные файлы

### PHP (v1.5.0)
- `functions.php` - подключены новые модули, удалены YITH зависимости
- `inc/features/notifications.php` - добавлено уведомление удаления из корзины
- `inc/features/action-badges.php` - используют кастомные системы вместо YITH

### JavaScript (v1.5.0)
- `assets/js/notifications.js` - слушает кастомные события, обработка удаления из корзины
- `assets/js/action-badges.js` - слушает кастомные события, упрощен код

## Удаленные файлы

```
/inc/yith-compare-fixes.php - больше не нужен
/inc/yith-wishlist-fixes.php - больше не нужен
```

## Технические детали

### Архитектура Wishlist

**Хранение данных:**
- Авторизованные пользователи: `user_meta` с ключом `_mkx_wishlist`
- Гости: WooCommerce Session + cookie `mkx_wishlist`

**AJAX endpoints:**
- `mkx_wishlist_add` - добавить товар
- `mkx_wishlist_remove` - удалить товар
- `mkx_wishlist_get_count` - получить количество

**События:**
- `mkx_added_to_wishlist` (productId, count)
- `mkx_removed_from_wishlist` (productId, count)

### Архитектура Compare

**Хранение данных:**
- WooCommerce Session + cookie `mkx_compare`
- Лимит: максимум 4 товара

**AJAX endpoints:**
- `mkx_compare_add` - добавить товар
- `mkx_compare_remove` - удалить товар
- `mkx_compare_clear` - очистить список
- `mkx_compare_get_count` - получить количество

**События:**
- `mkx_added_to_compare` (productId, count)
- `mkx_removed_from_compare` (productId, count)
- `mkx_compare_cleared` (count)
- `mkx_compare_limit_reached` (message)

### Интеграция с темой

**Хуки WooCommerce:**
- `woocommerce_after_shop_loop_item` (priority 15, 20) - кнопки в loop
- `woocommerce_single_product_summary` (priority 35, 36) - кнопки на single

**Шорткоды:**
- `[mkx_wishlist]` - страница списка избранного
- `[mkx_compare]` - страница сравнения товаров

## Что нужно сделать после обновления

### 1. Создать страницы

**Избранное:**
- Название: Избранное
- Ярлык: `wishlist`
- Содержимое: `[mkx_wishlist]`

**Сравнение:**
- Название: Сравнение товаров
- Ярлык: `compare`
- Содержимое: `[mkx_compare]`

### 2. Обновить ссылки в меню

Если есть ссылки на старые YITH страницы, обновите:
- Wishlist: `/wishlist/`
- Compare: `/compare/`

### 3. Деактивировать YITH плагины

После проверки работы:
- YITH WooCommerce Wishlist - можно деактивировать
- YITH WooCommerce Compare - можно деактивировать

### 4. Очистить кеш

- Очистить кеш плагина (если используется)
- Очистить кеш браузера
- Проверить работу на разных устройствах

## Тестирование

### Чек-лист функционала

**Wishlist:**
- [ ] Кнопка на странице каталога
- [ ] Кнопка на странице товара
- [ ] Уведомление при добавлении
- [ ] Уведомление при удалении
- [ ] Счетчик в header
- [ ] Счетчик в mobile nav
- [ ] Страница списка wishlist
- [ ] Удаление из списка

**Compare:**
- [ ] Кнопка на странице каталога
- [ ] Кнопка на странице товара
- [ ] Уведомление при добавлении
- [ ] Уведомление при удалении
- [ ] Уведомление лимита (5-й товар)
- [ ] Счетчик в header
- [ ] Счетчик в mobile nav
- [ ] Страница сравнения
- [ ] Удаление из сравнения
- [ ] Кнопка "Очистить всё"

**Корзина:**
- [ ] Уведомление при добавлении
- [ ] Уведомление при удалении
- [ ] Обновление счетчика при добавлении
- [ ] Обновление счетчика при удалении

## Производительность

### Оптимизации

- Debounce для уведомлений (1000-1500ms)
- Кеширование названий товаров в JS
- Единый AJAX endpoint для счетчиков
- Минимальные DOM манипуляции
- CSS анимации вместо JS

### Размер файлов

- custom-wishlist.js: ~3KB
- custom-compare.js: ~4KB
- custom-wishlist.css: ~5KB
- custom-compare.css: ~5KB

**Итого:** +17KB (несжатые), что значительно меньше чем YITH плагины (~500KB)

## Совместимость

- WordPress: 5.8+
- WooCommerce: 5.0+
- PHP: 7.4+
- Браузеры: Chrome, Firefox, Safari, Edge (последние 2 версии)
- Мобильные: iOS Safari 12+, Chrome Android 80+

## Безопасность

- Все AJAX запросы защищены nonce
- Проверка capabilities где необходимо
- Санитизация всех входящих данных
- Экранирование всех выводимых данных
- Защита от CSRF атак

## Поддержка

При возникновении проблем:

1. Проверьте консоль браузера на JS ошибки
2. Проверьте PHP error log
3. Убедитесь что версии файлов правильные (1.5.0)
4. Очистите все кеши
5. Проверьте что страницы созданы и шорткоды добавлены

## Автор

**KB Team**  
https://kowb.ru  
Версия: 1.5.0  
Дата: Декабрь 2024
