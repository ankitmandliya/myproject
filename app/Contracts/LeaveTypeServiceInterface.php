<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Defines the leave type service contract.
 */
interface LeaveTypeServiceInterface
{
    /** Get paginated leave types. */
    public function paginate(int $perPage = 10): mixed;

    /** Get a leave type by ID. */
    public function getById(int $id): mixed;

    /** Store a leave type. */
    public function store(array $data): mixed;

    /** Update a leave type. */
    public function update(int $id, array $data): mixed;

    /** Delete a leave type. */
    public function delete(int $id): mixed;

    /** Get active leave types. */
    public function active(): mixed;
}
