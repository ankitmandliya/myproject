@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-3">Leave Reports Dashboard</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave Reports'],
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
                <a href="{{ $url }}" class="btn btn-sm {{ $label === 'Dashboard' ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
            @endforeach
        </div>

        @include('Adminpanel.HRMS.LeaveReports._partials.filters', ['action' => route('hrms.leave-reports.index')])
        @include('Adminpanel.HRMS.LeaveReports._partials.charts')

        <div class="row">
            @foreach($report['widgets'] ?? [] as $title => $items)
                <div class="col-lg-6 col-xl-3 mb-3">
                    <div class="card card-round h-100">
                        <div class="card-header"><div class="card-title">{{ \Illuminate\Support\Str::headline($title) }}</div></div>
                        <div class="card-body">
                            @forelse($items as $item)
                                <div class="d-flex justify-content-between border-bottom py-2 gap-2">
                                    <span>{{ $item['employee'] ?? $item['leave_type'] ?? '-' }}</span>
                                    <strong>{{ $item['days'] ?? $item['balance'] ?? $item['status'] ?? '-' }}</strong>
                                </div>
                            @empty
                                <div class="text-muted">No Report Data Found</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
