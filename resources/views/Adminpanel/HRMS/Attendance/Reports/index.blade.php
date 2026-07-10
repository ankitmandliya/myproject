@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Attendance Reports</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Attendance', 'url' => route('hrms.attendance.index')],
                    ['label' => 'Reports'],
                ]])
            </div>
            <div class="ms-md-auto d-flex flex-wrap gap-2">
                <button class="btn btn-label-info" disabled><i class="fas fa-file-excel me-1" aria-hidden="true"></i> Export Excel</button>
                <button class="btn btn-label-danger" disabled><i class="fas fa-file-pdf me-1" aria-hidden="true"></i> Export PDF</button>
                <button class="btn btn-light" type="button" onclick="window.print()"><i class="fas fa-print me-1" aria-hidden="true"></i> Print</button>
            </div>
        </div>

        @include('Adminpanel.HRMS.Attendance.Reports._partials.summary-cards')

        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('hrms.attendance.reports.employees') }}" class="btn btn-primary">Employee Report</a>
            <a href="{{ route('hrms.attendance.reports.departments') }}" class="btn btn-info">Department Report</a>
            <a href="{{ route('hrms.attendance.reports.monthly') }}" class="btn btn-secondary">Monthly Report</a>
        </div>

        @include('Adminpanel.HRMS.Attendance.Reports._partials.filters')

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Attendance Records</div>
            </div>
            <div class="card-body">
                @include('Adminpanel.HRMS.Attendance.Reports._partials.attendance-table')
            </div>
        </div>
    </div>
</div>
@endsection
