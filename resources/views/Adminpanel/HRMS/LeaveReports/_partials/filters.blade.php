<div class="card card-round mb-3 d-print-none">
    <div class="card-body">
        <form method="GET" action="{{ $action ?? url()->current() }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Financial Year</label>
                <input type="text" name="financial_year" value="{{ $filters['financial_year'] ?? '' }}" class="form-control" placeholder="2026-2027">
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    <option value="">All</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" @selected((string)($filters['month'] ?? '') === (string)$month)>{{ \Carbon\Carbon::create(null, $month, 1)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Employee</label>
                <input type="text" name="employee" value="{{ $filters['employee'] ?? '' }}" class="form-control" placeholder="Name">
            </div>
            <div class="col-md-2">
                <label class="form-label">Employee Code</label>
                <input type="text" name="employee_code" value="{{ $filters['employee_code'] ?? '' }}" class="form-control" placeholder="Code">
            </div>
            <div class="col-md-2">
                <label class="form-label">Department</label>
                <input type="text" name="department" value="{{ $filters['department'] ?? '' }}" class="form-control" placeholder="Department">
            </div>
            <div class="col-md-2">
                <label class="form-label">Designation</label>
                <input type="text" name="designation" value="{{ $filters['designation'] ?? '' }}" class="form-control" placeholder="Designation">
            </div>
            <div class="col-md-2">
                <label class="form-label">Leave Type</label>
                <select name="leave_type_id" class="form-select">
                    <option value="">All</option>
                    @foreach($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}" @selected((string)($filters['leave_type_id'] ?? '') === (string)$leaveType->id)>{{ $leaveType->leave_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Approval Stage</label>
                <select name="approval_stage" class="form-select">
                    <option value="">All</option>
                    @foreach($approvalStages as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['approval_stage'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Approver</label>
                <select name="approver_id" class="form-select">
                    <option value="">All</option>
                    @foreach($approvers as $approver)
                        <option value="{{ $approver['id'] }}" @selected((string)($filters['approver_id'] ?? '') === (string)$approver['id'])>{{ $approver['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Rows</label>
                <select name="per_page" class="form-select">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected((int)($filters['per_page'] ?? 25) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i> Search</button>
                <a href="{{ $action ?? url()->current() }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>
</div>
