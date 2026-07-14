<li id="attendance-widget-live" data-widget-url="{{ route('hrms.attendance.widget') }}" class="nav-item topbar-icon dropdown hidden-caret">
    <a class="nav-link dropdown-toggle" href="#" id="attendanceWidgetDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Attendance: {{ $attendanceWidget['status'] }}">
        <i class="fas fa-user-clock" aria-hidden="true"></i>
        <span class="d-none d-xl-inline ms-1 text-truncate" style="max-width: 140px;">{{ $attendanceWidget['status'] }}</span>
    </a>
    <div class="dropdown-menu dropdown-menu-end p-3 shadow" aria-labelledby="attendanceWidgetDropdown" style="min-width: min(340px, calc(100vw - 2rem));">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div class="min-w-0">
                <h6 class="fw-bold mb-0">Attendance</h6>
                <small class="text-muted d-block text-truncate">{{ $attendanceWidget['employeeName'] }} · {{ $attendanceWidget['todayDate'] }}</small>
            </div>
            <span class="badge bg-{{ $attendanceWidget['statusBadge'] }} flex-shrink-0">{{ $attendanceWidget['status'] }}</span>
        </div>

        @if($attendanceWidget['isCompanyHoliday'])
            <div class="alert alert-secondary py-2 mb-3" role="status">
                <strong>Today's Holiday</strong><br>
                {{ $attendanceWidget['holidayName'] }} ({{ $attendanceWidget['holidayDate'] }})<br>
                <small>Attendance is disabled today.</small>
            </div>
        @elseif($attendanceWidget['isApprovedLeave'] && ! $attendanceWidget['canCheckIn'] && ! $attendanceWidget['canCheckOut'])
            <div class="alert alert-primary py-2 mb-3" role="status">
                <strong>{{ $attendanceWidget['leaveStatusLabel'] ?? 'Approved Leave' }}</strong><br>
                <span>{{ $attendanceWidget['leaveType'] ?? 'Leave' }}</span>
                @if(! empty($attendanceWidget['approvedBy']))
                    <br><small>Approved by {{ $attendanceWidget['approvedBy'] }}</small>
                @endif
                @if(! empty($attendanceWidget['leaveReason']))
                    <br><small class="text-break">{{ $attendanceWidget['leaveReason'] }}</small>
                @endif
                <br><small>{{ $attendanceWidget['attendanceMessage'] ?? 'Attendance not required today' }}</small>
            </div>
        @elseif($attendanceWidget['isWeeklyOff'])
            <div class="alert alert-secondary py-2 mb-3" role="status">
                Today is Weekly Off ({{ $attendanceWidget['weeklyOff'] }}).<br>
                <small>Attendance is not required.</small>
            </div>
        @else
            <div class="row g-2 small mb-3">
                <div class="col-6"><span class="text-muted">Check In</span><br><strong>{{ $attendanceWidget['checkInTime'] ?? '-' }}</strong></div>
                <div class="col-6"><span class="text-muted">Check Out</span><br><strong>{{ $attendanceWidget['checkOutTime'] ?? '-' }}</strong></div>
                @if($attendanceWidget['workingHours'] !== null)
                    <div class="col-12"><span class="text-muted">Working Hours:</span> {{ $attendanceWidget['workingHours'] }}</div>
                @endif
            </div>
        @endif

        <div class="small border-top pt-2 mb-3">
            <div>Office Hours: <strong>{{ $attendanceWidget['officeStartTime'] }} - {{ $attendanceWidget['officeEndTime'] }}</strong></div>
            <div>Late After: <strong>{{ $attendanceWidget['lateThreshold'] }}</strong></div>
            <div>Half Day After: <strong>{{ $attendanceWidget['halfDayThreshold'] }}</strong></div>
            <div>Current date and time: <strong>{{ $attendanceWidget['todayDate'] }} {{ $attendanceWidget['currentTime'] }}</strong></div>
        </div>

        @if($attendanceWidget['isApprovedLeave'] && ! $attendanceWidget['canCheckIn'] && ! $attendanceWidget['canCheckOut'])
            <div class="border-top pt-3 small fw-semibold text-muted">{{ $attendanceWidget['attendanceMessage'] ?? 'Attendance not required today' }}</div>
        @else
            <div class="d-flex align-items-center justify-content-between gap-3 border-top pt-3">
                <span class="fw-semibold">{{ $attendanceWidget['attendanceCompleted'] ? 'Attendance Completed' : ($attendanceWidget['canCheckOut'] ? 'ON' : 'OFF') }}</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="spinner-border spinner-border-sm d-none" id="attendanceToggleSpinner" aria-hidden="true"></span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" aria-label="Toggle attendance" @checked($attendanceWidget['canCheckOut'] || $attendanceWidget['attendanceCompleted']) @disabled($attendanceWidget['attendanceCompleted'] || (! $attendanceWidget['canCheckIn'] && ! $attendanceWidget['canCheckOut'])) @if($attendanceWidget['canCheckIn']) data-attendance-toggle data-confirm-target="#attendanceCheckInModal" @elseif($attendanceWidget['canCheckOut']) data-attendance-toggle data-confirm-target="#attendanceCheckoutModal" @endif>
                    </div>
                </div>
            </div>
        @endif
    </div>
</li>
