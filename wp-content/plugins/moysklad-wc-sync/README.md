# MoySklad WooCommerce Sync

Оптимизированная односторонняя синхронизация товаров и остатков из МойСклад в WooCommerce с поддержкой webhook и инкрементальных обновлений.

## Версия

**Текущая версия:** 2.2.0  
**Дата релиза:** Декабрь 2024

## Требования

- **WordPress:** 6.0 или выше
- **WooCommerce:** 7.0 или выше
- **PHP:** 8.0 или выше
- **MySQL:** 5.7 или выше
- **Расширения PHP:** json, curl, mbstring

## Возможности

### Базовые функции
- ✅ Односторонняя синхронизация из МойСклад в WooCommerce
- ✅ Автоматическое создание и обновление товаров
- ✅ Синхронизация остатков со склада
- ✅ Поддержка розничных и оптовых цен
- ✅ Инкрементальные обновления остатков

### Продвинутые функции
- ✅ Real-time обновления через webhook
- ✅ Batch processing для больших каталогов
- ✅ Умное управление памятью
- ✅ Retry logic с экспоненциальной задержкой
- ✅ Детальное логирование с контекстом
- ✅ Индексированная база данных для быстрых запросов

### Cron Jobs
- ✅ Полная синхронизация товаров (ежедневно в 23:50)
- ✅ Инкрементальные обновления остатков (настраиваемый интервал)
- ✅ Автоматическое восстановление при сбоях

### Webhook Support
- ✅ Real-time обновления при изменении товаров
- ✅ HMAC подпись для безопасности
- ✅ Автоматическая регистрация webhook
- ✅ Fallback на scheduled sync при сбое webhook

## Установка

### Через WordPress Admin
1. Загрузите zip-файл плагина
2. Перейдите в **Плагины → Добавить новый → Загрузить плагин**
3. Выберите zip-файл и нажмите **Установить**
4. Активируйте плагин

### Вручную
1. Распакуйте архив в `wp-content/plugins/moysklad-wc-sync/`
2. Активируйте плагин в админ-панели WordPress

### Через WP-CLI
```bash
wp plugin install moysklad-wc-sync.zip --activate
```

## Настройка

### 1. API Token
1. Войдите в МойСклад
2. Перейдите в **Настройки → Пользователи и права → Пользователь → Токены доступа**
3. Создайте новый токен с правами на чтение товаров и остатков
4. Скопируйте токен

### 2. Настройки плагина
1. Перейдите в **WooCommerce → МойСклад Sync**
2. Вставьте API Token
3. Нажмите **Проверить соединение**
4. Настройте параметры синхронизации

### 3. Настройка Cron
Плагин автоматически создает cron jobs:
- **Полная синхронизация:** ежедневно в 23:50
- **Обновление остатков:** каждые 10 минут (по умолчанию)

### 4. Настройка Webhook (опционально)
1. Включите **Использовать Webhooks**
2. Нажмите **Зарегистрировать Webhooks**
3. Webhook URL: `https://yoursite.com/wp-json/moysklad-wc-sync/v1/webhook`

## Использование

### Ручная синхронизация
1. Перейдите в **WooCommerce → МойСклад Sync**
2. Нажмите **Запустить синхронизацию** для полной синхронизации
3. Или **Обновить остатки** для обновления только остатков

### Мониторинг
- **Логи:** просмотр детальных логов синхронизации
- **Progress Bar:** отслеживание прогресса в реальном времени
- **Last Sync:** время последней успешной синхронизации

### Troubleshooting
- **Сброс блокировки:** если синхронизация зависла
- **Перепланировка Cron:** если cron jobs не работают
- **Очистка логов:** для освобождения места в БД

## Архитектура

### Структура файлов
```
moysklad-wc-sync/
├── moysklad-wc-sync.php          # Main plugin file
├── includes/
│   ├── class-admin.php           # Admin interface
│   ├── class-api.php             # МойСклад API handler
│   ├── class-cron.php            # Cron manager
│   ├── class-logger.php          # Logging system
│   ├── class-stock-sync.php      # Stock synchronization
│   ├── class-sync-engine.php     # Full sync engine
│   └── class-webhook-handler.php # Webhook processor
├── assets/
│   ├── css/
│   │   └── admin.css             # Admin styles
│   └── js/
│       └── admin.js              # Admin scripts
├── templates/
│   └── admin-page.php            # Admin UI template
├── languages/
│   └── moysklad-wc-sync-ru_RU.po # Russian translation
└── README.md                     # This file
```

### Database Tables

**wp_ms_wc_sync_logs**
- Хранит логи синхронизации
- Retention: 30 дней
- Indexes: log_time, log_level, (log_level, log_time)

