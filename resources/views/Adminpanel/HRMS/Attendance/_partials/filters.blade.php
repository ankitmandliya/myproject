<div class="card card-round">
    <div class="card-header">
        <div class="card-title">Search &amp; Filters</div>
    </div>
    <div class="card-body">
        <form action="{{ route('hrms.attendance.index') }}" method="GET">
            <div class="row">
                @foreach([
                    'name' => 'Employee Name',
                    'emp_code' => 'Employee Code',
                    'department' => 'Department',
                    'designation' => 'Designation',
                ] as $field => $label)
                    <div class="col-sm-6 col-lg-3">
                        <div class="form-group">
                            <label for="{{ $field }}">{{ $label }}</label>
                            <input type="text" name="{{ $field }}" id="{{ $field }}" class="form-control"
                                value="{{ $filters[$field] ?? request($field) }}">
                        </div>
                    </div>
                @endforeach
                <div class="col-sm-6 col-lg-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Statuses</option>
                            @foreach(['Present', 'Absent', 'Late', 'Half Day', 'Leave', 'Holiday'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? request('status')) === $status)>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="form-group">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control"
                            value="{{ $filters['from_date'] ?? request('from_date') }}">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="form-group">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control"
                            value="{{ $filters['to_date'] ?? request('to_date') }}">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="form-group">
                        <label for="per_page">Per Page</label>
                        <select name="per_page" id="per_page" class="form-control">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) ($perPage ?? request('per_page', 10)) === $size)>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group pt-0">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <a href="{{ route('hrms.attendance.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
