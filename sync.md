# MoySklad WC Sync - Upgrade Guide v2.2.0

## Overview

This document outlines the upgrade process from version 2.1.0 to 2.2.0 of the MoySklad WooCommerce Sync plugin. This upgrade includes critical improvements to performance, reliability, and error handling.

## Version Information

- **Previous Version:** 2.1.0
- **New Version:** 2.2.0
- **Release Date:** December 2024
- **Compatibility:** WordPress 6.0+, WooCommerce 7.0+, PHP 8.0+

## Key Changes

### 1. Enhanced Error Handling & Recovery
- Improved retry logic with exponential backoff
- Better connection error handling
- Graceful degradation for API timeouts
- Enhanced error logging with full context

### 2. Performance Optimizations
- Optimized database queries with proper indexing
- Reduced memory footprint during large syncs
- Improved batch processing efficiency
- Better memory management with configurable thresholds

### 3. Webhook Improvements
- Enhanced webhook validation with HMAC signatures
- Better webhook failure detection
- Automatic fallback to scheduled sync when webhooks fail
- Webhook health monitoring

### 4. Database Schema Updates
- Added indexes for better query performance
- Optimized stock data table structure
- Improved log retention policies

### 5. Admin Interface Enhancements
- Real-time sync progress indicators
- Better error messaging
- Webhook registration interface
- Enhanced debugging tools

## Upgrade Steps

### Step 1: Backup

**CRITICAL:** Always backup before upgrading!

```bash
cp -r wp-content/plugins/moysklad-wc-sync wp-content/plugins/moysklad-wc-sync-backup-$(date +%Y%m%d)
mysqldump -u [user] -p [database] > moysklad_backup_$(date +%Y%m%d).sql
```

### Step 2: Update Plugin Files

Replace all plugin files with the new version. The following files are updated:

**Core Files:**
- `moysklad-wc-sync.php` → v2.2.0
- `includes/class-admin.php` → v2.2.0
- `includes/class-api.php` → v2.2.0
- `includes/class-cron.php` → v2.2.0
- `includes/class-logger.php` → v2.2.0
- `includes/class-stock-sync.php` → v2.2.0
- `includes/class-sync-engine.php` → v2.2.0 (already updated)
- `includes/class-webhook-handler.php` → v2.2.0

**Assets:**
- `assets/js/admin.js` - Enhanced progress tracking
- `assets/css/admin.css` - Improved UI styling

### Step 3: Database Schema Updates

Run database migrations automatically on plugin activation, or manually execute:

```sql
ALTER TABLE wp_ms_wc_sync_stock_data 
ADD INDEX idx_product_id (product_id),
ADD INDEX idx_sku (sku),
ADD INDEX idx_updated_at (updated_at);

ALTER TABLE wp_ms_wc_sync_logs
ADD INDEX idx_log_level_time (log_level, log_time),
MODIFY context longtext DEFAULT NULL;
```

### Step 4: Update Plugin Constants

Version constants are automatically updated in `moysklad-wc-sync.php`:

```php
define('MS_WC_SYNC_VERSION', '2.2.0');
```

### Step 5: Reactivate Plugin

Deactivate and reactivate the plugin to trigger:
- Database table creation/updates
- Cron job rescheduling
- Webhook endpoint registration
- Option migrations

```bash
wp plugin deactivate moysklad-wc-sync
wp plugin activate moysklad-wc-sync
```

### Step 6: Verify Settings

Navigate to **WooCommerce → МойСклад Sync** and verify:

1. **API Settings:**
   - API Token is present
   - Test Connection succeeds

2. **Sync Settings:**
   - Batch size (recommended: 50)
   - Max execution time (recommended: 180)
   - Stock sync interval configured

3. **Webhook Settings:**
   - If using webhooks, re-register them
   - Verify webhook secret is set
   - Check webhook status

### Step 7: Test Synchronization

Perform manual sync tests:

