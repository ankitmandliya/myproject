<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Defines the role permission service contract.
 */
interface RolePermissionServiceInterface
{
    /** Determine whether a user has a permission. */
    public function hasPermission(int $userId, string $permission): bool;

    /** Determine whether a user has a role. */
    public function hasRole(int $userId, string $role): bool;

    /** Get permissions assigned to a user. */
    public function getUserPermissions(int $userId): Collection;

    /** Get roles assigned to a user. */
    public function getUserRoles(int $userId): Collection;

    /** Authorize a user for a permission. */
    public function authorize(int $userId, string $permission): bool;

    /** Determine whether a role exists. */
    public function roleExists(string $role): bool;

    /** Determine whether a permission exists. */
    public function permissionExists(string $permission): bool;

    /** Get permissions assigned to a role. */
    public function getRolePermissions(string $role): Collection;

    /** Get every role with its permissions. */
    public function getRolesWithPermissions(): EloquentCollection;

    /** Determine whether a user has any supplied role. */
    public function userHasAnyRole(int $userId, array $roles): bool;

    /** Determine whether a user has any supplied permission. */
    public function userHasAnyPermission(int $userId, array $permissions): bool;

    /** Sync permissions for a role. */
    public function syncPermissions(int $roleId, array $permissions): mixed;
}
