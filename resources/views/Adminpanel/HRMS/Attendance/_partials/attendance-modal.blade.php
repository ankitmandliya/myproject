@foreach($attendance as $record)
    @php
        $employee = $record->user;
        $detail = $employee?->userDetail;
        $employeeName = trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? ''))
            ?: ($employee?->name ?? 'Unknown Employee');
        $displayStatus = $record->display_status ?? $record->status ?? '-';
    @endphp
    <div class="modal fade" id="attendanceModal{{ $record->id }}" tabindex="-1"
        aria-labelledby="attendanceModalLabel{{ $record->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalLabel{{ $record->id }}">Attendance Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Employee</dt><dd class="col-sm-7">{{ $employeeName }}</dd>
                        <dt class="col-sm-5">Employee Code</dt><dd class="col-sm-7">{{ $detail?->emp_code ?? '-' }}</dd>
                        <dt class="col-sm-5">Date</dt><dd class="col-sm-7">{{ $record->attendance_date?->format('d M Y') ?? '-' }}</dd>
                        <dt class="col-sm-5">Check In</dt><dd class="col-sm-7">{{ $record->check_in ?? '-' }}</dd>
                        <dt class="col-sm-5">Check Out</dt><dd class="col-sm-7">{{ $record->check_out ?? '-' }}</dd>
                        <dt class="col-sm-5">Working Hours</dt><dd class="col-sm-7">{{ $record->working_hours ?? '-' }}</dd>
                        <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ $displayStatus }}</dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('hrms.attendance.show', $record->id) }}" class="btn btn-primary">Full Details</a>
                </div>
            </div>
        </div>
    </div>
@endforeach
