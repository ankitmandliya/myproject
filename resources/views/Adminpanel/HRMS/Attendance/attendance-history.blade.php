@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="pt-2 pb-4">
            <h3 class="fw-bold mb-3">{{ $employee->name }} - Attendance History</h3>
            @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                ['label' => 'HRMS'],
                ['label' => 'Attendance', 'url' => route('hrms.attendance.index')],
                ['label' => 'History'],
            ]])
        </div>
        <div class="card card-round">
            <div class="card-header"><div class="card-title">{{ $monthLabel ?? 'Monthly Attendance' }}</div></div>
            <div class="card-body">
                @if($attendance->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Working Hours</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($attendance as $record)
                                    <tr>
                                        <td>{{ $record->attendance_date?->format('d M Y') ?? '-' }}</td>
                                        <td>{{ $record->check_in ?? '-' }}</td>
                                        <td>{{ $record->check_out ?? '-' }}</td>
                                        <td>{{ $record->working_hours ?? '-' }}</td>
                                        <td>{{ $record->display_status ?? $record->status ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($attendance, 'links'))
                        <div class="mt-3">{{ $attendance->withQueryString()->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-5"><h4>No Attendance Found</h4><a href="{{ route('hrms.attendance.calendar', $employee->id) }}" class="btn btn-primary mt-2">View Calendar</a></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
