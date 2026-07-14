@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Reporting Hierarchy</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Employees', 'url' => route('hrms.users.index')],
                    ['label' => 'Reporting Hierarchy'],
                ]])
            </div>
            <div class="ms-md-auto py-2 py-md-0 d-print-none">
                <button type="button" class="btn btn-label-info btn-round me-2" disabled><i class="fas fa-file-excel me-1"></i> Excel</button>
                <button type="button" class="btn btn-label-danger btn-round me-2" disabled><i class="fas fa-file-pdf me-1"></i> PDF</button>
                <button type="button" class="btn btn-light btn-round" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
            </div>
        </div>

        <div class="card card-round d-print-none">
            <div class="card-header"><div class="card-title">Search & Filters</div></div>
            <div class="card-body">
                <form action="{{ route('hrms.reporting-hierarchy.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label for="name">Employee</label><input type="text" name="name" id="name" class="form-control" value="{{ $filters['name'] ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label for="department">Department</label><input type="text" name="department" id="department" class="form-control" value="{{ $filters['department'] ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label for="designation">Designation</label><input type="text" name="designation" id="designation" class="form-control" value="{{ $filters['designation'] ?? '' }}"></div></div>
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
                        <div class="col-md-3"><div class="form-group"><label for="per_page">Per Page</label><select name="per_page" id="per_page" class="form-control">@foreach([10, 25, 50, 100] as $size)<option value="{{ $size }}" @selected((int) $perPage === $size)>{{ $size }}</option>@endforeach</select></div></div>
                        <div class="col-md-3 d-flex align-items-end"><div class="form-group w-100"><button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Search</button><a href="{{ route('hrms.reporting-hierarchy.index') }}" class="btn btn-light">Reset</a></div></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header"><div class="card-title">Hierarchy Report</div></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead><tr><th>Employee</th><th>Department</th><th>Designation</th><th>Reporting Manager</th><th>Status</th><th>Organization Preview</th></tr></thead>
                        <tbody>
                            @forelse($reportRows as $employee)
                                @php
                                    $detail = $employee->userDetail;
                                    $employeeName = trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? '')) ?: $employee->name;
                                    $manager = $detail?->reportingManager;
                                    $managerDetail = $manager?->userDetail;
                                    $managerName = $manager ? (trim(($managerDetail?->first_name ?? '') . ' ' . ($managerDetail?->last_name ?? '')) ?: $manager->name) : '-';
                                    $isActive = (bool) ($detail?->status ?? false);
                                @endphp
                                <tr>
                                    <td><a href="{{ route('hrms.users.show', $employee->id) }}">{{ $employeeName }}</a><br><small class="text-muted">{{ $detail?->emp_code ?? '-' }}</small></td>
                                    <td>{{ $detail?->department ?? '-' }}</td>
                                    <td>{{ $detail?->designation ?? '-' }}</td>
                                    <td>@if($manager)<a href="{{ route('hrms.users.show', $manager->id) }}">{{ $managerName }}</a>@else - @endif</td>
                                    <td><span class="badge {{ $isActive ? 'badge-success' : 'badge-secondary' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span></td>
                                    <td>{{ $managerName }} <span class="text-muted">-></span> {{ $employeeName }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No Report Data Found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-print-none">{{ $reportRows->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
