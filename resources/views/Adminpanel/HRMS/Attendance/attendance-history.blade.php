@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">{{ $employee->name }} - Attendance History</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Attendance', 'url' => route('hrms.attendance.index')],
                    ['label' => 'History'],
                ]])
            </div>
            <div class="ms-md-auto">
                <a href="{{ route('hrms.attendance.calendar', ['employeeId' => $employee->id, 'month' => request('month')]) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-calendar-alt me-1" aria-hidden="true"></i> View Calendar
                </a>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">{{ $monthLabel ?? 'Monthly Attendance' }}</div>
            </div>
            <div class="card-body">
                @if($attendance->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">Date</th>
                                    <th class="text-nowrap">Check In</th>
                                    <th class="text-nowrap">Check Out</th>
                                    <th class="text-nowrap">Working Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendance as $record)
                                    <tr>
                                        <td class="text-nowrap">{{ $record->attendance_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="text-nowrap">{{ $record->check_in ?? '-' }}</td>
                                        <td class="text-nowrap">{{ $record->check_out ?? '-' }}</td>
                                        <td class="text-nowrap">{{ $record->working_hours ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $record->display_status ?? $record->status ?? '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($attendance, 'links'))
                        <div class="mt-3">{{ $attendance->withQueryString()->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <h4 class="fw-bold">No History Available</h4>
                        <p class="text-muted">Attendance history will appear here when records are available.</p>
                        <a href="{{ route('hrms.attendance.calendar', $employee->id) }}" class="btn btn-primary mt-2">View Calendar</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
