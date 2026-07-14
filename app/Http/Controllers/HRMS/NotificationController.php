<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\NotificationServiceInterface;
use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationServiceInterface $notificationService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['type', 'status', 'from_date', 'to_date', 'per_page']);
        $perPage = $this->perPage($request);
        $notifications = $this->notificationService->paginateForUser($request->user(), $filters, $perPage);
        $types = array_keys(NotificationService::TYPE_STYLES);
        $filters['per_page'] = $perPage;

        return view('Adminpanel.Notifications.index', compact('notifications', 'filters', 'types', 'perPage'));
    }

    public function show(Request $request, string $notification): View
    {
        $notification = $this->notificationService->findForUser($request->user(), $notification);

        if ($notification->read_at === null) {
            $notification->markAsRead();
            $notification = $this->notificationService->findForUser($request->user(), (string) $notification->id);
        }

        return view('Adminpanel.Notifications.show', compact('notification'));
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $this->notificationService->markRead($request->user(), $notification);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notificationService->markAllRead($request->user());

        return back()->with('success', 'All notifications marked as read.');
    }

    protected function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }
}
