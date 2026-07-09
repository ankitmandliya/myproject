@extends('Adminpanel.layout.mainlayout')

@section('content')
@php
    $detail = $user->userDetail;
    $fullName = trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? '')) ?: $user->name;
    $isActive = (bool) ($detail?->status ?? false);
    $roleNames = $user->roles->pluck('role_name')->join(', ') ?: '-';
    $latestSalary = $salarySummary['latest_salary'] ?? null;
    $defaultPhoto = 'assets/img/profile.jpg';
    $photoPath = trim((string) ($detail?->profile_photo ?? ''));
    $photoUrl = $photoPath !== '' && file_exists(public_path($photoPath))
        ? asset($photoPath)
        : asset($defaultPhoto);
@endphp

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Employee Profile</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Employees', 'url' => route('hrms.users.index')],
                    ['label' => 'View'],
                ]])
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <a href="{{ route('hrms.users.edit', $user->id) }}" class="btn btn-primary btn-round me-2">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('hrms.users.index') }}" class="btn btn-light btn-round">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-profile card-secondary card-round">
                    <div class="card-header" style="background-image: url('{{ asset('assets/img/blogpost.jpg') }}')">
                        <div class="profile-picture">
                            <div class="avatar avatar-xl">
                                <img src="{{ $photoUrl }}" alt="{{ $fullName }}" class="avatar-img rounded-circle">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="user-profile text-center">
                            <div class="name">{{ $fullName }}</div>
                            <div class="job">{{ $detail?->designation ?? '-' }}</div>
                            <div class="desc">{{ $detail?->department ?? '-' }}</div>
                            <span class="badge {{ $isActive ? 'badge-success' : 'badge-secondary' }} mt-2">
                                {{ $isActive ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">Basic Information</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><strong>Employee Code:</strong><br>{{ $detail?->emp_code ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>Name:</strong><br>{{ $fullName }}</div>
                            <div class="col-md-6 mb-3"><strong>Email:</strong><br>{{ $user->email }}</div>
                            <div class="col-md-6 mb-3"><strong>Role:</strong><br>{{ $roleNames }}</div>
                            <div class="col-md-6 mb-3"><strong>Status:</strong><br>{{ $isActive ? 'Active' : 'Inactive' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">Personal Information</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><strong>Gender:</strong><br>{{ $detail?->gender ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>Date of Birth:</strong><br>{{ $detail?->dob ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>Mobile:</strong><br>{{ $user->phone ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>Address:</strong><br>{{ $detail?->address ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">Employment Information</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><strong>Department:</strong><br>{{ $detail?->department ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>Designation:</strong><br>{{ $detail?->designation ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>Joining Date:</strong><br>{{ $detail?->joining_date ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>Basic Salary:</strong><br>{{ $detail?->basic_salary ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">Government Details</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><strong>Aadhaar:</strong><br>{{ $detail?->aadhaar ?? '-' }}</div>
                            <div class="col-md-6 mb-3"><strong>PAN:</strong><br>{{ $detail?->pan ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center">
                            <span>Attendance Summary</span>
                            <span>
                                <a href="{{ route('hrms.attendance.history', $user->id) }}" class="btn btn-xs btn-primary">History</a>
                                <a href="{{ route('hrms.attendance.calendar', $user->id) }}" class="btn btn-xs btn-info">Calendar</a>
                            </span>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6 mb-2">Total Days<br><strong>{{ $attendanceSummary['total_days'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Present<br><strong>{{ $attendanceSummary['present'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Absent<br><strong>{{ $attendanceSummary['absent'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Leave<br><strong>{{ $attendanceSummary['leave'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Late Days<br><strong>{{ $attendanceSummary['late_days'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Half Days<br><strong>{{ $attendanceSummary['half_days'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="card-title">Leave Summary</div>
                        <div class="row mt-3">
                            <div class="col-6 mb-2">Applied<br><strong>{{ $leaveSummary['total_applied'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Pending<br><strong>{{ $leaveSummary['pending'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Approved<br><strong>{{ $leaveSummary['approved'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Rejected<br><strong>{{ $leaveSummary['rejected'] ?? 0 }}</strong></div>
                            <div class="col-12 mb-2">Approved Leave Days<br><strong>{{ $leaveSummary['total_leave_days'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="card-title">Salary Summary</div>
                        <div class="row mt-3">
                            <div class="col-6 mb-2">Records<br><strong>{{ $salarySummary['total_salary_records'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Latest Net<br><strong>{{ $latestSalary?->net_salary ?? '-' }}</strong></div>
                            <div class="col-6 mb-2">Average<br><strong>{{ $salarySummary['average_salary'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Highest<br><strong>{{ $salarySummary['highest_salary'] ?? 0 }}</strong></div>
                            <div class="col-6 mb-2">Lowest<br><strong>{{ $salarySummary['lowest_salary'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection