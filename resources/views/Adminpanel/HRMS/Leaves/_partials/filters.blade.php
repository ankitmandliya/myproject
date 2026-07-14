@php
    $filters = $filters ?? request()->only(['employee', 'employee_code', 'department', 'leave_type_id', 'status', 'from_date', 'to_date', 'per_page']);
    $leaveTypes = $leaveTypes ?? collect();
@endphp

<div class="card card-round">
    <div class="card-header">
        <div class="card-title">Search & Filters</div>
    </div>
    <div class="card-body">
        <form action="{{ route('hrms.leave-apply.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="employee">Employee</label>
                        <input type="text" name="employee" id="employee" class="form-control" value="{{ $filters['employee'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="employee_code">Employee Code</label>
                        <input type="text" name="employee_code" id="employee_code" class="form-control" value="{{ $filters['employee_code'] ?? '' }}">
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
                        <label for="leave_type_id">Leave Type</label>
                        <select name="leave_type_id" id="leave_type_id" class="form-control">
                            <option value="">All Leave Types</option>
                            @foreach ($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}" @selected((string) ($filters['leave_type_id'] ?? '') === (string) $leaveType->id)>
                                    {{ $leaveType->leave_name ?? $leaveType->name ?? 'Leave Type' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Statuses</option>
                            @foreach (['Pending', 'Approved', 'Rejected', 'Cancelled', 'Revoked'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="per_page">Per Page</label>
                        <select name="per_page" id="per_page" class="form-control">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <a href="{{ route('hrms.leave-apply.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

