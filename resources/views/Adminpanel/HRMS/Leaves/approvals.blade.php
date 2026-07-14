@extends('Adminpanel.layout.mainlayout')

@section('content')
@php
    $filters = $filters ?? [];
    $statuses = $statuses ?? [];
    $approvalLevels = $approvalLevels ?? [];
    $dashboardCards = $dashboardCards ?? [];
    $summary = $summary ?? [];
@endphp

<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Leave Approvals</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave', 'url' => route('hrms.leave-apply.index')],
                    ['label' => 'Approvals'],
                ]])
            </div>
            <div class="ms-md-auto d-flex flex-wrap gap-2">
                <a href="{{ route('hrms.leave-apply.index') }}" class="btn btn-light btn-round"><i class="fas fa-list me-1"></i> Leave List</a>
                <button type="button" class="btn btn-light btn-round" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
            </div>
        </div>

        <div class="row row-cols-2 row-cols-md-4 row-cols-xl-7 g-3 mb-3">
            @foreach([
                ['pending', 'Pending', 'warning'],
                ['manager_pending', 'Manager Pending', 'info'],
                ['hr_pending', 'HR Pending', 'primary'],
                ['approved', 'Approved', 'success'],
                ['rejected', 'Rejected', 'danger'],
                ['cancelled', 'Cancelled', 'secondary'],
                ['revoked', 'Revoked', 'dark'],
            ] as [$key, $label, $color])
                <div class="col">
                    <div class="card card-stats card-round h-100 mb-0">
                        <div class="card-body text-center py-3">
                            <p class="card-category mb-1">{{ $label }}</p>
                            <h4 class="card-title text-{{ $color }} mb-0">{{ $summary[$key] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mb-3">
            @foreach($dashboardCards as $card)
                <div class="col-sm-6 col-lg-3 mb-3">
                    <div class="card card-stats card-round h-100 mb-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon"><div class="icon-big text-center icon-{{ $card['class'] }} bubble-shadow-small"><i class="fas {{ $card['icon'] }}"></i></div></div>
                                <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">{{ $card['label'] }}</p><h4 class="card-title">{{ $card['value'] }}</h4></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card card-round mb-3">
            <div class="card-header"><div class="card-title">Search & Filters</div></div>
            <div class="card-body">
                <form method="GET" action="{{ route('hrms.leave-apply.approvals') }}">
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>Employee</label><input type="text" name="employee" value="{{ $filters['employee'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Employee Code</label><input type="text" name="employee_code" value="{{ $filters['employee_code'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Department</label><input type="text" name="department" value="{{ $filters['department'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Designation</label><input type="text" name="designation" value="{{ $filters['designation'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Leave Type</label><select name="leave_type_id" class="form-control"><option value="">All Leave Types</option>@foreach($leaveTypes as $leaveType)<option value="{{ $leaveType->id }}" @selected((string)($filters['leave_type_id'] ?? '') === (string)$leaveType->id)>{{ $leaveType->leave_name }}</option>@endforeach</select></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="">All Statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>@endforeach</select></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Approval Level</label><select name="approval_level" class="form-control"><option value="">All Levels</option>@foreach($approvalLevels as $level)<option value="{{ $level }}" @selected(($filters['approval_level'] ?? '') === $level)>{{ ucfirst($level) }}</option>@endforeach</select></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Financial Year</label><input type="text" name="financial_year" value="{{ $filters['financial_year'] ?? '' }}" class="form-control" placeholder="2026-2027"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>From Date</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>To Date</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Per Page</label><select name="per_page" class="form-control">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 10) === $size)>{{ $size }}</option>@endforeach</select></div></div>
                        <div class="col-md-12 d-flex justify-content-end"><div class="form-group"><button class="btn btn-primary me-2" type="submit"><i class="fas fa-search me-1"></i> Search</button><a href="{{ route('hrms.leave-apply.approvals') }}" class="btn btn-light">Reset</a></div></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
                <div class="card-title mb-0">Pending Leave Requests</div>
                @if($canBulkApprove || $canBulkReject)
                    <div class="d-flex flex-wrap gap-2">
                        @if($canBulkApprove)<button type="button" class="btn btn-success btn-sm" data-bulk-action="approve"><i class="fas fa-check me-1"></i> Approve Selected</button>@endif
                        @if($canBulkReject)<button type="button" class="btn btn-danger btn-sm" data-bulk-action="reject"><i class="fas fa-times me-1"></i> Reject Selected</button>@endif
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if($leaves->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center"><input type="checkbox" class="form-check-input" data-select-all></th>
                                    <th>Photo</th>
                                    <th>Code</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Requested</th>
                                    <th>Payable</th>
                                    <th>Current Stage</th>
                                    <th>Applied</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves as $leave)
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="form-check-input" data-leave-select value="{{ $leave->id }}" data-approve-url="{{ route('hrms.leave-apply.approve', $leave->id) }}" data-reject-url="{{ route('hrms.leave-apply.reject', $leave->id) }}" @disabled(! $leave->can_approve && ! $leave->can_reject)></td>
                                        <td><img src="{{ $leave->employee_photo_url }}" class="rounded-circle border" width="42" height="42" style="object-fit: cover;" alt="{{ $leave->employee_name }}" onerror="this.onerror=null;this.src='{{ asset('assets/img/profile.jpg') }}';"></td>
                                        <td class="text-nowrap">{{ $leave->employee_code }}</td>
                                        <td class="fw-semibold text-truncate" style="max-width: 180px;">{{ $leave->employee_name }}</td>
                                        <td>{{ $leave->department }}</td>
                                        <td>{{ $leave->leave_type_label }}</td>
                                        <td class="text-nowrap">{{ $leave->from_date_label }}</td>
                                        <td class="text-nowrap">{{ $leave->to_date_label }}</td>
                                        <td>{{ $leave->requested_days_label }}</td>
                                        <td>{{ $leave->payable_days_label }}</td>
                                        <td>{{ $leave->current_stage_label }}</td>
                                        <td class="text-nowrap">{{ $leave->applied_date_label }}</td>
                                        <td><span class="badge {{ $leave->status_badge['class'] ?? 'badge-secondary' }} px-3 py-2">{{ $leave->status_label }}</span></td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('hrms.leave-apply.show', $leave->id) }}" class="btn btn-link btn-primary btn-lg" title="View"><i class="fas fa-eye"></i></a>
                                                @if($leave->can_approve)<button type="button" class="btn btn-link btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#approveLeaveModal{{ $leave->id }}" title="Approve"><i class="fas fa-check"></i></button>@endif
                                                @if($leave->can_reject)<button type="button" class="btn btn-link btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectLeaveModal{{ $leave->id }}" title="Reject"><i class="fas fa-times"></i></button>@endif
                                                @if($leave->can_revoke)<button type="button" class="btn btn-link btn-dark btn-lg" data-bs-toggle="modal" data-bs-target="#revokeLeaveModal{{ $leave->id }}" title="Revoke"><i class="fas fa-ban"></i></button>@endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $leaves->withQueryString()->links() }}</div>
                @else
                    <div class="text-center py-5"><i class="fas fa-calendar-times fa-3x text-muted mb-3"></i><h4 class="fw-bold">No Leave Requests Found</h4></div>
                @endif
            </div>
        </div>
    </div>
</div>

@foreach($leaves as $leave)
    @if($leave->can_approve)
        <div class="modal fade" id="approveLeaveModal{{ $leave->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="{{ route('hrms.leave-apply.approve', $leave->id) }}" method="POST" class="js-loading-form">@csrf<div class="modal-header"><h5 class="modal-title">Approve Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3" maxlength="1000"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success" data-loading-text="Approving..."><span class="spinner-border spinner-border-sm me-1 d-none"></span>Approve</button></div></form></div></div></div>
    @endif
    @if($leave->can_reject)
        <div class="modal fade" id="rejectLeaveModal{{ $leave->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="{{ route('hrms.leave-apply.reject', $leave->id) }}" method="POST" class="js-loading-form">@csrf<div class="modal-header"><h5 class="modal-title">Reject Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3" minlength="10" maxlength="1000" required></textarea><small class="text-muted">Minimum 10 characters.</small></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger" data-loading-text="Rejecting..."><span class="spinner-border spinner-border-sm me-1 d-none"></span>Reject</button></div></form></div></div></div>
    @endif
    @if($leave->can_revoke)
        <div class="modal fade" id="revokeLeaveModal{{ $leave->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="{{ route('hrms.leave-apply.revoke', $leave->id) }}" method="POST" class="js-loading-form">@csrf<div class="modal-header"><h5 class="modal-title">Revoke Approval</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p class="mb-2">This will restore leave balance.</p><p class="fw-semibold">Continue?</p><textarea name="remarks" class="form-control" rows="3" maxlength="1000" placeholder="Remarks"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark" data-loading-text="Revoking..."><span class="spinner-border spinner-border-sm me-1 d-none"></span>Revoke Approval</button></div></form></div></div></div>
    @endif
@endforeach

<div class="modal fade" id="bulkLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="bulkLeaveTitle">Bulk Action</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p id="bulkLeaveMessage" class="mb-3"></p><label class="form-label">Remarks</label><textarea id="bulkLeaveRemarks" class="form-control" rows="3"></textarea><div class="invalid-feedback d-block d-none" id="bulkLeaveError"></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="bulkLeaveSubmit"><span class="spinner-border spinner-border-sm me-1 d-none"></span>Continue</button></div></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.submitted === 'true') { event.preventDefault(); return; }
            form.dataset.submitted = 'true';
            var button = form.querySelector('button[type="submit"]');
            if (button) { button.disabled = true; var spinner = button.querySelector('.spinner-border'); if (spinner) spinner.classList.remove('d-none'); }
        });
    });
    var selectAll = document.querySelector('[data-select-all]');
    if (selectAll) { selectAll.addEventListener('change', function () { document.querySelectorAll('[data-leave-select]:not(:disabled)').forEach(function (box) { box.checked = selectAll.checked; }); }); }
    var bulkModalEl = document.getElementById('bulkLeaveModal');
    var bulkModal = bulkModalEl ? new bootstrap.Modal(bulkModalEl) : null;
    var bulkAction = null;
    document.querySelectorAll('[data-bulk-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            bulkAction = button.dataset.bulkAction;
            var selected = document.querySelectorAll('[data-leave-select]:checked');
            document.getElementById('bulkLeaveError').classList.toggle('d-none', selected.length > 0);
            if (selected.length === 0) { document.getElementById('bulkLeaveError').textContent = 'Select at least one leave request.'; return; }
            document.getElementById('bulkLeaveTitle').textContent = bulkAction === 'approve' ? 'Approve Selected' : 'Reject Selected';
            document.getElementById('bulkLeaveMessage').textContent = bulkAction === 'approve' ? 'Selected leave requests will be approved.' : 'Selected leave requests will be rejected.';
            document.getElementById('bulkLeaveRemarks').required = bulkAction === 'reject';
            document.getElementById('bulkLeaveRemarks').minLength = bulkAction === 'reject' ? 10 : 0;
            if (bulkModal) bulkModal.show();
        });
    });
    var bulkSubmit = document.getElementById('bulkLeaveSubmit');
    if (bulkSubmit) {
        bulkSubmit.addEventListener('click', function () {
            var remarks = document.getElementById('bulkLeaveRemarks').value || '';
            var error = document.getElementById('bulkLeaveError');
            if (bulkAction === 'reject' && remarks.trim().length < 10) { error.textContent = 'Rejection remarks must be at least 10 characters.'; error.classList.remove('d-none'); return; }
            var selected = Array.from(document.querySelectorAll('[data-leave-select]:checked'));
            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            bulkSubmit.disabled = true;
            bulkSubmit.querySelector('.spinner-border').classList.remove('d-none');
            Promise.all(selected.map(function (box) {
                var url = bulkAction === 'approve' ? box.dataset.approveUrl : box.dataset.rejectUrl;
                var body = new FormData();
                body.append('_token', token);
                body.append('remarks', remarks);
                return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: body });
            })).then(function () { window.location.reload(); }).catch(function () { error.textContent = 'Bulk action failed. Please try again.'; error.classList.remove('d-none'); bulkSubmit.disabled = false; bulkSubmit.querySelector('.spinner-border').classList.add('d-none'); });
        });
    }
});
</script>
@endsection
