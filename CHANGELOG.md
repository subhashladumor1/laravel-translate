# Changelog

All notable changes to `laravel-translate` will be documented in this file.

## [1.0.0] - 2025-10-25

### Added
- Initial release
- Multi-source translation support (LibreTranslate, Lingva, MyMemory, Google, Argos)
- Automatic fallback mechanism between services
- Smart caching with Redis, File, and Database support
- Automatic language detection
- Batch translation for arrays and collections
- Queue support for large translation jobs
- Blade directives (`@translate`, `@translateStart/@translateEnd`)
- Helper functions (`t()`, `translate()`, `translate_batch()`)
- Facade support
- CLI commands (translate:string, translate:file, translate:sync, translate:clear-cache)
- Middleware for auto-locale detection
- Analytics dashboard for monitoring performance
- Translation logging and metrics
- PSR-4 compliant structure
- Comprehensive documentation
- Test suite with PHPUnit
- Offline translation support via Argos
