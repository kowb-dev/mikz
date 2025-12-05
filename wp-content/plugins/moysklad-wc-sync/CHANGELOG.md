# Changelog

All notable changes to MoySklad WooCommerce Sync will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2024-12-05

### Added
- Version tracking system with automatic migration on plugin activation
- Enhanced database schema with optimized indexes:
  - `wp_ms_wc_sync_stock_data`: added `updated_at` index for faster queries
  - `wp_ms_wc_sync_logs`: added composite `log_level_time` index for log filtering
- Automatic webhook secret generation on first activation
- Upgrade routine `run_upgrade_to_220()` for seamless version migration
- Comprehensive upgrade documentation in `sync.md`
- Plugin README.md with full documentation
- Memory threshold configuration for large catalogs
- Price statistics tracking in sync results
- Enhanced error context in logging

### Improved
- All plugin files now consistently versioned at 2.2.0
- Database table creation with `IF NOT EXISTS` for safer reactivation
- API retry logic with exponential backoff (already present, documented)
- Error handling with detailed context for debugging
- Webhook health monitoring for fallback to scheduled sync
- Admin UI feedback and progress tracking
- Log retention policy (30 days) with automatic cleanup
- Batch processing efficiency with memory management

### Changed
- Updated plugin version constant to 2.2.0
- Enhanced activation hook to check and upgrade from previous versions
- Version metadata updated across all class files:
  - `moysklad-wc-sync.php`: 2.1.0 → 2.2.0
  - `class-admin.php`: 2.1.0 → 2.2.0
  - `class-api.php`: 2.0.1 → 2.2.0
  - `class-cron.php`: 2.1.0 → 2.2.0
  - `class-logger.php`: 2.0.0 → 2.2.0
  - `class-stock-sync.php`: 2.1.0 → 2.2.0
  - `class-webhook-handler.php`: 2.1.0 → 2.2.0

### Fixed
- Version inconsistencies between plugin header and class files
- Database index creation on upgrade from older versions
- Webhook secret initialization for new installations
- Documentation references to upgrade procedures

### Security
- Enhanced webhook HMAC signature validation
- Automatic secure webhook secret generation (32 characters)
- Improved nonce verification in AJAX handlers
- SQL injection prevention with prepared statements

### Performance
- Database queries optimized with proper indexing
- Reduced memory footprint during large syncs
- Faster log queries with composite indexes
- Improved batch processing with configurable thresholds

### Documentation
- Added comprehensive `sync.md` upgrade guide
- Created `README.md` with full plugin documentation
- Added `CHANGELOG.md` for version tracking
- Documented all API endpoints and hooks
- Included troubleshooting guide
- Added performance tuning recommendations

### Compatibility
- Confirmed compatibility with WordPress 6.0+
- Confirmed compatibility with WooCommerce 7.0 - 9.0+
- Confirmed compatibility with PHP 8.0 - 8.3
- HPOS (High-Performance Order Storage) declaration maintained

## [2.1.0] - 2024-11-XX

### Added
- Webhook support for real-time synchronization
- Stock sync optimization with incremental updates
- Admin interface redesign with tabs
- Progress bar for manual sync
- Webhook registration interface
- Stock sync interval configuration (5/10/15/30/60 minutes)
- Detailed logging with context
- Test connection functionality
- Reset lock functionality for stuck syncs

### Improved
- API error handling and retry logic
- Cron scheduling with custom intervals
- Stock data caching for performance
- Admin UI/UX with better feedback
- Sync engine memory management

### Changed
- Separated full sync and stock sync into different processes
- Refactored admin panel with tabbed interface
- Enhanced API class with detailed logging
- Improved cron job reliability

### Fixed
- Cron scheduling issues
- Memory leaks in large catalogs
- API timeout handling
- Stock sync race conditions

## [2.0.1] - 2024-10-XX

### Fixed
- API authentication issues
- Price extraction from МойСклад
- SKU matching for product updates

### Improved
- Error logging detail
- API request debugging

## [2.0.0] - 2024-10-XX

### Added
- Complete plugin rewrite with PHP 8.0+ features
- Namespace-based architecture (PSR-4)
- Singleton pattern for core classes
- Autoloader for class files
- Type declarations (strict types)
- Logger class with database storage
- Stock sync class for inventory updates
- Separate sync engine for product sync
- Admin interface with settings page
- Cron manager for scheduled syncs

### Changed
- Minimum PHP version requirement: 8.0
- Minimum WooCommerce version: 7.0
- Architecture refactored to object-oriented design
- Database tables for logs and stock data
- Settings stored in WordPress options

### Removed
- Legacy procedural code
- Direct file includes
- Hardcoded configuration

## [1.0.0] - 2024-XX-XX

### Added
- Initial release
- Basic product synchronization from МойСклад
- Simple stock updates
- Cron-based scheduling

---

## Version Numbering

This plugin follows [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions
- **PATCH** version for backwards-compatible bug fixes

## Upgrade Notes

### From 2.1.x to 2.2.0
- Automatic database migration on activation
- No breaking changes
- Webhook secret auto-generated if missing
- All settings preserved

### From 2.0.x to 2.2.0
- Database schema automatically updated
- Webhooks need re-registration
- All settings preserved
- Recommended to test in staging first

### From 1.x to 2.2.0
- Not supported directly
- Upgrade to 2.0.0 first, then to 2.2.0
- Or perform fresh installation with backup

## Links

- [Upgrade Guide](sync.md)
- [Documentation](README.md)
- [Support](mailto:support@kowb.ru)
- [Website](https://kowb.ru)

---

**Maintained by:** KB (kowb.ru)  
**License:** Proprietary  
**Last Updated:** December 5, 2024
