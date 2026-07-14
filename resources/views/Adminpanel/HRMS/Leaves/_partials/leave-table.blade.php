@php
    $leaves = $leaves ?? collect();
@endphp

@if ($leaves->count() > 0)
    <div class="table-responsive">
        <table class="display table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th class="text-center">Photo</th>
                    <th>Employee Code</th>
                    <th>Employee Name</th>
                    <th>Leave Type</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th class="text-center">Days</th>
                    <th>Applied Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaves as $leave)
                    @php
                        $employeeName = $leave->employee_name ?? '-';
                        $photoUrl = $leave->employee_photo_url ?? asset('assets/img/profile.jpg');
                        $status = $leave->status ?? 'Pending';
                        $badge = $leave->status_badge ?? ['class' => 'badge-secondary', 'label' => $status];
                    @endphp
                    <tr>
                        <td class="text-center">
                            <img src="{{ $photoUrl }}" alt="{{ $employeeName }}" class="avatar-img rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;" loading="lazy">
                        </td>
                        <td>{{ $leave->employee_code ?? '-' }}</td>
                        <td class="fw-semibold">{{ $employeeName }}</td>
                        <td>{{ $leave->leave_type_label }}</td>
                        <td>{{ $leave->from_date_label }}</td>
                        <td>{{ $leave->to_date_label }}</td>
                        <td class="text-center">{{ $leave->duration_text }}</td>
                        <td>{{ $leave->applied_date_label }}</td>
                        <td class="text-center">
                            <span class="badge {{ $badge['class'] ?? 'badge-secondary' }} px-3 py-2">{{ $badge['label'] ?? $status }}</span>
                        </td>
                        <td class="text-end">
                            <div class="form-button-action justify-content-end gap-1">
                                <a href="{{ route('hrms.leave-apply.show', $leave->id) }}" class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip" title="View timeline" aria-label="View timeline">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @if ($status === 'Pending')
                                    <a href="{{ route('hrms.leave-apply.edit', $leave->id) }}" class="btn btn-link btn-primary btn-lg" data-bs-toggle="tooltip" title="Edit pending leave" aria-label="Edit pending leave">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @if ($leave->can_cancel ?? false)
                                        <button type="button" class="btn btn-link btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#cancelLeaveModal{{ $leave->id }}" title="Cancel pending leave" aria-label="Cancel pending leave">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @foreach ($leaves as $leave)
        @if (($leave->can_cancel ?? false) && ($leave->status ?? 'Pending') === 'Pending')
            <div class="modal fade" id="cancelLeaveModal{{ $leave->id }}" tabindex="-1" aria-labelledby="cancelLeaveModalLabel{{ $leave->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelLeaveModalLabel{{ $leave->id }}">Cancel Leave Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Cancel {{ $leave->employee_name ?? 'this employee' }}'s pending leave request from {{ $leave->from_date_label }} to {{ $leave->to_date_label }}?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep Request</button>
                            <form action="{{ route('hrms.leave-apply.cancel', $leave->id) }}" method="POST" class="d-inline js-loading-form">
                                @csrf
                                <input type="hidden" name="remarks" value="Cancelled by employee">
                                <button type="submit" class="btn btn-danger" data-loading-text="Cancelling...">
                                    <span class="spinner-border spinner-border-sm me-1 d-none" aria-hidden="true"></span>
                                    Cancel Leave
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@else
    <div class="text-center py-5">
        <div class="mb-3 text-muted"><i class="fas fa-calendar-times fa-3x"></i></div>
        <h4 class="fw-bold mb-3">No Leave Requests Found</h4>
        <a href="{{ route('hrms.leave-apply.create') }}" class="btn btn-primary btn-round">
            <i class="fas fa-plus me-1"></i> Apply Leave
        </a>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.submitted === 'true') {
                event.preventDefault();
                return;
            }
            form.dataset.submitted = 'true';
            var button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                var spinner = button.querySelector('.spinner-border');
                if (spinner) {
                    spinner.classList.remove('d-none');
                }
                if (button.dataset.loadingText) {
                    var textNode = Array.prototype.slice.call(button.childNodes).reverse().find(function (node) {
                        return node.nodeType === Node.TEXT_NODE;
                    });
                    if (textNode) {
                        textNode.textContent = ' ' + button.dataset.loadingText;
                    }
                }
            }
        });
    });
});
</script>

