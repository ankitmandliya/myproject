<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RoleServiceInterface;
use App\Models\RoleMaster;
use BadMethodCallException;

/**
 * Service skeleton for role operations.
 */
class RoleService implements RoleServiceInterface
{
    /** Create a new role service instance. */
    public function __construct(
        protected RoleMaster $roleMaster
    ) {
    }

    /** Get paginated roles. */
    public function paginate(int $perPage = 10): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Get a role by ID. */
    public function getById(int $id): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Store a role. */
    public function store(array $data): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Update a role. */
    public function update(int $id, array $data): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Delete a role. */
    public function delete(int $id): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Get permissions assigned to a role. */
    public function permissions(int $roleId): mixed { throw new BadMethodCallException('Not implemented.'); }
}
