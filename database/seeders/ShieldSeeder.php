<?php

namespace Raison\FilamentStarter\Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = json_encode([]);
        $users = json_encode([
            [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'email_verified_at' => '2026-01-21T15:14:45.000000Z',
                'created_at' => '2026-01-21T15:14:45.000000Z',
                'updated_at' => '2026-01-21T15:14:45.000000Z',
                'roles' => [],
                'permissions' => [],
            ],
            [
                'id' => 2,
                'name' => 'Edrisa',
                'email' => 'edrisa@raison.africa',
                'email_verified_at' => null,
                'created_at' => '2026-01-21T15:40:04.000000Z',
                'updated_at' => '2026-01-21T15:40:04.000000Z',
                'roles' => ['super_admin'],
                'permissions' => [],
            ],
        ]);
        $userTenantPivot = json_encode([]);
        $rolesWithPermissions = json_encode([
            [
                'name' => 'super_admin',
                'guard_name' => 'web',
                'permissions' => [
                    'ViewAny:Role',
                    'View:Role',
                    'Create:Role',
                    'Update:Role',
                    'Delete:Role',
                    'Restore:Role',
                    'ForceDelete:Role',
                    'ForceDeleteAny:Role',
                    'RestoreAny:Role',
                    'Replicate:Role',
                    'Reorder:Role',
                    'ViewAny:Activity',
                    'View:Activity',
                    'Create:Activity',
                    'Update:Activity',
                    'Delete:Activity',
                    'Restore:Activity',
                    'ForceDelete:Activity',
                    'ForceDeleteAny:Activity',
                    'RestoreAny:Activity',
                    'Replicate:Activity',
                    'Reorder:Activity',
                    'ViewAny:UserView',
                    'View:UserView',
                    'Create:UserView',
                    'Update:UserView',
                    'Delete:UserView',
                    'Restore:UserView',
                    'ForceDelete:UserView',
                    'ForceDeleteAny:UserView',
                    'RestoreAny:UserView',
                    'Replicate:UserView',
                    'Reorder:UserView',
                    'ViewAny:QueueMonitor',
                    'View:QueueMonitor',
                    'Create:QueueMonitor',
                    'Update:QueueMonitor',
                    'Delete:QueueMonitor',
                    'Restore:QueueMonitor',
                    'ForceDelete:QueueMonitor',
                    'ForceDeleteAny:QueueMonitor',
                    'RestoreAny:QueueMonitor',
                    'Replicate:QueueMonitor',
                    'Reorder:QueueMonitor',
                    'View:LogTable',
                    'View:UserActivitiesPage',
                    'View:ModuleManager',
                    'View:ModulesOverview',
                    'View:ActivityChartWidget',
                    'View:LatestActivityWidget',
                ],
            ],
        ]);
        $directPermissions = json_encode([
            'can-impersonate',
            'can-access-panel',
            'can-see-environment-indicator',
        ]);

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
