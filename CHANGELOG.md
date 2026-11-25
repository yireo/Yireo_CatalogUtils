# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 25 November 2026
### Added
- `ProductRepository` now implements Magento's `ProductRepositoryInterface` and adds `createNewProduct()` and `getFirstProductInListing()` helpers
- PHP 8.5 support

### Changed
- `CommonSearchCriteriaBuilder` and `ProductSearchCriteriaBuilder` now extend Magento's `SearchCriteriaBuilder` instead of proxying calls through `__call()`, so their constructor arguments have changed

### Removed
- Support for PHP versions below 8.2

### Fixed
- Methods proxied through the old `__call()` wrappers received their arguments wrapped in an array, breaking calls like `get()` and `getList()`
- `addWebsiteIdFilter()` let negative website IDs through and accepted non-integer input like `1,2`, which could widen the filter to multiple websites

## [1.0.2] - 29 July 2020
### Added
- Magento 2.4 compatibility

## [1.0.1] - Undocumented
