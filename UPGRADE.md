# Upgrade Guide

## From 1.0.0 to 1.1.0 (Model A Dependency Ownership)

### 1. Remove Plugin Dependencies from Host App
The package now owns all managed plugin dependencies. You should remove the following from your root `composer.json` to avoid conflicts:
- `filament/filament`
- `bezhansalleh/filament-shield`
- `jeffgreco13/filament-breezy`
- ... (and all other managed plugins)

### 2. Archilex Filter Sets Repository
If you use `filter-sets`, ensure the custom repository is still present in your root `composer.json`:
```json
"repositories": [
    {
        "type": "composer",
        "url": "https://filament-filter-sets.composer.sh"
    }
]
```

### 3. Update Dependencies
```bash
composer update
php artisan starter:update
```

## From Starter Kit v4 to Filament Starter Package 1.0.0

### 1. Update `composer.json`
Add the path repository and require the package:
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/raison/filament-starter"
    }
],
"require": {
    "raison/filament-starter": "dev-main"
}
```

### 2. Register Plugin
In your `AdminPanelProvider.php`, remove all managed plugins and add:
```php
->plugins([
    \Raison\FilamentStarter\Filament\StarterPlugin::make(),
])
```

### 3. Run Installation
```bash
php artisan starter:install
```
