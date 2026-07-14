@php
    $isLeaveMenuActive = request()->routeIs('leavepolicy')
        || request()->routeIs('leave-types.*')
        || request()->routeIs('holidays.*')
        || request()->routeIs('hrms.leave-apply.*');

    $canManageAttendance = auth()->user()?->roles?->pluck('role_name')->intersect(['Admin', 'HR'])->isNotEmpty() ?? false;
    $isReportingManagerForLeave = auth()->check() && \App\Models\UserDetail::where('reporting_manager_id', (int) auth()->id())->exists();
    $canManageLeaveApproval = $canManageAttendance || $isReportingManagerForLeave;
    $isLeaveReportsActive = request()->routeIs('hrms.leave-reports.*');
    $isFinancialYearActive = request()->routeIs('hrms.financial-year.*');
    $isAttendanceMenuActive = request()->routeIs('hrms.attendance.index')
        || request()->routeIs('hrms.attendance.create')
        || request()->routeIs('hrms.attendance.show')
        || request()->routeIs('hrms.attendance.edit')
        || request()->routeIs('hrms.attendance.calendar')
        || request()->routeIs('hrms.attendance.employee')
        || request()->routeIs('hrms.attendance.history')
        || request()->routeIs('hrms.my-attendance');
    $isAttendanceReportsActive = request()->routeIs('hrms.attendance.reports*');

    $isHrmsMenuActive = request()->routeIs('hrms.dashboard')
        || request()->routeIs('hrms.users.*')
        || request()->routeIs('hrms.reporting-hierarchy.*')
        || $isAttendanceMenuActive
        || $isAttendanceReportsActive
        || request()->routeIs('hrms.salary.*')
        || request()->routeIs('hrms.roles.*')
        || request()->routeIs('hrms.company-setting.*')
        || $isLeaveReportsActive
        || $isLeaveMenuActive;
@endphp

<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('dashboard') }}" class="logo">
                <img src="{{ asset('assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="navbar-brand" height="20" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item {{ $isHrmsMenuActive ? 'active submenu' : '' }}">
                    <a data-bs-toggle="collapse" href="#hrmsMenu" class="{{ $isHrmsMenuActive ? '' : 'collapsed' }}" aria-expanded="{{ $isHrmsMenuActive ? 'true' : 'false' }}">
                        <i class="fas fa-users-cog"></i>
                        <p>HRMS</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ $isHrmsMenuActive ? 'show' : '' }}" id="hrmsMenu">
                        <ul class="nav nav-collapse">
                            <li class="{{ request()->routeIs('hrms.dashboard') ? 'active' : '' }}">
                                <a href="{{ route('hrms.dashboard') }}">
                                    <span class="sub-item">Dashboard</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('hrms.users.*') ? 'active' : '' }}">
                                <a href="{{ route('hrms.users.index') }}">
                                    <span class="sub-item">Employees</span>
                                </a>
                            </li>
                            @if($canManageAttendance)
                                <li class="{{ request()->routeIs('hrms.reporting-hierarchy.*') ? 'active' : '' }}">
                                    <a href="{{ route('hrms.reporting-hierarchy.index') }}">
                                        <span class="sub-item">Reporting Hierarchy</span>
                                    </a>
                                </li>
                            @endif
                            <li class="{{ request()->routeIs('hrms.notifications.*') ? 'active' : '' }}">
                                <a href="{{ route('hrms.notifications.index') }}">
                                    <span class="sub-item">Notifications</span>
                                </a>
                            </li>
                            <li class="{{ $isAttendanceMenuActive ? 'active' : '' }}">
                                <a href="{{ $canManageAttendance ? route('hrms.attendance.index') : route('hrms.my-attendance') }}">
                                    <span class="sub-item">{{ $canManageAttendance ? 'Attendance' : 'My Attendance' }}</span>
                                </a>
                            </li>
                            @if($canManageAttendance)
                                <li class="{{ $isAttendanceReportsActive ? 'active' : '' }}">
                                    <a href="{{ route('hrms.attendance.reports') }}">
                                        <span class="sub-item">Attendance Reports</span>
                                    </a>
                                </li>
                            @endif
                            <li class="{{ $isLeaveMenuActive ? 'active submenu' : '' }}">
                                <a data-bs-toggle="collapse" href="#hrmsLeaveMenu" class="{{ $isLeaveMenuActive ? '' : 'collapsed' }}" aria-expanded="{{ $isLeaveMenuActive ? 'true' : 'false' }}">
                                    <span class="sub-item">Leave Management</span>
                                    <span class="caret"></span>
                                </a>
                                <div class="collapse {{ $isLeaveMenuActive ? 'show' : '' }}" id="hrmsLeaveMenu">
                                    <ul class="nav nav-collapse subnav">
                                        <li class="{{ request()->routeIs('leavepolicy') || request()->routeIs('leave-types.*') ? 'active' : '' }}">
                                            <a href="{{ route('leavepolicy') }}">
                                                <span class="sub-item">Leave Types</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                                            <a href="{{ route('holidays.index') }}">
                                                <span class="sub-item">Holidays</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('hrms.leave-apply.index') || request()->routeIs('hrms.leave-apply.create') || request()->routeIs('hrms.leave-apply.edit') || request()->routeIs('hrms.leave-apply.show') || request()->routeIs('hrms.leave-apply.calendar') || request()->routeIs('hrms.leave-apply.history') ? 'active' : '' }}">
                                            <a href="{{ route('hrms.leave-apply.index') }}">
                                                <span class="sub-item">Leave Apply</span>
                                            </a>
                                        </li>
                                        @if ($canManageLeaveApproval)
                                            <li class="{{ request()->routeIs('hrms.leave-apply.approvals') ? 'active' : '' }}">
                                                <a href="{{ route('hrms.leave-apply.approvals') }}">
                                                    <span class="sub-item">Leave Approvals</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($canManageAttendance)
                                            <li class="{{ $isLeaveReportsActive ? 'active' : '' }}">
                                                <a href="{{ route('hrms.leave-reports.index') }}">
                                                    <span class="sub-item">Leave Reports</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                            @if($canManageAttendance)
                                <li class="{{ $isFinancialYearActive ? 'active' : '' }}">
                                    <a href="{{ route('hrms.financial-year.index') }}">
                                        <span class="sub-item">Financial Year Closing</span>
                                    </a>
                                </li>
                            @endif
                            <li class="{{ request()->routeIs('hrms.salary.*') ? 'active' : '' }}">
                                <a href="{{ route('hrms.salary.index') }}">
                                    <span class="sub-item">Payroll</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('hrms.roles.*') ? 'active' : '' }}">
                                <a href="{{ route('hrms.roles.index') }}">
                                    <span class="sub-item">Roles & Permissions</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('hrms.company-setting.*') ? 'active' : '' }}">
                                <a href="{{ route('hrms.company-setting.index') }}">
                                    <span class="sub-item">Company Settings</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- End Sidebar -->
