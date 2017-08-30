# Changelog
All Notable changes to `flipboxdigital\interval` will be documented in this file

## Unreleased
### Fixed
- Issue where human duration string would not return negative if DateInterval is inverted

## 1.0.0-beta.3 - 2017-08-21
### Changed
- The 'year' interval is now calculated from 365 days vs 365.2416 days

### Removed
- The 'month' interval

## 1.0.0-beta.2 - 2017-08-03
### Changed
- The default value behavior.  The field will always return a `\DateInterval` object
- A required field value must not be equal to zero.

## 1.0.0-beta.1 - 2017-08-03
### Added
- Admin icon
- Field Type can be included in admin table view

### Fixed
- Instance where an empty interval would thrown an exception in the admin

## 1.0.0-beta - 2017-08-01
### Added
- Initial release!