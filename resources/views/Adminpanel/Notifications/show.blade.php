@extends('Adminpanel.layout.mainlayout')

@section('title', 'Notification Detail')

@section('content')
@php($item = $notification->presentation)
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Notification Detail</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'Notifications', 'url' => route('hrms.notifications.index')],
                    ['label' => 'Detail'],
                ]])
            </div>
            <div class="ms-md-auto"><a href="{{ route('hrms.notifications.index') }}" class="btn btn-light btn-round"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
        </div>

        <div class="card card-round">
            <div class="card-header d-flex align-items-center gap-3">
                <span class="badge badge-{{ $item['color'] }} p-3"><i class="bi {{ $item['icon'] }}"></i></span>
                <div>
                    <div class="card-title mb-1">{{ $item['title'] }}</div>
                    <span class="badge badge-{{ $item['priority_color'] }}">{{ $item['priority'] }}</span>
                    <span class="badge badge-{{ $item['is_read'] ? 'secondary' : 'primary' }}">{{ $item['is_read'] ? 'Read' : 'Unread' }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3"><strong>Full Message</strong><div class="mt-2 p-3 bg-light rounded text-break">{{ $item['message'] }}</div></div>
                    <div class="col-md-4 mb-3"><strong>Date</strong><div>{{ $item['created_at']?->format('d M Y') ?? '-' }}</div></div>
                    <div class="col-md-4 mb-3"><strong>Time</strong><div>{{ $item['created_at']?->format('h:i A') ?? '-' }}</div></div>
                    <div class="col-md-4 mb-3"><strong>Type</strong><div>{{ $item['type'] }}</div></div>
                    <div class="col-md-6 mb-3"><strong>Reference</strong><div>{{ $item['reference_type'] ?? '-' }} #{{ $item['reference_id'] ?? '-' }}</div></div>
                    <div class="col-md-6 mb-3"><strong>Created By</strong><div>{{ $item['created_by'] ?? '-' }}</div></div>
                </div>
                <a href="{{ $item['url'] }}" class="btn btn-primary"><i class="bi bi-box-arrow-up-right me-1"></i> Open Action Link</a>
            </div>
        </div>
    </div>
</div>
@endsection

