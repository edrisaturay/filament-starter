# Filament Starter Package

The core engine of the Filament Starter Kit. This package provides centralized plugin management, multi-panel synchronization, and administrative infrastructure for Filament v5 applications.

## Key Components

### 1. Plugin Registry (`Support/PluginRegistry.php`)
A centralized list of all managed Filament plugins. It defines how each plugin is installed, its default state, and whether it's critical for system stability.

### 2. Platform Manager
Registers a dedicated `platform` panel and provides resources for:
- **Plugin Management**: Enable/disable plugins per panel and tenant.
- **Panel Snapshots**: Track registered panels and their configurations.
- **System Status**: Real-time health checks for the platform and its dependencies.
- **Audit Logs**: Track all configuration changes made via the Platform UI.

### 3. Plugin Synchronization (`Support/PluginSyncManager.php`)
Ensures that the database overrides are always in sync with the code-based registry. It automatically handles the creation of default records and cleanup of stale entries.

### 4. Platform Doctor (`Support/Doctor.php`)
A diagnostic tool that verifies:
- Platform installation status.
- Presence of panel snapshots.
- Availability of required plugin classes and packages.
- Integrity of tenancy configurations.

## Commands

- `php artisan starter:install`: The primary entry point for setting up the platform.
- `php artisan starter:doctor`: Diagnostic command for health monitoring.
- `php artisan starter:update`: Refreshes snapshots and synchronizes the plugin registry.
- `php artisan starter:safe-mode`: Emergency command to bypass plugin overrides.

## Configuration

The package behavior can be customized via `config/filament-starter.php`. Key settings include:
- `tenancy`: Configure multi-tenancy mode and identification strategy.
- `multilanguage`: Enable and configure supported locales.
- `superadmin`: Define the role required to access Platform Manager resources.

## Usage in Panels

To use the starter kit in any panel, simply register the `StarterPlugin`:

```php
use Raison\FilamentStarter\Filament\StarterPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            StarterPlugin::make(),
        ]);
}
```

This will automatically register the Platform Manager resources (for authorized users) and resolve all enabled plugins for that panel.

## License

The MIT License (MIT).
