<?php

declare(strict_types=1);

namespace App\Contracts\Common;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;

interface PaginationServiceInterface
{
    public function perPage(?int $perPage = null): int;

    public function paginate(mixed $query, ?int $perPage = null): LengthAwarePaginator;

    public function simplePaginate(mixed $query, ?int $perPage = null): Paginator;
}
