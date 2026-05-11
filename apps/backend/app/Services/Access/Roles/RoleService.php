<?php

namespace App\Services\Access\Roles;

use App\Models\Access\RoleModel;
use App\Support\PermissionCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) Arr::get($filters, 'per_page', 15), 1), 100);
        $sort = (string) Arr::get($filters, 'sort', 'id');
        $direction = strtolower((string) Arr::get($filters, 'direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $search = trim((string) Arr::get($filters, 'search', ''));
        $activeOnly = filter_var(Arr::get($filters, 'active_only', false), FILTER_VALIDATE_BOOLEAN);

        $sortable = ['id', 'name', 'is_active', 'created_at', 'updated_at'];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'id';
        }

        return RoleModel::query()
            ->where('guard_name', 'sanctum')
            ->with('permissions:name')
            ->withCount('users')
            ->when($activeOnly, static fn (Builder $query): Builder => $query->active())
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $term = '%'.addcslashes($search, '%_\\').'%';
                $query->where('name', 'like', $term);
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);
    }

    public function create(array $data): RoleModel
    {
        return DB::transaction(function () use ($data): RoleModel {
            $payload = $this->normalize($data);
            $permissions = $this->flattenPermissionsInput($payload['permissions'] ?? []);
            unset($payload['permissions']);
            $payload['guard_name'] = 'sanctum';
            $payload['is_active'] = $payload['is_active'] ?? true;

            $role = RoleModel::query()->create($payload);
            $role->syncPermissions($this->ensurePermissions($permissions));

            return $role->refresh()->load('permissions:name')->loadCount('users');
        });
    }

    public function update(RoleModel $role, array $data): RoleModel
    {
        return DB::transaction(function () use ($role, $data): RoleModel {
            $payload = $this->normalize($data);
            $permissions = null;

            if (array_key_exists('permissions', $payload)) {
                $permissions = $this->flattenPermissionsInput($payload['permissions'] ?? []);
                unset($payload['permissions']);
            }

            if ($payload !== []) {
                $role->update($payload);
            }

            if ($permissions !== null) {
                $role->syncPermissions($this->ensurePermissions($permissions));
            }

            return $role->refresh()->load('permissions:name')->loadCount('users');
        });
    }

    public function inactivate(RoleModel $role): RoleModel
    {
        if (! $role->is_active) {
            return $role->load('permissions:name')->loadCount('users');
        }

        return DB::transaction(function () use ($role): RoleModel {
            $role->update(['is_active' => false]);

            return $role->refresh()->load('permissions:name')->loadCount('users');
        });
    }

    public function permissionsCatalog(): array
    {
        $catalog = [];
        foreach (PermissionCatalog::modules() as $module) {
            $catalog[$module] = PermissionCatalog::actions();
        }

        return $catalog;
    }

    /**
     * @param  array<int|string, mixed>  $input
     * @return list<string>
     */
    private function flattenPermissionsInput(array $input): array
    {
        $permissions = [];

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $permissions[] = $value;
                continue;
            }

            if (! is_string($key) || ! is_array($value)) {
                continue;
            }

            foreach ($value as $action => $enabled) {
                if (! is_string($action) || ! in_array($action, PermissionCatalog::actions(), true)) {
                    continue;
                }

                if ((bool) $enabled) {
                    $permissions[] = "{$key}.{$action}";
                }
            }
        }

        return array_values(array_unique(array_filter($permissions)));
    }

    /**
     * @param  list<string>  $permissions
     * @return \Illuminate\Support\Collection<int, Permission>
     */
    private function ensurePermissions(array $permissions)
    {
        $this->ensureCatalogPermissions();

        return collect($permissions)->map(function (string $name): Permission {
            return Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'sanctum',
            ]);
        });
    }

    private function ensureCatalogPermissions(): void
    {
        foreach (PermissionCatalog::modules() as $module) {
            foreach (PermissionCatalog::actions() as $action) {
                Permission::query()->firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'sanctum',
                ]);
            }
        }
    }

    private function normalize(array $data): array
    {
        $payload = $data;

        if (array_key_exists('name', $payload)) {
            $payload['name'] = trim((string) $payload['name']);
        }

        return $payload;
    }
}