1. **Test Connection:** Click "Проверить соединение"
2. **Manual Sync:** Run "Запустить синхронизацию"
3. **Stock Sync:** Test "Обновить остатки"
4. **Webhook Test:** If enabled, trigger a webhook from МойСклад

### Step 8: Monitor Cron Jobs

Verify cron scheduling:

```bash
wp cron event list | grep ms_wc_sync
```

Expected output:
- `ms_wc_sync_daily_sync` - Full product sync (daily at 23:50)
- `ms_wc_sync_stock_sync` - Stock updates (configurable interval)

### Step 9: Check Logs

Review synchronization logs:

1. Navigate to **Логи** tab in admin
2. Look for upgrade success messages
3. Check for any errors
4. Verify log cleanup is working (30-day retention)

## Configuration Changes

### New Options

```php
// Enhanced error handling
'ms_wc_sync_max_retries' => 3

// Memory management
'ms_wc_sync_memory_threshold' => 0.8

// Webhook security
'ms_wc_sync_webhook_secret' => 'auto-generated'

// Enhanced debugging
'ms_wc_sync_debug_mode' => false
```

### Updated Options

```php
// Stock sync intervals (expanded)
'ms_wc_sync_stock_interval' => [
    'ms_wc_sync_5min',   // Every 5 minutes
    'ms_wc_sync_10min',  // Every 10 minutes (default)
    'ms_wc_sync_15min',  // Every 15 minutes
    'ms_wc_sync_30min',  // Every 30 minutes
    'ms_wc_sync_hourly'  // Every hour
]
```

## API Changes

### Modified Methods

**class-api.php:**
- `make_request()` - Enhanced retry logic with exponential backoff
- `test_connection()` - Improved validation
- Error handling improvements

**class-sync-engine.php:**
- `run_sync()` - Better memory management
- `sync_all_products()` - Optimized batch processing
- Added price statistics tracking

**class-webhook-handler.php:**
- `verify_webhook()` - HMAC signature validation
- `process_webhook()` - Enhanced error handling
- `check_webhooks()` - Health monitoring

## Webhook Registration

If using webhooks, you must re-register them after upgrade:

### Via Admin Panel:
1. Go to **WooCommerce → МойСклад Sync**
2. Navigate to **Webhook** tab
3. Click **Зарегистрировать Webhooks**

### Via МойСклад API:

```bash
POST https://api.moysklad.ru/api/remap/1.2/entity/webhook

{
  "url": "https://yoursite.com/wp-json/moysklad-wc-sync/v1/webhook",
  "action": "UPDATE",
  "entityType": "product"
}
```

Add `X-MoySklad-Webhook-Signature` header with your webhook secret.

## Cron Configuration

### Full Sync Schedule:
- **Frequency:** Daily at 23:50 (server timezone)
- **Hook:** `ms_wc_sync_daily_sync`
- **Recurrence:** `ms_wc_sync_daily`

### Stock Sync Schedule:
- **Frequency:** Configurable (5/10/15/30/60 minutes)
- **Hook:** `ms_wc_sync_stock_sync`
- **Recurrence:** Based on settings

### Manual Rescheduling:

```bash
wp cron event run ms_wc_sync_daily_sync
wp cron event run ms_wc_sync_stock_sync
```

## Troubleshooting

### Issue: Version mismatch after upgrade

**Solution:**
```bash
wp plugin deactivate moysklad-wc-sync
wp plugin activate moysklad-wc-sync
wp cache flush
```

### Issue: Cron jobs not running

**Solution:**
```bash
wp cron event schedule ms_wc_sync_daily_sync "+1 day" ms_wc_sync_daily
wp cron event schedule ms_wc_sync_stock_sync "+10 minutes" ms_wc_sync_10min
```

### Issue: Webhooks not working

**Check:**
1. Webhook secret is configured
2. REST API is accessible: `https://yoursite.com/wp-json/moysklad-wc-sync/v1/webhook`
3. Permalinks are flushed
4. Server allows incoming POST requests

