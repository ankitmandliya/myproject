<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Models\User;
use App\Notifications\LeaveDatabaseNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class NotificationService implements NotificationServiceInterface
{
    public const TYPE_LEAVE_APPLIED = 'Leave Applied';
    public const TYPE_LEAVE_APPROVED = 'Leave Approved';
    public const TYPE_LEAVE_REJECTED = 'Leave Rejected';
    public const TYPE_LEAVE_CANCELLED = 'Leave Cancelled';
    public const TYPE_LEAVE_REVOKED = 'Leave Revoked';
    public const TYPE_LEAVE_UPDATED = 'Leave Updated';
    public const TYPE_REMINDER = 'Reminder';
    public const TYPE_INFORMATION = 'Information';
    public const TYPE_WARNING = 'Warning';

    /** @var array<string, array{icon: string, color: string}> */
    public const TYPE_STYLES = [
        self::TYPE_LEAVE_APPLIED => ['icon' => 'bi-send', 'color' => 'primary'],
        self::TYPE_LEAVE_APPROVED => ['icon' => 'bi-check-circle', 'color' => 'success'],
        self::TYPE_LEAVE_REJECTED => ['icon' => 'bi-x-circle', 'color' => 'danger'],
        self::TYPE_LEAVE_CANCELLED => ['icon' => 'bi-calendar-x', 'color' => 'warning'],
        self::TYPE_LEAVE_REVOKED => ['icon' => 'bi-arrow-counterclockwise', 'color' => 'dark'],
        self::TYPE_LEAVE_UPDATED => ['icon' => 'bi-pencil-square', 'color' => 'info'],
        self::TYPE_REMINDER => ['icon' => 'bi-alarm', 'color' => 'warning'],
        self::TYPE_INFORMATION => ['icon' => 'bi-info-circle', 'color' => 'info'],
        self::TYPE_WARNING => ['icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
    ];

    /** @var array<string, string> */
    public const PRIORITY_COLORS = [
        'Low' => 'secondary',
        'Medium' => 'primary',
        'High' => 'warning',
        'Critical' => 'danger',
    ];

    public function unreadCount(User $user): int
    {
        if (! $this->notificationsTableReady()) {
            return 0;
        }

        return (int) $user->unreadNotifications()->count();
    }

    public function latestForNavbar(User $user, int $limit = 10): Collection
    {
        if (! $this->notificationsTableReady()) {
            return collect();
        }

        return $user->notifications()
            ->latest('created_at')
            ->limit(max(1, min($limit, 10)))
            ->get()
            ->map(fn (DatabaseNotification $notification): array => $this->present($notification));
    }

    public function paginateForUser(User $user, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->baseQueryForUser($user);

        if (($filters['type'] ?? '') !== '') {
            $query->where('data->type', (string) $filters['type']);
        }

        if (($filters['status'] ?? '') === 'read') {
            $query->whereNotNull('read_at');
        } elseif (($filters['status'] ?? '') === 'unread') {
            $query->whereNull('read_at');
        }

        if (($filters['from_date'] ?? '') !== '') {
            $query->whereDate('created_at', '>=', (string) $filters['from_date']);
        }

        if (($filters['to_date'] ?? '') !== '') {
            $query->whereDate('created_at', '<=', (string) $filters['to_date']);
        }

        $paginator = $query->latest('created_at')->paginate($this->perPage($perPage));
        $paginator->setCollection($paginator->getCollection()->map(fn (DatabaseNotification $notification) => $this->attachPresentation($notification)));

        return $paginator;
    }

    public function findForUser(User $user, string $id): DatabaseNotification
    {
        $notification = $this->baseQueryForUser($user)->whereKey($id)->first();

        if (! $notification instanceof DatabaseNotification) {
            abort(403);
        }

        return $this->attachPresentation($notification);
    }

    public function markRead(User $user, string $id): DatabaseNotification
    {
        $notification = $this->findForUser($user, $id);
        $notification->markAsRead();

        return $this->attachPresentation($notification->refresh());
    }

    public function markAllRead(User $user): int
    {
        if (! $this->notificationsTableReady()) {
            return 0;
        }

        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function sendToUsers(iterable $users, array $payload): int
    {
        if (! $this->notificationsTableReady()) {
            return 0;
        }

        $sent = 0;
        foreach ($users as $user) {
            if ($user instanceof User) {
                $user->notify(new LeaveDatabaseNotification($this->normalizePayload($payload)));
                $sent++;
            }
        }

        return $sent;
    }

    public function roleUsers(array $roles): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('role_name', $roles))
            ->with('roles')
            ->get();
    }

    public function present(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];
        $type = (string) ($data['type'] ?? self::TYPE_INFORMATION);
        $priority = (string) ($data['priority'] ?? 'Medium');
        $style = self::TYPE_STYLES[$type] ?? self::TYPE_STYLES[self::TYPE_INFORMATION];

        return [
            'id' => $notification->id,
            'title' => (string) ($data['title'] ?? $type),
            'message' => (string) ($data['message'] ?? ''),
            'type' => $type,
            'icon' => (string) ($data['icon'] ?? $style['icon']),
            'color' => (string) ($data['color'] ?? $style['color']),
            'priority' => $priority,
            'priority_color' => self::PRIORITY_COLORS[$priority] ?? 'secondary',
            'url' => (string) ($data['url'] ?? route('hrms.notifications.show', $notification->id)),
            'reference_id' => $data['reference_id'] ?? null,
            'reference_type' => $data['reference_type'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'is_read' => $notification->read_at !== null,
            'created_at' => $notification->created_at,
            'created_label' => $notification->created_at?->format('d M Y h:i A') ?? '-',
            'time_ago' => $notification->created_at?->diffForHumans() ?? '-',
            'read_at' => $notification->read_at,
        ];
    }


    protected function attachPresentation(DatabaseNotification $notification): DatabaseNotification
    {
        $notification->setAttribute('presentation', $this->present($notification));
        $notification->syncOriginalAttribute('presentation');

        return $notification;
    }
    protected function baseQueryForUser(User $user): Builder
    {
        if (! $this->notificationsTableReady()) {
            throw new RuntimeException('Notifications table is not available.');
        }

        if ($this->isAdmin($user)) {
            return DatabaseNotification::query()->with('notifiable');
        }

        return DatabaseNotification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey());
    }

    protected function isAdmin(User $user): bool
    {
        $user->loadMissing('roles');

        return $user->roles->pluck('role_name')->contains('Admin');
    }

    protected function normalizePayload(array $payload): array
    {
        $type = (string) ($payload['type'] ?? self::TYPE_INFORMATION);
        $style = self::TYPE_STYLES[$type] ?? self::TYPE_STYLES[self::TYPE_INFORMATION];

        return [
            'title' => (string) ($payload['title'] ?? $type),
            'message' => (string) ($payload['message'] ?? ''),
            'type' => $type,
            'icon' => (string) ($payload['icon'] ?? $style['icon']),
            'color' => (string) ($payload['color'] ?? $style['color']),
            'priority' => (string) ($payload['priority'] ?? 'Medium'),
            'url' => (string) ($payload['url'] ?? route('hrms.dashboard')),
            'reference_id' => $payload['reference_id'] ?? null,
            'reference_type' => $payload['reference_type'] ?? null,
            'created_by' => $payload['created_by'] ?? auth()->id(),
            'uuid' => (string) Str::uuid(),
        ];
    }

    protected function notificationsTableReady(): bool
    {
        return Schema::hasTable('notifications');
    }

    protected function perPage(int $perPage): int
    {
        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }
}




