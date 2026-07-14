@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Leave Details</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave', 'url' => route('hrms.leave-apply.index')],
                    ['label' => 'Details'],
                ]])
            </div>
            <div class="ms-md-auto d-flex flex-wrap gap-2">
                <a href="{{ route('hrms.leave-apply.index') }}" class="btn btn-light btn-round"><i class="fas fa-arrow-left me-1"></i> Back</a>
                @if($leave->can_cancel)
                    <button type="button" class="btn btn-warning btn-round" data-bs-toggle="modal" data-bs-target="#cancelLeaveModal"><i class="fas fa-calendar-times me-1"></i> Cancel Leave</button>
                @endif
                @if($leave->can_revoke)
                    <button type="button" class="btn btn-dark btn-round" data-bs-toggle="modal" data-bs-target="#revokeLeaveModal"><i class="fas fa-ban me-1"></i> Revoke Approval</button>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-round">
                    <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-3">
                        <img src="{{ $leave->employee_photo_url }}" alt="{{ $leave->employee_name }}" class="avatar-img rounded-circle border" style="width: 64px; height: 64px; object-fit: cover;" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/img/profile.jpg') }}';">
                        <div class="flex-fill">
                            <div class="card-title mb-1">{{ $leave->employee_name }}</div>
                            <div class="text-muted small">{{ $leave->employee_code }} | {{ $leave->department }} | {{ $leave->designation }}</div>
                            <div class="text-muted small">Joining Date: {{ $leave->joining_date_label }}</div>
                        </div>
                        <span class="badge {{ $statusBadge['class'] ?? 'badge-secondary' }} px-3 py-2">{{ $statusBadge['label'] ?? $leave->status }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><strong>Leave Type:</strong> {{ $leave->leave_type_label }}</div>
                            <div class="col-md-6 mb-3"><strong>Current Stage:</strong> {{ $leave->current_stage_label }}</div>
                            <div class="col-md-6 mb-3"><strong>From:</strong> {{ $leave->from_date_label }}</div>
                            <div class="col-md-6 mb-3"><strong>To:</strong> {{ $leave->to_date_label }}</div>
                            <div class="col-md-6 mb-3"><strong>Applied Date:</strong> {{ $leave->applied_date_label }}</div>
                            <div class="col-md-6 mb-3"><strong>Remaining Balance:</strong> {{ $leave->remaining_balance }}</div>
                            <div class="col-md-12 mb-3"><strong>Attachment:</strong> -</div>
                            <div class="col-md-12"><strong>Reason:</strong><div class="mt-2 p-3 bg-light rounded text-break">{{ $leave->reason ?: '-' }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="card card-round">
                    <div class="card-header"><div class="card-title">Calculation Snapshot</div></div>
                    <div class="card-body">
                        <div class="row row-cols-2 row-cols-md-5 g-3 text-center">
                            <div class="col"><div class="border rounded p-3 h-100"><div class="text-muted small">Requested Days</div><strong>{{ $leave->requested_days_label }}</strong></div></div>
                            <div class="col"><div class="border rounded p-3 h-100"><div class="text-muted small">Holiday Days</div><strong>{{ $leave->holiday_days_label }}</strong></div></div>
                            <div class="col"><div class="border rounded p-3 h-100"><div class="text-muted small">Weekly Off</div><strong>{{ $leave->weekly_off_days_label }}</strong></div></div>
                            <div class="col"><div class="border rounded p-3 h-100"><div class="text-muted small">Sandwich Days</div><strong>{{ $leave->sandwich_summary['days'] ?? '0.00' }}</strong></div></div>
                            <div class="col"><div class="border rounded p-3 h-100"><div class="text-muted small">Payable Days</div><strong>{{ $leave->payable_days_label }}</strong></div></div>
                        </div>
                    </div>
                </div>

                <div class="card card-round">
                    <div class="card-header"><div class="card-title">Approval History</div></div>
                    <div class="card-body">
                        @if(count($leave->audit_items ?? []))
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light"><tr><th>User</th><th>Role</th><th>Action</th><th>Date</th><th>Time</th><th>Remarks</th><th>IP Address</th></tr></thead>
                                    <tbody>@foreach($leave->audit_items as $item)<tr><td>{{ $item['user'] }}</td><td>{{ $item['role'] }}</td><td><span class="badge badge-{{ $item['badge'] }}">{{ $item['action'] }}</span></td><td>{{ $item['date'] }}</td><td>{{ $item['time'] }}</td><td class="text-break">{{ $item['remarks'] }}</td><td>{{ $item['ip'] }}</td></tr>@endforeach</tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted">No approval history available.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @include('Adminpanel.HRMS.Leaves._partials.timeline', ['leave' => $leave])
            </div>
        </div>
    </div>
</div>

@if($leave->can_cancel)
    <div class="modal fade" id="cancelLeaveModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="{{ route('hrms.leave-apply.cancel', $leave->id) }}" method="POST" class="js-loading-form">@csrf<div class="modal-header"><h5 class="modal-title">Cancel Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>Are you sure?</p><p class="fw-semibold">This request will be cancelled.</p><textarea name="remarks" class="form-control" rows="3" maxlength="1000" placeholder="Remarks"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">No</button><button type="submit" class="btn btn-warning" data-loading-text="Cancelling..."><span class="spinner-border spinner-border-sm me-1 d-none"></span>Cancel Leave</button></div></form></div></div></div>
@endif
@if($leave->can_revoke)
    <div class="modal fade" id="revokeLeaveModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="{{ route('hrms.leave-apply.revoke', $leave->id) }}" method="POST" class="js-loading-form">@csrf<div class="modal-header"><h5 class="modal-title">Revoke Approval</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>This will restore leave balance.</p><p class="fw-semibold">Continue?</p><textarea name="remarks" class="form-control" rows="3" maxlength="1000" placeholder="Remarks"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark" data-loading-text="Revoking..."><span class="spinner-border spinner-border-sm me-1 d-none"></span>Revoke Approval</button></div></form></div></div></div>
@endif

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
});
</script>
@endsection

