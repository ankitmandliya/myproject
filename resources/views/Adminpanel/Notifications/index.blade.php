@extends('Adminpanel.layout.mainlayout')

@section('title', 'Notifications')

@section('content')
@php
    $filters = $filters ?? [];
    $types = $types ?? [];
@endphp
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Notifications</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'Notifications'],
                ]])
            </div>
            <div class="ms-md-auto">
                <form action="{{ route('hrms.notifications.read-all') }}" method="POST" class="d-inline js-confirm-form" data-confirm="Mark all notifications as read?">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-round"><i class="bi bi-check2-all me-1"></i> Mark All as Read</button>
                </form>
            </div>
        </div>

        <div class="card card-round mb-3">
            <div class="card-header"><div class="card-title">Filters</div></div>
            <div class="card-body">
                <form action="{{ route('hrms.notifications.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Type</label><select name="type" class="form-control"><option value="">All Types</option>@foreach($types as $type)<option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $type }}</option>@endforeach</select></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="">All</option><option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Unread</option><option value="read" @selected(($filters['status'] ?? '') === 'read')>Read</option></select></div></div>
                        <div class="col-md-2"><div class="form-group"><label>From Date</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>To Date</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>Per Page</label><select name="per_page" class="form-control">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 10) === $size)>{{ $size }}</option>@endforeach</select></div></div>
                        <div class="col-12 d-flex justify-content-end"><div class="form-group"><button type="submit" class="btn btn-primary me-2"><i class="bi bi-search me-1"></i> Search</button><a href="{{ route('hrms.notifications.index') }}" class="btn btn-light">Reset</a></div></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header"><div class="card-title">Notification History</div></div>
            <div class="card-body p-0">
                @if($notifications->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Icon</th><th>Title</th><th>Message</th><th>User</th><th>Created</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                @foreach($notifications as $notification)
                                    @php($item = $notification->presentation)
                                    <tr class="{{ $item['is_read'] ? '' : 'table-light' }}">
                                        <td><span class="badge badge-{{ $item['color'] }} p-2"><i class="bi {{ $item['icon'] }}"></i></span></td>
                                        <td><div class="fw-semibold">{{ $item['title'] }}</div><span class="badge badge-{{ $item['priority_color'] }}">{{ $item['priority'] }}</span></td>
                                        <td class="text-break">{{ $item['message'] }}</td>
                                        <td>{{ $notification->notifiable?->name ?? '-' }}</td>
                                        <td>{{ $item['created_label'] }}</td>
                                        <td><span class="badge badge-{{ $item['is_read'] ? 'secondary' : 'primary' }}">{{ $item['is_read'] ? 'Read' : 'Unread' }}</span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('hrms.notifications.show', $notification->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                                @unless($item['is_read'])
                                                    <form action="{{ route('hrms.notifications.read', $notification->id) }}" method="POST">@csrf<button type="submit" class="btn btn-sm btn-light"><i class="bi bi-check2"></i></button></form>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5"><div class="mb-3 text-muted"><i class="bi bi-bell-slash display-4"></i></div><h4 class="fw-bold">No Notifications Found</h4></div>
                @endif
            </div>
            @if(method_exists($notifications, 'links') && $notifications->count())
                <div class="card-footer">{{ $notifications->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-confirm-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm || 'Continue?')) {
                event.preventDefault();
            }
        });
    });
});
</script>
@endsection

