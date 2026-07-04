<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Defines the holiday service contract.
 */
interface HolidayServiceInterface
{
    /** Get paginated holidays. */
    public function paginate(int $perPage = 10): mixed;

    /** Get a holiday by ID. */
    public function getById(int $id): mixed;

    /** Store a holiday. */
    public function store(array $data): mixed;

    /** Update a holiday. */
    public function update(int $id, array $data): mixed;

    /** Delete a holiday. */
    public function delete(int $id): mixed;

    /** Get active holidays. */
    public function active(): mixed;
}
