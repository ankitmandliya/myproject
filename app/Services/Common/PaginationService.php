<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Common\PaginationServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use InvalidArgumentException;

class PaginationService implements PaginationServiceInterface
{
    protected int $defaultPerPage = 15;

    protected int $maximumPerPage = 100;

    public function perPage(?int $perPage = null): int
    {
        $perPage = $perPage ?? (int) request()->input('per_page', $this->defaultPerPage);

        if ($perPage <= 0) {
            throw new InvalidArgumentException('Per-page value must be a positive integer.');
        }

        return min($perPage, $this->maximumPerPage);
    }

    public function paginate(mixed $query, ?int $perPage = null): LengthAwarePaginator
    {
        if (! method_exists($query, 'paginate')) {
            throw new InvalidArgumentException('The given query does not support pagination.');
        }

        return $query->paginate($this->perPage($perPage));
    }

    public function simplePaginate(mixed $query, ?int $perPage = null): Paginator
    {
        if (! method_exists($query, 'simplePaginate')) {
            throw new InvalidArgumentException('The given query does not support simple pagination.');
        }

        return $query->simplePaginate($this->perPage($perPage));
    }
}
