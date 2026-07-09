@php
    $badgeClasses = [
        'Present' => 'badge-success',
        'Absent' => 'badge-danger',
        'Late' => 'badge-warning',
        'Half Day' => 'badge-info',
        'Leave' => 'badge-primary',
        'Holiday' => 'badge-secondary',
    ];
@endphp

@if($attendance->count())
    <div class="table-responsive">
        <table class="display table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Employee Code</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Attendance Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Working Hours</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendance as $record)
                    @php
                        $employee = $record->user;
                        $detail = $employee?->userDetail;
                        $employeeName = trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? ''))
                            ?: ($employee?->name ?? 'Unknown Employee');
                        $photoPath = trim((string) ($detail?->profile_photo ?? ''));
                        $photoUrl = $photoPath !== '' ? asset($photoPath) : asset('assets/img/profile.jpg');
                        $displayStatus = $record->display_status ?? $record->status ?? '-';
                    @endphp
                    <tr>
                        <td>
                            <img src="{{ $photoUrl }}" alt="{{ $employeeName }}" class="avatar-img rounded-circle"
                                style="width: 42px; height: 42px; object-fit: cover;"
                                onerror="this.onerror=null;this.src='{{ asset('assets/img/profile.jpg') }}';">
                        </td>
                        <td>{{ $detail?->emp_code ?? '-' }}</td>
                        <td>{{ $employeeName }}</td>
                        <td>{{ $detail?->department ?? '-' }}</td>
                        <td>{{ $detail?->designation ?? '-' }}</td>
                        <td>{{ $record->attendance_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $record->check_in ?? '-' }}</td>
                        <td>{{ $record->check_out ?? '-' }}</td>
                        <td>{{ $record->working_hours ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $badgeClasses[$displayStatus] ?? 'badge-secondary' }}">
                                {{ $displayStatus }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-link btn-primary btn-lg" data-bs-toggle="modal"
                                data-bs-target="#attendanceModal{{ $record->id }}" title="Quick view">
                                <i class="fa fa-eye"></i>
                            </button>
                            <a href="{{ route('hrms.attendance.show', $record->id) }}"
                                class="btn btn-link btn-primary btn-lg" title="View details">
                                <i class="fa fa-external-link-alt"></i>
                            </a>
                            @if($record->can_check_in ?? false)
                                <form action="{{ route('hrms.attendance.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $record->user_id }}">
                                    <button type="submit" class="btn btn-sm btn-success">Check In</button>
                                </form>
                            @endif
                            @if($record->can_check_out ?? false)
                                <form action="{{ route('hrms.attendance.update', $record->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-warning">Check Out</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $attendance->withQueryString()->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <h4 class="fw-bold">No Attendance Records Found</h4>
        <p class="text-muted mb-0">Attendance records will appear here when available.</p>
    </div>
@endif
