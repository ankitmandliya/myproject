@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Attendance</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Attendance'],
                ]])
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button type="button" class="btn btn-label-info btn-round" disabled>
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </div>

        @include('Adminpanel.HRMS.Attendance._partials.summary-cards', ['summary' => $summary ?? []])
        @include('Adminpanel.HRMS.Attendance._partials.filters', [
            'filters' => $filters ?? [],
            'perPage' => $perPage ?? 10,
        ])

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Attendance List</div>
            </div>
            <div class="card-body">
                @include('Adminpanel.HRMS.Attendance._partials.attendance-table')
            </div>
        </div>
    </div>
</div>

@include('Adminpanel.HRMS.Attendance._partials.attendance-modal')
@endsection
