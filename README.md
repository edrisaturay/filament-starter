# Filament Starter Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/edrisaturay/filament-starter.svg?style=flat-square)](https://packagist.org/packages/edrisaturay/filament-starter)
[![Total Downloads](https://img.shields.io/packagist/dt/edrisaturay/filament-starter.svg?style=flat-square)](https://packagist.org/packages/edrisaturay/filament-starter)
[![GitHub](https://img.shields.io/github/license/edrisaturay/filament-starter.svg?style=flat-square)](https://github.com/edrisaturay/filament-starter/blob/main/LICENSE.md)

The core engine of the Filament Starter Kit. This package provides centralized plugin management, multi-panel synchronization, and administrative infrastructure for Filament v5 applications.

### Installation

You can install the package via composer:

```bash
composer require edrisaturay/filament-starter:dev-main
```

#### Troubleshooting Dependency Conflicts

If you encounter a conflict with `webmozart/assert` (e.g., when it is locked to `^2.0` in your project), you may need to allow Composer to downgrade it to `1.12.1`, which is the version required by many core Laravel and Filament dependencies (like `spatie/laravel-data` via `phpdocumentor/reflection`).

Try running:
```bash
composer require edrisaturay/filament-starter:dev-main -W
```

If that fails, you can manually set the version of `webmozart/assert` in your root `composer.json` before installing:
```bash
composer require webmozart/assert:1.12.1
```

## Key Components

### 1. Plugin Registry (`Support/PluginRegistry.php`)
A centralized list of all managed Filament plugins. It defines how each plugin is installed, its default state, and whether it's critical for system stability.

### 2. Platform Management
The package provides resources for managing the platform directly within your existing panels:
- **Plugin Management**: Enable/disable plugins per panel and tenant.
- **Panel Snapshots**: Track registered panels and their configurations.
- **System Status**: Real-time health checks for the platform and its dependencies.
- **Audit Logs**: Track all configuration changes made via the Platform UI.

### 4. Plugin Global Config
The starter kit allows centralized configuration of plugins via `config/filament-starter.php`. For example, `filament-backgrounds` settings are managed there.

### 5. Developer Gate
Secure any route or panel using a static password for developer-only access.
- Enabled/Disabled via config.
- Configurable password.
- Middleware: `starter.developer-gate`.

### 4. Plugin Synchronization (`Support/PluginSyncManager.php`)
Ensures that the database overrides are always in sync with the code-based registry. It automatically handles the creation of default records and cleanup of stale entries.

### 5. Platform Doctor (`Support/Doctor.php`)
A diagnostic tool that verifies:
- Platform installation status.
- Presence of panel snapshots.
- Availability of required plugin classes and packages.
- Integrity of tenancy configurations.

## Commands

- `php artisan starter:install`: The primary entry point for setting up the platform. Now supports interactive configuration of plugin publishing, activation, and safety markers.
- `php artisan starter:doctor`: Diagnostic command for health monitoring.
- `php artisan starter:update`: Refreshes snapshots and synchronizes the plugin registry.
- `php artisan starter:safe-mode`: Emergency command to bypass plugin overrides.

## Filament Backgrounds Support
The starter kit integrates `swisnl/filament-backgrounds`.
- **Installation**: Run `starter:install` to set up background providers and interactive configuration.
- **Global Config**: Customize attribution, cache time (remember), and image providers in `config/filament-starter.php`.
- **Custom Images**: Supports `my-images` provider with a configurable directory.

## Column Filters Support
The starter kit bundles `zvizvi/filament-column-filters` (registry key `column-filters`, enabled by default on every panel that registers `FilamentStarterPlugin`).
- **Excel-style header filters**: add `->columnFilter(ColumnFilter::search()|date()|select()|range())` to any table column; a backing table filter (`cf_{column}`) is registered automatically and shows the usual filter indicators.
- **Syncing**: a regular filter named like the column (or any `SelectFilter` on the same attribute) is synced with automatically, otherwise point at it with `->syncWith('filter_name', [...])`.
- **Headless tables**: pages that build `getTable()` outside a Livewire request need `Zvizvi\FilamentColumnFilters\Concerns\HasColumnFilters` on the page class itself.
- **Toggle**: disable per panel from **Plugin Management** or the `column-filters` override row; the `columnFilter()` macro is only available on panels where the plugin is registered.

## Revive Recycle Bin Support
The starter kit integrates `promethys/revive` for centralized management of deleted records.
- **Scoping Control**: Configure User-scoping and Tenant-scoping globally via `config/filament-starter.php`.
- **Admin Visibility**: Define "Global Admin Panels" where admins can see all deleted records regardless of user or tenant.
- **Interactive Setup**: Run `starter:install` to configure scoping preferences and activate the recycle bin for specific panels.

### New Interactive Installation Features
During `starter:install`, you can now:
1. **Publish Configs**: Interactively choose which plugin configuration files to publish to your application.
2. **Activate Plugins per Panel**: Select exactly which plugins should be enabled for each of your managed panels.
3. **Mark Dangerous Plugins**: Identify plugins that are critical for your system. Once marked, these plugins cannot be disabled via the UI, ensuring system stability.

## Dynamic Panel Switcher
The starter kit now includes a high-performance **Panel Switcher** powered by `bezhansalleh/filament-panel-switch`.

- **Seamless Transition**: Shift between "Control Center" (Admin) and "Operations" (Staff) with a sleek modal or slide-over interface.
- **Auto-Discovery**: Automatically lists all your panels or only those you've explicitly enabled via the Plugin Management UI.
- **Themed UI**: Includes custom icons (Shield, Identification, Book) and labels out of the box.
- **Theme Requirements**: To ensure the switcher looks great, add the following to your custom `theme.css`:
    ```css
    @source '../../../../vendor/bezhansalleh/filament-panel-switch/resources/views/**/*.blade.php';
    ```

## Configuration

The package behavior can be customized via `config/filament-starter.php`. Key settings include:
- `tenancy`: Configure multi-tenancy mode and identification strategy.
- `multilanguage`: Enable and configure supported locales.
- `superadmin`: Define the role required to access Platform Manager resources.

## Usage in Panels

To use the starter kit in any panel, register the plugin and include the persistent middleware:

```php
use EdrisaTuray\FilamentStarter\Filament\FilamentStarterPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->middleware([
            'starter.developer-gate',
        ], isPersistent: true)
        ->plugins([
            FilamentStarterPlugin::make(),
        ]);
}
```

This will automatically register the Platform Manager resources (for authorized users) and resolve all enabled plugins for that panel.

## User Model Setup

Ensure your `User` model implements the required interfaces, traits, and methods. You can add them individually, or use the provided trait:

```php
use EdrisaTuray\FilamentStarter\Concerns\HasStarterUserFeatures;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasStarterUserFeatures;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
}
```

If you prefer to add them manually, include the following:

Traits:

```php
use AuthenticationLoggable;
use CanAccessPanel;
use HasApiTokens;
use HasRoles;
use LogsActivity;
use Notifiable;
use TwoFactorAuthenticatable;
```

Methods:

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll();
}

public function getFilamentAvatarUrl(): ?string
{
    return $this->avatar_url ? Storage::url($this->avatar_url) : null;
}

public function canAccessPanel(Panel $panel): bool
{
    return true;
}
```

## License

The MIT License (MIT).
