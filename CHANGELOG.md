# Changelog

All notable changes to this project will be documented here.

## Unreleased

### Added

- New `MageOS_ShoppingFeed` module and `mage-os/module-shopping-feed` package identity
- Consolidated Generic, Google Shopping, Google Local Inventory, and Google Promotions feed support
- Isolated database, configuration, route, cron, CLI, event, layout, UI, JavaScript, log, and output identifiers
- Isolated dependency-injection array keys and application cache identifiers
- Isolated feed output, promotion cache, process lock, and log paths
- Isolated Admin session keys used when restoring failed form submissions
- Declarative schema whitelist regenerated from the consolidated database schema
- Repository validation and CI checks for identity isolation, merged feed definitions, XML, Composer metadata, and PHP syntax
- Portable Magento test bootstrap and the consolidated 257-test unit suite
- Explicit MSI source-code to Google store-code mapping for Local Inventory
- Streaming gzip generation for FTP and SFTP uploads
- Current Google Ads `view_item` events for selected configurable variants

### Fixed

- Removed the duplicated Google Shopping `shipping_weight` default column
- Added the required encoding to the Local Inventory feed definition
- Replaced legacy DoubleClick remarketing pixels and globals with Google tag events
- Made configurable deep links work independently of the dynamic remarketing setting
- Added support for configurable dropdown deep links as well as swatches
- Updated Promotions enums and repeated destinations for Shopping ads and free listings
- Updated schema.org offer URLs to HTTPS
- Corrected cached uploader reuse so each FTP or SFTP destination uses its own credentials
- Applied the saved gzip setting to product and Promotions uploads with cleanup after transfer
- Fixed post-upload event data so Promotions uploads receive the destination object instead of a Boolean result
- Removed the original paid-module product skip attribute from the new module's runtime behavior
- Excluded historical data patches that could inspect or move legacy-package data and log files
- Fixed PHP 8 failures in filter sorting, empty delimiter handling, generator state restoration, and additional image mapping
- Hardened price formatting against nonnumeric strings and restored label mapping for array-valued select attributes
- Passed modern configurable-product dependencies explicitly to avoid runtime service-locator fallbacks
- Replaced the service-locator serializer wrapper with Magento's serializer interface
- Made stock handling safe when no legacy stock item is returned and avoided duplicate stock-status reads
- Made empty-column replacement and option concatenation safe for incomplete configuration data
- Corrected log-handler replacement so stale handlers are not retained

### Migration

- No automatic migration from the Rocket Web packages is performed
- Existing package installations and data remain untouched

### Changed

- Aligned Composer metadata, source notices, and the bundled license on OSL-3.0
