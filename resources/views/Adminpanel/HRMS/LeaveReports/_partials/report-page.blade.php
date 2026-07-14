@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">{{ $report['title'] ?? 'Leave Report' }}</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave Reports', 'url' => route('hrms.leave-reports.index')],
                    ['label' => $report['title'] ?? 'Report'],
                ]])
            </div>
            <div class="ms-md-auto d-flex flex-wrap gap-2 d-print-none">
                <button class="btn btn-label-info" disabled><i class="fas fa-file-excel me-1"></i> Excel</button>
                <button class="btn btn-label-danger" disabled><i class="fas fa-file-pdf me-1"></i> PDF</button>
                <button class="btn btn-light" type="button" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
            </div>
        </div>

        @include('Adminpanel.HRMS.LeaveReports._partials.summary-cards')

        <div class="d-flex flex-wrap gap-2 mb-3 d-print-none">
            @foreach($reportRoutes as $label => $url)
                <a href="{{ $url }}" class="btn btn-sm {{ url()->current() === $url ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
            @endforeach
        </div>

        @include('Adminpanel.HRMS.LeaveReports._partials.filters', ['action' => url()->current()])

        <div class="card card-round">
            <div class="card-header"><div class="card-title">{{ $report['title'] ?? 'Report Data' }}</div></div>
            <div class="card-body">
                @include('Adminpanel.HRMS.LeaveReports._partials.tables')
            </div>
        </div>
    </div>
</div>
@endsection
