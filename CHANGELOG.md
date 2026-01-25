# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2026-01-23

### Added
- Model A Dependency Ownership: The package now owns all managed plugin dependencies.
- Class-level verification in `Doctor` command for enabled plugins.
- CI workflow for automated testing and health checks.
- Multi-panel support verification with new Staff panel in sandbox.

### Changed
- Moved Filament plugin requirements from host app to package.

## [1.0.0] - 2026-01-23

### Added
- Centralized Plugin Registry for 18 managed Filament plugins.
- Dedicated Platform management panel at `/platform`.
- Panel-aware plugin resolution with database overrides.
- Safe Mode for emergency access.
- `starter:install`, `starter:update`, and `starter:doctor` commands.
- Multi-tenancy and Multilanguage infrastructure support.
- Audit logging for all platform configuration changes.
- Guardrails to prevent disabling critical plugins.
