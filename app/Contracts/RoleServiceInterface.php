<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Defines the role service contract.
 */
interface RoleServiceInterface
{
    /** Get paginated roles. */
    public function paginate(int $perPage = 10): mixed;

    /** Get a role by ID. */
    public function getById(int $id): mixed;

    /** Store a role. */
    public function store(array $data): mixed;

    /** Update a role. */
    public function update(int $id, array $data): mixed;

    /** Delete a role. */
    public function delete(int $id): mixed;

    /** Get permissions assigned to a role. */
    public function permissions(int $roleId): mixed;
}
