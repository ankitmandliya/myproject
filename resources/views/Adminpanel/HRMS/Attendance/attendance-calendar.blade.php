@extends('Adminpanel.layout.mainlayout')

@section('content')
@php
    $calendarBadgeClasses = [
        'Present' => 'badge-success',
        'Absent' => 'badge-danger',
        'Leave' => 'badge-primary',
        'Holiday' => 'badge-secondary',
        'Weekend' => 'badge-dark',
        'Late' => 'badge-warning',
        'Half Day' => 'badge-info',
    ];
@endphp
<div class="container">
    <div class="page-inner">
        <div class="pt-2 pb-4">
            <h3 class="fw-bold mb-3">Attendance Calendar</h3>
            @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                ['label' => 'HRMS'],
                ['label' => 'Attendance', 'url' => route('hrms.attendance.index')],
                ['label' => 'Calendar'],
            ]])
        </div>
        <div class="card card-round">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">{{ trim(($employee->userDetail?->first_name ?? '') . ' ' . ($employee->userDetail?->last_name ?? '')) ?: $employee->name }}</h4>
                    <span class="text-muted">{{ $employee->userDetail?->emp_code ?? '-' }} · {{ $employee->userDetail?->department ?? '-' }} · {{ $employee->userDetail?->designation ?? '-' }}</span>
                </div>
                <a href="{{ route('hrms.attendance.history', $employee->id) }}" class="btn btn-primary btn-sm mt-3 mt-md-0">History</a>
            </div>
        </div>
        <div class="row">
            @foreach(['present' => 'Present', 'absent' => 'Absent', 'leave' => 'Leave', 'late_days' => 'Late', 'half_days' => 'Half Day'] as $key => $label)
                <div class="col-6 col-md"><div class="card card-stats card-round"><div class="card-body text-center"><div class="card-category">{{ $label }}</div><h4 class="card-title">{{ $attendanceSummary[$key] ?? 0 }}</h4></div></div></div>
            @endforeach
        </div>
        <div class="card card-round">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title">{{ $calendarData['label'] ?? 'Monthly Calendar' }}</div>
                @if($isDemoCalendar)
                    <span class="badge badge-info">Demo Calendar Data</span>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead><tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr></thead>
                        <tbody>
                            @forelse($calendarData['weeks'] ?? [] as $week)
                                <tr>
                                    @foreach($week as $day)
                                        <td class="align-top" style="min-width: 120px; height: 110px;">
                                            <div class="fw-bold mb-2">{{ $day['day'] }}</div>
                                            @if($day['is_weekend'])
                                                <span class="badge {{ $calendarBadgeClasses['Weekend'] }}">Weekend</span>
                                            @endif
                                            @foreach($day['statuses'] as $status)
                                                <span class="badge {{ $calendarBadgeClasses[$status] ?? 'badge-secondary' }} d-block mt-1">
                                                    {{ $status }}
                                                </span>
                                            @endforeach
                                        </td>
                                    @endforeach
                                    @for($empty = count($week); $empty < 7; $empty++)
                                        <td class="bg-light"></td>
                                    @endfor
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-5 text-muted">No calendar data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