**Fix:**
```bash
wp rewrite flush
```

### Issue: Sync lock timeout

**Solution:**
```php
delete_transient('ms_wc_sync_running');
```

Or use admin panel: **Сбросить блокировку**

### Issue: Database errors

**Check table existence:**
```sql
SHOW TABLES LIKE 'wp_ms_wc_sync_%';
```

**Recreate tables:**
```bash
wp plugin deactivate moysklad-wc-sync
DROP TABLE wp_ms_wc_sync_logs;
DROP TABLE wp_ms_wc_sync_stock_data;
wp plugin activate moysklad-wc-sync
```

## Performance Tuning

### Recommended Settings:

**For small catalogs (< 500 products):**
```php
'ms_wc_sync_batch_size' => 100
'ms_wc_sync_max_time' => 180
'ms_wc_sync_stock_interval' => 'ms_wc_sync_5min'
```

**For medium catalogs (500-2000 products):**
```php
'ms_wc_sync_batch_size' => 50
'ms_wc_sync_max_time' => 240
'ms_wc_sync_stock_interval' => 'ms_wc_sync_10min'
```

**For large catalogs (> 2000 products):**
```php
'ms_wc_sync_batch_size' => 30
'ms_wc_sync_max_time' => 300
'ms_wc_sync_stock_interval' => 'ms_wc_sync_15min'
'ms_wc_sync_use_webhooks' => 'yes'  // Highly recommended
```

### PHP Configuration:

```ini
memory_limit = 256M
max_execution_time = 300
post_max_size = 20M
max_input_time = 300
```

## Rollback Procedure

If you encounter critical issues:

### 1. Restore files:
```bash
rm -rf wp-content/plugins/moysklad-wc-sync
cp -r wp-content/plugins/moysklad-wc-sync-backup-YYYYMMDD wp-content/plugins/moysklad-wc-sync
```

### 2. Restore database:
```bash
mysql -u [user] -p [database] < moysklad_backup_YYYYMMDD.sql
```

### 3. Reactivate:
```bash
wp plugin deactivate moysklad-wc-sync
wp plugin activate moysklad-wc-sync
```

## Testing Checklist

After upgrade, verify:

- [ ] Plugin version shows 2.2.0
- [ ] All classes show version 2.2.0
- [ ] Test connection succeeds
- [ ] Manual sync completes successfully
- [ ] Stock sync updates products
- [ ] Webhooks receive and process events (if enabled)
- [ ] Cron jobs are scheduled correctly
- [ ] Logs are being written
- [ ] Admin UI displays correctly
- [ ] No PHP errors in logs
- [ ] No JavaScript console errors

## Migration Notes

### From 2.0.x to 2.2.0:
- All settings are preserved
- Database schema is backward compatible
- Webhooks need re-registration
- Cron jobs are automatically rescheduled

### From 1.x to 2.2.0:
- Not supported. Upgrade to 2.1.0 first, then to 2.2.0

## Support

For issues or questions:
- **Email:** support@kowb.ru
- **Documentation:** https://kowb.ru/docs/moysklad-wc-sync
- **GitHub:** Report issues with upgrade process

## Changelog Summary

### Version 2.2.0 (December 2024)

**Added:**
- Enhanced error retry logic with exponential backoff
- Webhook health monitoring
- Database query optimization with indexes
- Memory threshold configuration
- Price statistics tracking
- Enhanced debugging tools

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

**Changed:**
- Increased default memory limit to 256M
- Enhanced logging context
- Improved error messages

## Next Steps

After successful upgrade:

1. Monitor first few sync cycles
2. Check server resource usage
3. Optimize batch sizes if needed
4. Enable webhooks for real-time updates
5. Configure alerts for sync failures
6. Document any custom modifications

---

**Last Updated:** December 2024  
**Document Version:** 1.0  
**Plugin Version:** 2.2.0
