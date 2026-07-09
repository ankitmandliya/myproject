<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\HolidayServiceInterface;
use App\Models\Holiday;
use BadMethodCallException;

/**
 * Service skeleton for holiday operations.
 */
class HolidayService implements HolidayServiceInterface
{
    /** Create a new holiday service instance. */
    public function __construct(
        protected Holiday $holiday
    ) {
    }

    /** Get paginated holidays. */
    public function paginate(int $perPage = 10): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Get a holiday by ID. */
    public function getById(int $id): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Store a holiday. */
    public function store(array $data): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Update a holiday. */
    public function update(int $id, array $data): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Delete a holiday. */
    public function delete(int $id): mixed { throw new BadMethodCallException('Not implemented.'); }

    /** Get active holidays. */
    public function active(): mixed
    {
        return $this->holiday->active(1)->get();
    }
}
