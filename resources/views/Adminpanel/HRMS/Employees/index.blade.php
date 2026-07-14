@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Employees</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Employees'],
                ]])
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <a href="{{ route('hrms.reporting-hierarchy.index') }}" class="btn btn-label-info btn-round me-2">
                    <i class="fas fa-sitemap me-1"></i> Reporting Hierarchy
                </a>
                <a href="{{ route('hrms.users.create') }}" class="btn btn-primary btn-round">
                    <i class="fas fa-plus me-1"></i> Add Employee
                </a>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Search & Filters</div>
            </div>
            <div class="card-body">
                <form action="{{ route('hrms.users.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Employee Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ $filters['name'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="emp_code">Employee Code</label>
                                <input type="text" name="emp_code" id="emp_code" class="form-control" value="{{ $filters['emp_code'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" name="department" id="department" class="form-control" value="{{ $filters['department'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="designation">Designation</label>
                                <input type="text" name="designation" id="designation" class="form-control" value="{{ $filters['designation'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="reporting_manager_id">Reporting Manager</label>
                                <select name="reporting_manager_id" id="reporting_manager_id" class="form-control">
                                    <option value="">All Managers</option>
                                    <option value="none" @selected((string) ($filters['reporting_manager_id'] ?? '') === 'none')>No Manager</option>
                                    @foreach($reportingManagers as $manager)
                                        @php
                                            $managerDetail = $manager->userDetail;
                                            $managerName = trim(($managerDetail?->first_name ?? '') . ' ' . ($managerDetail?->last_name ?? '')) ?: $manager->name;
                                        @endphp
                                        <option value="{{ $manager->id }}" @selected((string) ($filters['reporting_manager_id'] ?? '') === (string) $manager->id)>{{ $managerName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select name="role" id="role" class="form-control">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected((string) ($filters['role'] ?? '') === (string) $role->id)>{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="1" @selected((string) ($filters['status'] ?? '') === '1')>Active</option>
                                    <option value="0" @selected((string) ($filters['status'] ?? '') === '0')>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="sort">Sort By</label>
                                <select name="sort" id="sort" class="form-control">
                                    <option value="latest" @selected((string) ($filters['sort'] ?? '') === 'latest')>Latest</option>
                                    <option value="employee" @selected((string) ($filters['sort'] ?? '') === 'employee')>Employee</option>
                                    <option value="reporting_manager" @selected((string) ($filters['sort'] ?? '') === 'reporting_manager')>Reporting Manager</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="per_page">Per Page</label>
                                <select name="per_page" id="per_page" class="form-control">
                                    @foreach([10, 25, 50, 100] as $size)
                                        <option value="{{ $size }}" @selected((int) $perPage === $size)>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                                <a href="{{ route('hrms.users.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Employee List</div>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Profile Photo</th>
                                    <th>Employee Code</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Reporting Manager</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joining Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $employee)
                                    @php
                                        $detail = $employee->userDetail;
                                        $fullName = trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? '')) ?: $employee->name;
                                        $isActive = (bool) ($detail?->status ?? false);
                                        $manager = $detail?->reportingManager;
                                        $managerDetail = $manager?->userDetail;
                                        $managerName = $manager ? (trim(($managerDetail?->first_name ?? '') . ' ' . ($managerDetail?->last_name ?? '')) ?: $manager->name) : '-';
                                        $defaultPhoto = 'assets/img/profile.jpg';
                                        $photoPath = trim((string) ($detail?->profile_photo ?? ''));
                                        $photoUrl = $photoPath !== '' && file_exists(public_path($photoPath))
                                            ? asset($photoPath)
                                            : asset($defaultPhoto);
                                    @endphp
                                    <tr>
                                        <td>
                                            <img src="{{ $photoUrl }}" alt="{{ $fullName }}" class="avatar-img rounded-circle" style="width: 42px; height: 42px; object-fit: cover;">
                                        </td>
                                        <td>{{ $detail?->emp_code ?? '-' }}</td>
                                        <td>{{ $fullName }}</td>
                                        <td>{{ $employee->email }}</td>
                                        <td>{{ $detail?->department ?? '-' }}</td>
                                        <td>{{ $detail?->designation ?? '-' }}</td>
                                        <td>
                                            @if($manager)
                                                <a href="{{ route('hrms.users.show', $manager->id) }}">{{ $managerName }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $employee->roles->pluck('role_name')->join(', ') ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $isActive ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $isActive ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $detail?->joining_date ?? '-' }}</td>
                                        <td class="text-end">
                                            <div class="form-button-action justify-content-end">
                                                <a href="{{ route('hrms.users.show', $employee->id) }}" class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('hrms.attendance.employee', $employee->id) }}" class="btn btn-link btn-info btn-lg" data-bs-toggle="tooltip" title="View Attendance">
                                                    <i class="fa fa-calendar-check"></i>
                                                </a>
                                                <a href="{{ route('hrms.users.edit', $employee->id) }}" class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('hrms.users.update', $employee->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="{{ $isActive ? 0 : 1 }}">
                                                    <button type="submit" class="btn btn-link {{ $isActive ? 'btn-danger' : 'btn-success' }} btn-lg" data-bs-toggle="tooltip" title="{{ $isActive ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fa {{ $isActive ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <h4 class="fw-bold mb-3">No Employees Found</h4>
                        <a href="{{ route('hrms.users.create') }}" class="btn btn-primary btn-round">
                            <i class="fas fa-plus me-1"></i> Add Employee
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection