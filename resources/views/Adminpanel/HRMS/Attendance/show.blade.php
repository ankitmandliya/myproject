@extends('Adminpanel.layout.mainlayout')

@section('content')
@php
    $employee = $attendance->user;
    $detail = $employee?->userDetail;
    $employeeName = trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? ''))
        ?: ($employee?->name ?? 'Unknown Employee');
    $photoPath = trim((string) ($detail?->profile_photo ?? ''));
    $photoUrl = $photoPath !== '' ? asset($photoPath) : asset('assets/img/profile.jpg');
    $displayStatus = $attendance->display_status ?? $attendance->status ?? '-';
@endphp
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Attendance Details</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Attendance', 'url' => route('hrms.attendance.index')],
                    ['label' => 'Details'],
                ]])
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <a href="{{ route('hrms.attendance.index') }}" class="btn btn-light btn-round">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card card-round text-center">
                    <div class="card-body">
                        <img src="{{ $photoUrl }}" alt="{{ $employeeName }}" class="avatar-img rounded-circle mb-3"
                            style="width: 110px; height: 110px; object-fit: cover;"
                            onerror="this.onerror=null;this.src='{{ asset('assets/img/profile.jpg') }}';">
                        <h4 class="fw-bold mb-1">{{ $employeeName }}</h4>
                        <p class="text-muted mb-1">{{ $detail?->emp_code ?? '-' }}</p>
                        <p class="mb-0">{{ $detail?->department ?? '-' }} / {{ $detail?->designation ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card card-round">
                    <div class="card-header"><div class="card-title">Daily Attendance</div></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Attendance Date</dt><dd class="col-sm-7">{{ $attendance->attendance_date?->format('d M Y') ?? '-' }}</dd>
                            <dt class="col-sm-5">Check In</dt><dd class="col-sm-7">{{ $attendance->check_in ?? '-' }}</dd>
                            <dt class="col-sm-5">Check Out</dt><dd class="col-sm-7">{{ $attendance->check_out ?? '-' }}</dd>
                            <dt class="col-sm-5">Working Hours</dt><dd class="col-sm-7">{{ $attendance->working_hours ?? '-' }}</dd>
                            <dt class="col-sm-5">Late Minutes</dt><dd class="col-sm-7">{{ $attendance->late_minutes ?? 0 }}</dd>
                            <dt class="col-sm-5">Half Day</dt><dd class="col-sm-7">{{ ($attendance->half_day ?? false) ? 'Yes' : 'No' }}</dd>
                            <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ $displayStatus }}</dd>
                            @if(filled($attendance->notes ?? null))
                                <dt class="col-sm-5">Notes</dt><dd class="col-sm-7">{{ $attendance->notes }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
