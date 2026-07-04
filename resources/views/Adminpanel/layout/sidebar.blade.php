@php
    $isLeaveMenuActive = request()->routeIs('leavepolicy')
        || request()->routeIs('leave-types.*')
        || request()->routeIs('holidays.*')
        || request()->routeIs('hrms.leave-apply.*');

    $isHrmsMenuActive = request()->routeIs('hrms.dashboard')
        || request()->routeIs('hrms.users.*')
        || request()->routeIs('hrms.attendance.*')
        || request()->routeIs('hrms.salary.*')
        || request()->routeIs('hrms.roles.*')
        || request()->routeIs('hrms.company-setting.*')
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
                            <li class="{{ request()->routeIs('hrms.attendance.*') ? 'active' : '' }}">
                                <a href="{{ route('hrms.attendance.index') }}">
                                    <span class="sub-item">Attendance</span>
                                </a>
                            </li>
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
                                        <li class="{{ request()->routeIs('hrms.leave-apply.*') ? 'active' : '' }}">
                                            <a href="{{ route('hrms.leave-apply.index') }}">
                                                <span class="sub-item">Leave Apply</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
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
