@extends('Adminpanel.layout.mainlayout')

@section('content')
@php
    $calendarItems = $calendarItems ?? collect();
    $filters = $filters ?? ['month' => now()->format('Y-m')];
    $monthLabel = $monthLabel ?? now()->format('F Y');
    $previousMonth = $previousMonth ?? now()->subMonth()->format('Y-m');
    $currentMonth = $currentMonth ?? now()->format('Y-m');
    $nextMonth = $nextMonth ?? now()->addMonth()->format('Y-m');
    $typeClasses = ['Leave' => 'badge-primary', 'Holiday' => 'badge-info', 'Weekly Off' => 'badge-dark'];
@endphp

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Leave Calendar</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave Management', 'url' => route('hrms.leave-apply.index')],
                    ['label' => 'Calendar'],
                ]])
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <a href="{{ route('hrms.leave-apply.index') }}" class="btn btn-light btn-round">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="card-title mb-0">{{ $monthLabel }}</div>
                <div class="btn-group" role="group" aria-label="Calendar month navigation">
                    <a href="{{ route('hrms.leave-apply.calendar', ['month' => $previousMonth]) }}" class="btn btn-light btn-sm" title="Previous month">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('hrms.leave-apply.calendar', ['month' => $currentMonth]) }}" class="btn btn-light btn-sm" title="Current month">Current</a>
                    <a href="{{ route('hrms.leave-apply.calendar', ['month' => $nextMonth]) }}" class="btn btn-light btn-sm" title="Next month">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('hrms.leave-apply.calendar') }}" method="GET" class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="month">Month</label>
                            <input type="month" name="month" id="month" class="form-control" value="{{ $filters['month'] ?? $currentMonth }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <a href="{{ route('hrms.leave-apply.calendar') }}" class="btn btn-light">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Calendar Items</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="display table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th class="text-center">Type</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calendarItems as $item)
                                <tr>
                                    <td>{{ optional($item->date ?? null)->format('d M Y') ?? '-' }}</td>
                                    <td>{{ $item->employee_name ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $typeClasses[$item->type ?? ''] ?? 'badge-secondary' }} px-3 py-2">{{ $item->type ?? '-' }}</span>
                                    </td>
                                    <td>{{ $item->description ?? '-' }}</td>
                                    <td>{{ $item->status ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="mb-3 text-muted"><i class="fas fa-calendar-times fa-3x"></i></div>
                                        <h4 class="fw-bold mb-0">No Leave Requests Found</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection