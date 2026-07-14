@if($attendance->count())
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-nowrap">Date</th>
                    <th>Employee</th>
                    <th class="text-nowrap">Check In</th>
                    <th class="text-nowrap">Check Out</th>
                    <th class="text-nowrap">Working Hours</th>
                    <th>Status</th>
                    <th class="text-nowrap">Leave Type</th>
                    <th>Reason</th>
                    <th class="text-nowrap">Approved By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendance as $record)
                    <tr>
                        <td class="text-nowrap">{{ $record->attendance_date?->format('d M Y') ?? '-' }}</td>
                        <td class="text-truncate" style="max-width: 220px;">{{ $record->user?->name ?? '-' }}</td>
                        <td class="text-nowrap">{{ $record->check_in ?? '-' }}</td>
                        <td class="text-nowrap">{{ $record->check_out ?? '-' }}</td>
                        <td class="text-nowrap">{{ $record->working_hours ?? '-' }}</td>
                        <td><span class="badge bg-{{ $record->status_badge ?? 'secondary' }}">{{ $record->display_status ?? $record->status ?? '-' }}</span></td>
                        <td class="text-nowrap">{{ $record->leave_type ?? $record->leaveType?->leave_name ?? '-' }}</td>
                        <td class="text-break">{{ $record->leave_reason ?? $record->reason ?? '-' }}</td>
                        <td class="text-nowrap">{{ $record->approved_by ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $attendance->withQueryString()->links() }}</div>
@else
    <div class="text-center py-5">
        <i class="fas fa-chart-bar fa-3x text-muted mb-3" aria-hidden="true"></i>
        <h4 class="fw-bold">No Attendance Report Found</h4>
        <p class="text-muted mb-0">Report data will appear here when attendance records are available.</p>
    </div>
@endif
