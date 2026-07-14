@extends('Adminpanel.layout.mainlayout')

@section('title', 'HRMS Dashboard')

@section('content')
    @php
        $summary = $dashboard['summary'] ?? [];
        $employees = $summary['employees'] ?? [];
        $attendance = $summary['attendance'] ?? [];
        $leave = $summary['leave'] ?? [];
        $salary = $summary['salary'] ?? [];
        $company = $summary['company'] ?? [];
        $recentLeaves = $dashboard['recent_leaves'] ?? collect();
        $recentAttendance = $dashboard['recent_attendance'] ?? collect();
        $recentSalarySlips = $dashboard['recent_salary_slips'] ?? collect();
        $recentEmployees = $dashboard['recent_employees'] ?? collect();
        $upcomingHolidays = $dashboard['upcoming_holidays'] ?? collect();
        $attendanceChart = $dashboard['attendance_chart'] ?? [];
        $leaveChart = $dashboard['leave_chart'] ?? [];
        $salaryChart = $dashboard['salary_chart'] ?? [];
        $leaveBalances = $dashboard['leave_balances'] ?? [];
        $recentNotifications = $dashboard['recent_notifications'] ?? [];
        $canManageHierarchy = (bool) ($dashboard['can_manage_hierarchy'] ?? false);
    @endphp

    <div class="container">
        <div class="page-inner">
            @include('Adminpanel.layout.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('dashboard')],
                    ['label' => 'HRMS', 'url' => route('hrms.dashboard')],
                    ['label' => 'Dashboard'],
                ],
            ])

            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                    <h3 class="fw-bold mb-3">HRMS Dashboard</h3>
                    <h6 class="op-7 mb-2">Live overview of HRMS activity</h6>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary bubble-shadow-small">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Total Employees</p>
                                        <h4 class="card-title">{{ $employees['total_employees'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-info bubble-shadow-small">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Active Employees</p>
                                        <h4 class="card-title">{{ $employees['active_employees'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-warning bubble-shadow-small">
                                        <i class="fas fa-user-times"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Inactive Employees</p>
                                        <h4 class="card-title">{{ $employees['inactive_employees'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Present Today</p>
                                        <h4 class="card-title">{{ $attendance['present'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($canManageHierarchy)
                    <div class="col-sm-6 col-md-3">
                        <a href="{{ route('hrms.users.index', ['reporting_manager_id' => 'none']) }}" class="text-decoration-none">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-user-tag"></i></div></div>
                                        <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Without Reporting Manager</p><h4 class="card-title">{{ $employees['employees_without_reporting_manager'] ?? 0 }}</h4></div></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-title">Attendance Summary</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2"><span>Present Today</span><strong>{{ $attendance['present'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Absent Today</span><strong>{{ $attendance['absent'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Late Today</span><strong>{{ $attendance['late'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Half Day Today</span><strong>{{ $attendance['half_day'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Leave Today</span><strong>{{ $attendance['leave'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>LWP Today</span><strong>{{ $attendance['lwp'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Holiday</span><strong>{{ $attendance['holiday'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between"><span>Weekly Off</span><strong>{{ $attendance['weekly_off'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-title">Leave Summary</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2"><span>Pending Leave</span><strong>{{ $leave['pending'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Approved Leave</span><strong>{{ $leave['approved'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between"><span>Rejected Leave</span><strong>{{ $leave['rejected'] ?? 0 }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-title">Payroll Summary</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2"><span>Salary Generated</span><strong>{{ $salary['generated'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Pending Salary</span><strong>{{ $salary['pending'] ?? 0 }}</strong></div>
                            <div class="d-flex justify-content-between"><span>Total Payroll</span><strong>{{ number_format((float) ($salary['total_payroll'] ?? 0), 2) }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Leave Balance</div></div>
                        <div class="card-body">
                            <div class="row">
                                @forelse ($leaveBalances as $balance)
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="fw-bold mb-2">{{ $balance['leave_type'] ?? '-' }}</div>
                                            <div class="d-flex justify-content-between mb-1"><span>Allocated</span><strong>{{ $balance['allocated'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between mb-1"><span>Used</span><strong>{{ $balance['used'] ?? 0 }}</strong></div>
                                            <div class="d-flex justify-content-between"><span>Remaining</span><strong>{{ $balance['remaining'] ?? 0 }}</strong></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12"><p class="text-muted mb-0">No Data Available</p></div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-round">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">Recent Notifications</div>
                            <a href="{{ route('hrms.notifications.index') }}" class="btn btn-sm btn-light">View All</a>
                        </div>
                        <div class="card-body">
                            @forelse($recentNotifications as $item)
                                <a href="{{ route('hrms.notifications.show', $item['id']) }}" class="d-flex align-items-start gap-3 text-decoration-none text-reset border-bottom pb-3 mb-3">
                                    <span class="badge badge-{{ $item['color'] }} p-2"><i class="bi {{ $item['icon'] }}"></i></span>
                                    <span class="flex-fill">
                                        <span class="d-flex justify-content-between gap-3"><strong>{{ $item['title'] }}</strong><small class="text-muted">{{ $item['time_ago'] }}</small></span>
                                        <span class="d-block text-muted text-break">{{ $item['message'] }}</span>
                                    </span>
                                    @unless($item['is_read'])<span class="badge badge-primary">Unread</span>@endunless
                                </a>
                            @empty
                                <div class="text-muted">No Notifications Found</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-title">Company Information</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2"><span>Office Start Time</span><strong>{{ $company['office_start_time'] ?? 'No Data Available' }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Office End Time</span><strong>{{ $company['office_end_time'] ?? 'No Data Available' }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Weekly Off</span><strong>{{ $company['weekly_off'] ?? 'No Data Available' }}</strong></div>
                            <div class="d-flex justify-content-between"><span>Salary Date</span><strong>{{ $company['salary_date'] ?? 'No Data Available' }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-title">Quick Actions</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-4 mb-3"><a href="{{ route('hrms.users.index') }}" class="btn btn-primary btn-round w-100"><i class="fas fa-users me-1"></i> Employees</a></div>
                                @if($canManageHierarchy)<div class="col-sm-6 col-md-4 mb-3"><a href="{{ route('hrms.reporting-hierarchy.index') }}" class="btn btn-primary btn-round w-100"><i class="fas fa-sitemap me-1"></i> Reporting Hierarchy</a></div>@endif
                                <div class="col-sm-6 col-md-4 mb-3"><a href="{{ route('hrms.attendance.index') }}" class="btn btn-primary btn-round w-100"><i class="fas fa-calendar-check me-1"></i> Attendance</a></div>
                                <div class="col-sm-6 col-md-4 mb-3"><a href="{{ route('hrms.leave-apply.index') }}" class="btn btn-primary btn-round w-100"><i class="fas fa-calendar-minus me-1"></i> Leave Apply</a></div>
                                <div class="col-sm-6 col-md-4 mb-3"><a href="{{ route('hrms.salary.index') }}" class="btn btn-primary btn-round w-100"><i class="fas fa-money-check-alt me-1"></i> Payroll</a></div>
                                <div class="col-sm-6 col-md-4 mb-3"><a href="{{ route('hrms.roles.index') }}" class="btn btn-primary btn-round w-100"><i class="fas fa-user-shield me-1"></i> Roles</a></div>
                                <div class="col-sm-6 col-md-4 mb-3"><a href="{{ route('hrms.company-setting.index') }}" class="btn btn-primary btn-round w-100"><i class="fas fa-cogs me-1"></i> Company Settings</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Recent Leave Applications</div></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead class="thead-light"><tr><th>Employee</th><th>Leave Type</th><th>Status</th><th>Dates</th></tr></thead>
                                    <tbody>
                                        @forelse ($recentLeaves as $leaveItem)
                                            <tr>
                                                <td>{{ $leaveItem->user?->userDetail?->first_name ?? $leaveItem->user?->name ?? 'No Data Available' }}</td>
                                                <td>{{ $leaveItem->leaveType?->leave_name ?? 'No Data Available' }}</td>
                                                <td><span class="badge badge-{{ $leaveItem->status === 'Approved' ? 'success' : ($leaveItem->status === 'Rejected' ? 'danger' : 'warning') }}">{{ $leaveItem->status }}</span></td>
                                                <td>{{ optional($leaveItem->from_date)->format('d-M-Y') }} - {{ optional($leaveItem->to_date)->format('d-M-Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">No Data Available</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Recent Attendance</div></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead class="thead-light"><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @forelse ($recentAttendance as $attendanceItem)
                                            <tr>
                                                <td>{{ $attendanceItem->user?->userDetail?->first_name ?? $attendanceItem->user?->name ?? 'No Data Available' }}</td>
                                                <td>{{ optional($attendanceItem->attendance_date)->format('d-M-Y') }}</td>
                                                <td>{{ $attendanceItem->check_in ?? 'No Data Available' }}</td>
                                                <td>{{ $attendanceItem->status ?? 'No Data Available' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">No Data Available</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Recent Salary Generation</div></div>
                        <div class="card-body">
                            @forelse ($recentSalarySlips as $salarySlip)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $salarySlip->user?->userDetail?->first_name ?? $salarySlip->user?->name ?? 'No Data Available' }}</span>
                                    <strong>{{ number_format((float) $salarySlip->net_salary, 2) }}</strong>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No Data Available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Newly Added Employees</div></div>
                        <div class="card-body">
                            @forelse ($recentEmployees as $employee)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $employee->userDetail?->first_name ?? $employee->name ?? 'No Data Available' }}</span>
                                    <span class="text-muted">{{ optional($employee->created_at)->format('d-M-Y') }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No Data Available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Upcoming Holidays</div></div>
                        <div class="card-body">
                            @forelse ($upcomingHolidays as $holiday)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $holiday->name }}</span>
                                    <strong>{{ optional($holiday->from_date)->format('d-M-Y') }}</strong>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No Data Available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Attendance Chart Data</div></div>
                        <div class="card-body">
                            @forelse ($attendanceChart as $label => $value)
                                <div class="d-flex justify-content-between mb-2"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
                            @empty
                                <p class="text-muted mb-0">No Data Available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Leave Chart Data</div></div>
                        <div class="card-body">
                            @forelse ($leaveChart as $label => $value)
                                <div class="d-flex justify-content-between mb-2"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
                            @empty
                                <p class="text-muted mb-0">No Data Available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-round">
                        <div class="card-header"><div class="card-title">Payroll Chart Data</div></div>
                        <div class="card-body">
                            @forelse ($salaryChart as $label => $value)
                                <div class="d-flex justify-content-between mb-2"><span>{{ $label }}</span><strong>{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</strong></div>
                            @empty
                                <p class="text-muted mb-0">No Data Available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
