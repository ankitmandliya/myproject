<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

interface NotificationServiceInterface
{
    public function unreadCount(User $user): int;

    public function latestForNavbar(User $user, int $limit = 10): Collection;

    public function paginateForUser(User $user, array $filters, int $perPage): LengthAwarePaginator;

    public function findForUser(User $user, string $id): DatabaseNotification;

    public function markRead(User $user, string $id): DatabaseNotification;

    public function markAllRead(User $user): int;

    public function sendToUsers(iterable $users, array $payload): int;

    public function roleUsers(array $roles): Collection;

    public function present(DatabaseNotification $notification): array;
}
