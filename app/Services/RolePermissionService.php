<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RolePermissionServiceInterface;
use App\Models\RoleMaster;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for reusable HRMS RBAC checks.
 */
class RolePermissionService implements RolePermissionServiceInterface
{
    /** Create a new role permission service instance. */
    public function __construct(
        protected RoleMaster $roleMaster,
        protected RolePermission $rolePermission,
        protected UserRole $userRole,
        protected User $user
    ) {
    }

    /** Determine whether a user has a permission. */
    public function hasPermission(int $userId, string $permission): bool
    {
        $permission = $this->validateName($permission, 'Permission');

        if (! $this->permissionExists($permission)) {
            throw new RuntimeException("Permission [{$permission}] does not exist.");
        }

        return $this->getUserPermissions($userId)->contains($permission);
    }

    /** Determine whether a user has a role. */
    public function hasRole(int $userId, string $role): bool
    {
        $role = $this->validateName($role, 'Role');

        if (! $this->roleExists($role)) {
            throw new RuntimeException("Role [{$role}] does not exist.");
        }

        return $this->getUserRoles($userId)->contains($role);
    }

    /** Get permissions assigned to a user. */
    public function getUserPermissions(int $userId): Collection
    {
        $user = $this->findUserWithRolesAndPermissions($userId);

        return $user->roles
            ->flatMap(fn (RoleMaster $role): Collection => $role->permissions->pluck('permission_name'))
            ->unique()
            ->sort()
            ->values();
    }

    /** Get roles assigned to a user. */
    public function getUserRoles(int $userId): Collection
    {
        $user = $this->findUserWithRolesAndPermissions($userId);

        return $user->roles
            ->pluck('role_name')
            ->unique()
            ->sort()
            ->values();
    }

    /** Authorize a user for a permission. */
    public function authorize(int $userId, string $permission): bool
    {
        if ($this->hasPermission($userId, $permission)) {
            return true;
        }

        throw new AuthorizationException("User [{$userId}] is not authorized for permission [{$permission}].");
    }

    /** Determine whether a role exists. */
    public function roleExists(string $role): bool
    {
        $role = $this->validateName($role, 'Role');

        return $this->roleMaster->where('role_name', $role)->exists();
    }

    /** Determine whether a permission exists. */
    public function permissionExists(string $permission): bool
    {
        $permission = $this->validateName($permission, 'Permission');

        return $this->rolePermission->where('permission_name', $permission)->exists();
    }

    /** Get permissions assigned to a role. */
    public function getRolePermissions(string $role): Collection
    {
        $roleModel = $this->findRoleWithPermissions($role);

        return $roleModel->permissions
            ->pluck('permission_name')
            ->unique()
            ->sort()
            ->values();
    }

    /** Get every role with its permissions. */
    public function getRolesWithPermissions(): EloquentCollection
    {
        return $this->roleMaster
            ->with(['permissions' => function ($query) {
                $query->orderBy('permission_name');
            }])
            ->orderBy('role_name')
            ->get();
    }

    /** Determine whether a user has any supplied role. */
    public function userHasAnyRole(int $userId, array $roles): bool
    {
        $roles = $this->validateNames($roles, 'Role');

        foreach ($roles as $role) {
            if (! $this->roleExists($role)) {
                throw new RuntimeException("Role [{$role}] does not exist.");
            }
        }

        return $this->getUserRoles($userId)->intersect($roles)->isNotEmpty();
    }

    /** Determine whether a user has any supplied permission. */
    public function userHasAnyPermission(int $userId, array $permissions): bool
    {
        $permissions = $this->validateNames($permissions, 'Permission');

        foreach ($permissions as $permission) {
            if (! $this->permissionExists($permission)) {
                throw new RuntimeException("Permission [{$permission}] does not exist.");
            }
        }

        return $this->getUserPermissions($userId)->intersect($permissions)->isNotEmpty();
    }

    /** Sync permissions for a role. */
    public function syncPermissions(int $roleId, array $permissions): mixed
    {
        throw new RuntimeException('Permission syncing is outside the current RBAC business logic phase.');
    }

    /** Find a user with roles and permissions eager loaded. */
    protected function findUserWithRolesAndPermissions(int $userId): User
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('User ID must be a positive integer.');
        }

        $user = $this->user->with('roles.permissions')->find($userId);

        if (! $user instanceof User) {
            throw new RuntimeException("User [{$userId}] does not exist.");
        }

        return $user;
    }

    /** Find a role with permissions eager loaded. */
    protected function findRoleWithPermissions(string $role): RoleMaster
    {
        $role = $this->validateName($role, 'Role');

        $roleModel = $this->roleMaster
            ->with(['permissions' => function ($query) {
                $query->orderBy('permission_name');
            }])
            ->where('role_name', $role)
            ->first();

        if (! $roleModel instanceof RoleMaster) {
            throw new RuntimeException("Role [{$role}] does not exist.");
        }

        return $roleModel;
    }

    /** Validate a required string value. */
    protected function validateName(string $value, string $label): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException("{$label} is required.");
        }

        return $value;
    }

    /**
     * Validate a required list of string values.
     *
     * @return array<int, string>
     */
    protected function validateNames(array $values, string $label): array
    {
        if ($values === []) {
            throw new InvalidArgumentException("At least one {$label} is required.");
        }

        return collect($values)
            ->map(fn (mixed $value): string => $this->validateName((string) $value, $label))
            ->unique()
            ->values()
            ->all();
    }
}