**wp_ms_wc_sync_stock_data**
- Кеш остатков для инкрементальных обновлений
- Indexes: moysklad_id (UNIQUE), product_id, sku, updated_at

### Sync Flow

```
1. Full Sync (Daily)
   ├── Get products from МойСклад API
   ├── Get assortment (stock + prices)
   ├── Create/Update WC products
   ├── Update stock levels
   └── Log results

2. Stock Sync (Frequent)
   ├── Get current stock from МойСклад
   ├── Compare with cached stock
   ├── Update only changed products
   └── Update cache

3. Webhook Sync (Real-time)
   ├── Receive webhook from МойСклад
   ├── Verify HMAC signature
   ├── Extract product ID
   ├── Update single product
   └── Return 200 OK
```

## API Reference

### Admin AJAX Actions

**Test Connection**
```javascript
wp.ajax.post('ms_wc_sync_test_connection', {
    _ajax_nonce: msWcSyncAdmin.nonce
});
```

**Manual Sync**
```javascript
wp.ajax.post('ms_wc_sync_manual', {
    _ajax_nonce: msWcSyncAdmin.nonce
});
```

**Stock Sync**
```javascript
wp.ajax.post('ms_wc_sync_stock_manual', {
    _ajax_nonce: msWcSyncAdmin.nonce
});
```

### REST API

**Webhook Endpoint**
```
POST /wp-json/moysklad-wc-sync/v1/webhook
Headers:
  X-MoySklad-Webhook-Signature: <HMAC-SHA256>
Body:
  {
    "eventType": "UPDATE",
    "entityType": "product",
    "entity": { ... }
  }
```

## Настройки производительности

### Для малых каталогов (< 500 товаров)
```php
'ms_wc_sync_batch_size' => 100
'ms_wc_sync_max_time' => 180
'ms_wc_sync_stock_interval' => 'ms_wc_sync_5min'
```

### Для средних каталогов (500-2000 товаров)
```php
'ms_wc_sync_batch_size' => 50
'ms_wc_sync_max_time' => 240
'ms_wc_sync_stock_interval' => 'ms_wc_sync_10min'
```

### Для больших каталогов (> 2000 товаров)
```php
'ms_wc_sync_batch_size' => 30
'ms_wc_sync_max_time' => 300
'ms_wc_sync_stock_interval' => 'ms_wc_sync_15min'
'ms_wc_sync_use_webhooks' => 'yes'
```

## Changelog

### Version 2.2.0 (December 2024)
**Added:**
- Enhanced error retry logic with exponential backoff
- Webhook health monitoring
- Database query optimization with indexes
- Memory threshold configuration
- Price statistics tracking
- Version tracking and migration system

**Improved:**
- API error handling
- Webhook signature validation
- Admin UI responsiveness
- Log retention and cleanup
- Batch processing efficiency

**Fixed:**
- Version inconsistencies across files
- Memory leaks in large syncs
- Webhook registration issues
- Cron scheduling edge cases

### Version 2.1.0 (November 2024)
- Initial release with webhook support
- Stock sync optimization
- Admin interface redesign

## Безопасность

- ✅ Nonce verification для всех AJAX запросов
- ✅ Capability checks (`manage_woocommerce`)
- ✅ HMAC signature validation для webhook
- ✅ Prepared statements для всех DB запросов
- ✅ Input sanitization и output escaping
- ✅ Secure token storage

## Совместимость

### Протестировано с:
- ✅ WordPress 6.0 - 6.4+
- ✅ WooCommerce 7.0 - 9.0+
- ✅ PHP 8.0 - 8.3
- ✅ MySQL 5.7 - 8.0
- ✅ HPOS (High-Performance Order Storage)

### Поддерживаемые плагины:
- WooCommerce Product Bundles
- WPML / Polylang
- WooCommerce Subscriptions
- Advanced Custom Fields (ACF)

## Поддержка

- **Email:** support@kowb.ru
- **Documentation:** https://kowb.ru/docs/moysklad-wc-sync
- **GitHub:** Report issues

## Лицензия

Copyright © 2024 KB (kowb.ru)  
All rights reserved.

## Автор

**KB**  
Website: https://kowb.ru  
Специализация: WordPress/WooCommerce Enterprise Solutions

## Upgrade Guide

Для подробных инструкций по обновлению см. [sync.md](/sync.md)

## Contribution

Этот плагин разработан для внутреннего использования. Pull requests не принимаются.

---

**Last Updated:** December 2024  
**Plugin Version:** 2.2.0  
**Documentation Version:** 1.0
