<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LeaveTypeServiceInterface;
use App\Models\LeaveType;
use BadMethodCallException;

/**
 * Service skeleton for leave type operations.
 */
class LeaveTypeService implements LeaveTypeServiceInterface
{
    /** Create a new leave type service instance. */
    public function __construct(
        protected LeaveType $leaveType
    ) {
    }

    /** Get paginated leave types. */
    public function paginate(int $perPage = 10): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Get a leave type by ID. */
    public function getById(int $id): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Store a leave type. */
    public function store(array $data): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Update a leave type. */
    public function update(int $id, array $data): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Delete a leave type. */
    public function delete(int $id): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Get active leave types. */
    public function active(): mixed { throw new BadMethodCallException('Not implemented.'); }
}
