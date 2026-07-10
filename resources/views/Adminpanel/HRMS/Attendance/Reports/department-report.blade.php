@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Department Attendance Report</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Attendance', 'url' => route('hrms.attendance.index')],
                    ['label' => 'Reports', 'url' => route('hrms.attendance.reports')],
                    ['label' => 'Department Report'],
                ]])
            </div>
            <div class="ms-md-auto d-flex flex-wrap gap-2">
                <button disabled class="btn btn-label-info"><i class="fas fa-file-excel me-1" aria-hidden="true"></i> Export Excel</button>
                <button disabled class="btn btn-label-danger"><i class="fas fa-file-pdf me-1" aria-hidden="true"></i> Export PDF</button>
                <button class="btn btn-light" type="button" onclick="window.print()"><i class="fas fa-print me-1" aria-hidden="true"></i> Print</button>
            </div>
        </div>

        @include('Adminpanel.HRMS.Attendance.Reports._partials.filters')

        <div class="card card-round">
            <div class="card-body">
                @if($reports->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Employees</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Half Day</th>
                                    <th>Leave</th>
                                    <th class="text-nowrap">Average Attendance %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $row)
                                    <tr>
                                        <td class="text-truncate" style="max-width: 240px;">{{ $row['department'] }}</td>
                                        <td>{{ $row['total_employees'] }}</td>
                                        <td>{{ $row['present'] }}</td>
                                        <td>{{ $row['absent'] }}</td>
                                        <td>{{ $row['late'] }}</td>
                                        <td>{{ $row['half_day'] }}</td>
                                        <td>{{ $row['leave'] }}</td>
                                        <td>{{ $row['attendance_percentage'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $reports->links() }}</div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <h4 class="fw-bold">No Attendance Report Found</h4>
                        <p class="text-muted mb-0">Department attendance statistics will appear here when records are available.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
