<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Defines the user service contract.
 */
interface UserServiceInterface
{
    /** Get paginated users. */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /** Get a user by ID. */
    public function getById(int $id): User;

    /** Store a user. */
    public function store(array $data): User;

    /** Update a user. */
    public function update(int $id, array $data): User;

    /** Delete a user. */
    public function delete(int $id): bool;

    /** Return paginated users latest first with profile and roles loaded. */
    public function getAllUsers(int $perPage = 10): LengthAwarePaginator;

    /** Return a complete employee profile by user ID. */
    public function getUserById(int $id): User;

    /** Create an employee user and profile. */
    public function createUser(array $data): User;

    /** Update an employee user and profile. */
    public function updateUser(int $id, array $data): User;

    /** Delete an employee user, profile, and role assignments. */
    public function deleteUser(int $id): bool;

    /** Activate an employee. */
    public function activateUser(int $id): bool;

    /** Deactivate an employee. */
    public function deactivateUser(int $id): bool;

    /** Assign a role to a user. */
    public function assignRole(int $userId, int $roleId): bool;

    /** Remove a role from a user. */
    public function removeRole(int $userId, ?int $roleId = null): bool;

    /** Replace existing user roles. */
    public function syncRoles(int $userId, array $roles): bool;

    /** Search employees by name, email, and employee code. */
    public function searchUsers(string $keyword): Collection;

    /** Get active employees. */
    public function getActiveUsers(): Collection;

    /** Get inactive employees. */
    public function getInactiveUsers(): Collection;

    /** Get employees by role name. */
    public function getEmployeesByRole(string $role): Collection;

    /** Determine whether a user exists. */
    public function userExists(int $id): bool;

    /** Determine whether an email exists. */
    public function emailExists(string $email, ?int $ignoreUserId = null): bool;

    /** Determine whether an employee code exists. */
    public function employeeCodeExists(string $empCode, ?int $ignoreUserDetailId = null): bool;

    /** Return complete employee profile. */
    public function getEmployeeProfile(int $userId): User;
}
