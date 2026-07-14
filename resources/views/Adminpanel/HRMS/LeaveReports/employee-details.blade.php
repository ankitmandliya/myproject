@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Employee Leave Details</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave Reports', 'url' => route('hrms.leave-reports.index')],
                    ['label' => 'Employee Details'],
                ]])
            </div>
            <div class="ms-md-auto d-flex gap-2 d-print-none">
                <a href="{{ route('hrms.leave-reports.employee', request()->query()) }}" class="btn btn-light">Back</a>
                <button class="btn btn-light" type="button" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
            </div>
        </div>

        <div class="card card-round mb-3">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-3">
                <img src="{{ $report['employee']['photo'] }}" alt="Employee" class="rounded-circle" width="72" height="72">
                <div>
                    <h4 class="fw-bold mb-1">{{ $report['employee']['name'] }}</h4>
                    <div class="text-muted">{{ $report['employee']['employee_code'] }} · {{ $report['employee']['department'] }} · {{ $report['employee']['designation'] }}</div>
                    <div class="text-muted">Joined {{ $report['employee']['joining_date'] }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card card-round h-100">
                    <div class="card-header"><div class="card-title">Leave Balances</div></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Type</th><th>Allocated</th><th>Used</th><th>Remaining</th><th>Carry Forward</th></tr></thead>
                            <tbody>
                                @forelse($report['balances'] as $balance)
                                    <tr><td>{{ $balance['leave_type'] }}</td><td>{{ $balance['allocated'] }}</td><td>{{ $balance['used'] }}</td><td>{{ $balance['remaining'] }}</td><td>{{ $balance['carry_forward'] }}</td></tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted text-center">No Report Data Found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card card-round h-100">
                    <div class="card-header"><div class="card-title">Attendance & Leave Summary</div></div>
                    <div class="card-body">
                        <div class="row text-center">
                            @foreach($report['attendance_summary'] as $label => $value)
                                <div class="col-6 mb-3"><div class="text-muted small">{{ \Illuminate\Support\Str::headline($label) }}</div><div class="fw-bold fs-4">{{ $value }}</div></div>
                            @endforeach
                            <div class="col-6 mb-3"><div class="text-muted small">Holidays</div><div class="fw-bold fs-4">{{ $report['holiday_summary']['total'] }}</div></div>
                            <div class="col-6 mb-3"><div class="text-muted small">Sandwich Days</div><div class="fw-bold fs-4">{{ $report['sandwich_count'] }}</div></div>
                            <div class="col-6 mb-3"><div class="text-muted small">LWP Days</div><div class="fw-bold fs-4">{{ $report['lwp_count'] }}</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-round mb-3">
            <div class="card-header"><div class="card-title">Leave History</div></div>
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($report['leave_history'] as $leave)
                            <tr><td>{{ $leave['leave_type'] }}</td><td>{{ $leave['from_date'] }}</td><td>{{ $leave['to_date'] }}</td><td>{{ $leave['days'] }}</td><td>{{ $leave['status'] }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center">No Report Data Found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header"><div class="card-title">Approval History</div></div>
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Leave Type</th><th>Action</th><th>Status</th><th>User</th><th>Remarks</th><th>At</th></tr></thead>
                    <tbody>
                        @forelse($report['approval_history'] as $item)
                            <tr><td>{{ $item['leave_type'] }}</td><td>{{ $item['action'] }}</td><td>{{ $item['status'] }}</td><td>{{ $item['user'] }}</td><td>{{ $item['remarks'] }}</td><td>{{ $item['at'] }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted text-center">No Report Data Found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
