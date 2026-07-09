@extends('Adminpanel.layout.mainlayout')

@section('content')
@php
    $calendarBadgeClasses = [
        'Present' => 'bg-success', 'Late' => 'bg-warning text-dark', 'Half Day' => 'bg-info',
        'Leave' => 'bg-primary', 'Absent' => 'bg-danger', 'Holiday' => 'bg-secondary',
        'Weekly Off' => 'bg-dark', 'No Attendance' => 'bg-light text-dark',
    ];
@endphp
<div class="container">
    <div class="page-inner">
        <div class="pt-2 pb-4">
            <h3 class="fw-bold mb-3">{{ request()->routeIs('hrms.my-attendance') ? 'My Attendance Calendar' : 'Employee Attendance Calendar' }}</h3>
            @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('hrms.dashboard')], ['label' => 'HRMS'],
                ['label' => 'Attendance', 'url' => route('hrms.attendance.index')], ['label' => 'Calendar'],
            ]])
        </div>
        <div class="card card-round">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div><h4 class="fw-bold mb-1">{{ trim(($employee->userDetail?->first_name ?? '') . ' ' . ($employee->userDetail?->last_name ?? '')) ?: $employee->name }}</h4><span class="text-muted">{{ $employee->userDetail?->emp_code ?? '-' }} · {{ $employee->userDetail?->department ?? '-' }} · {{ $employee->userDetail?->designation ?? '-' }}</span></div>
                <a href="{{ route('hrms.attendance.history', ['employeeId' => $employee->id, 'month' => $calendarData['selected_month']]) }}" class="btn btn-primary btn-sm mt-3 mt-md-0">History</a>
            </div>
        </div>
        <div class="row">
            @foreach(['present' => 'Present', 'absent' => 'Absent', 'leave' => 'Leave', 'late_days' => 'Late', 'half_days' => 'Half Day'] as $key => $label)
                <div class="col-6 col-md"><div class="card card-stats card-round"><div class="card-body text-center"><div class="card-category">{{ $label }}</div><h4 class="card-title">{{ $attendanceSummary[$key] ?? 0 }}</h4></div></div></div>
            @endforeach
        </div>
        <div class="card card-round">
            <div class="card-header">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['month' => $calendarData['previous_month']]) }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i> Previous</a>
                    <div class="text-center"><div class="card-title">{{ $calendarData['label'] }}</div>@if($isDemoCalendar)<span class="badge bg-info">Demo Calendar Data</span>@endif</div>
                    <a href="{{ request()->fullUrlWithQuery(['month' => $calendarData['next_month']]) }}" class="btn btn-sm btn-light">Next <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <form method="GET" action="{{ url()->current() }}" class="d-flex justify-content-center mt-3">
                    <input type="month" name="month" value="{{ $calendarData['selected_month'] }}" class="form-control form-control-sm" style="max-width: 190px;" aria-label="Attendance month">
                    <button class="btn btn-sm btn-primary ms-2" type="submit">Go</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle mb-3">
                        <thead><tr>@foreach($calendarData['weekdays'] as $weekday)<th>{{ $weekday }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach($calendarData['weeks'] as $week)
                                <tr>
                                    @foreach($week as $day)
                                        <td class="align-top {{ ! $day['is_current_month'] ? 'bg-light text-muted' : '' }} {{ $day['is_today'] ? 'border border-3 border-primary bg-primary-subtle' : '' }}" style="min-width: 125px; height: 135px;">
                                            @if($day['is_today'])<span class="badge bg-primary mb-1">TODAY</span>@endif
                                            <div class="fw-bold fs-5">{{ $day['day'] }}</div>
                                            @if($day['is_current_month'])
                                                <span class="badge {{ $calendarBadgeClasses[$day['status']] ?? 'bg-secondary' }}">{{ $day['status'] }}</span>
                                                @if($day['holiday_name'])<div class="small fw-semibold mt-1">{{ $day['holiday_name'] }}</div>@endif
                                                @if($day['check_in'])<div class="small mt-1"><i class="fas fa-sign-in-alt text-success"></i> {{ $day['check_in'] }}</div>@endif
                                                @if($day['check_out'])<div class="small"><i class="fas fa-sign-out-alt text-danger"></i> {{ $day['check_out'] }}</div>@endif
                                                @if($day['working_hours'] !== null)<div class="small text-muted">{{ $day['working_hours'] }} hrs</div>@endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(! $calendarData['has_attendance'])<div class="text-center text-muted mb-3">No Attendance Found</div>@endif
                <div class="d-flex flex-wrap gap-2" aria-label="Attendance legend">
                    @foreach(['Present','Late','Half Day','Leave','Absent','Holiday','Weekly Off'] as $status)<span class="badge {{ $calendarBadgeClasses[$status] }}">{{ $status }}</span>@endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
