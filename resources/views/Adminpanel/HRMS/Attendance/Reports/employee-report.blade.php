@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Employee Attendance Report</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Attendance', 'url' => route('hrms.attendance.index')],
                    ['label' => 'Reports', 'url' => route('hrms.attendance.reports')],
                    ['label' => 'Employee Report'],
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
                                    <th>Photo</th>
                                    <th>Code</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Present</th>
                                    <th>Late</th>
                                    <th>Half Day</th>
                                    <th>Leave</th>
                                    <th>Holiday</th>
                                    <th>Weekly Off</th>
                                    <th>Absent</th>
                                    <th>Hours</th>
                                    <th>Avg In</th>
                                    <th>Avg Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $row)
                                    @php
                                        $photoPath = trim((string) ($row['employee']->userDetail?->profile_photo ?? ''));
                                        $photoUrl = $photoPath !== '' ? asset($photoPath) : asset('assets/img/profile.jpg');
                                    @endphp
                                    <tr>
                                        <td>
                                            <img src="{{ $photoUrl }}" class="rounded-circle" width="38" height="38" style="object-fit: cover;" alt="{{ $row['employee_name'] }}" onerror="this.onerror=null;this.src='{{ asset('assets/img/profile.jpg') }}';">
                                        </td>
                                        <td class="text-nowrap">{{ $row['employee_code'] }}</td>
                                        <td class="text-truncate" style="max-width: 220px;">{{ $row['employee_name'] }}</td>
                                        <td>{{ $row['department'] }}</td>
                                        <td>{{ $row['designation'] }}</td>
                                        <td>{{ $row['present'] }}</td>
                                        <td>{{ $row['late'] }}</td>
                                        <td>{{ $row['half_day'] }}</td>
                                        <td>{{ $row['leave'] }}</td>
                                        <td>{{ $row['holiday'] }}</td>
                                        <td>{{ $row['weekly_off'] }}</td>
                                        <td>{{ $row['absent'] }}</td>
                                        <td>{{ $row['working_hours'] }}</td>
                                        <td class="text-nowrap">{{ $row['average_check_in'] }}</td>
                                        <td class="text-nowrap">{{ $row['average_check_out'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $reports->links() }}</div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <h4 class="fw-bold">No Attendance Report Found</h4>
                        <p class="text-muted mb-0">Employee attendance statistics will appear here when records are available.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
