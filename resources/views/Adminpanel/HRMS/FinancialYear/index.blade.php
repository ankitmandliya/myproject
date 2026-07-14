@extends('Adminpanel.layout.mainlayout')

@section('content')
@php($cards = [
    ['label' => 'Current Financial Year', 'value' => $financial_year],
    ['label' => 'Status', 'value' => $status],
    ['label' => 'Total Employees', 'value' => $total_employees],
    ['label' => 'Processed', 'value' => $processed],
    ['label' => 'Pending', 'value' => $pending],
    ['label' => 'Carry Forward Employees', 'value' => $carry_forward_employees],
    ['label' => 'Last Closed Date', 'value' => $last_closed_date],
])
<div class="container"><div class="page-inner">
    <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-3">Financial Year Closing</h3>
            @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [['label' => 'Dashboard', 'url' => route('hrms.dashboard')], ['label' => 'HRMS'], ['label' => 'Financial Year']]])
        </div>
        <div class="ms-md-auto d-flex flex-wrap gap-2 d-print-none">
            <button class="btn btn-label-info" disabled>Export Excel</button><button class="btn btn-label-danger" disabled>Export PDF</button><button class="btn btn-light" onclick="window.print()">Print</button>
        </div>
    </div>
    @include('Adminpanel.HRMS.FinancialYear._partials.summary-cards')
    <div class="card card-round mb-3 d-print-none"><div class="card-body">
        <form method="GET" action="{{ route('hrms.financial-year.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Financial Year</label><input name="financial_year" value="{{ $financial_year }}" class="form-control"></div>
            <div class="col-md-6 d-flex gap-2"><button class="btn btn-primary">Search</button><a class="btn btn-info" href="{{ route('hrms.financial-year.preview', ['financial_year' => $financial_year]) }}">Preview Closing</a><a class="btn btn-light" href="{{ route('hrms.financial-year.history') }}">History</a></div>
        </form>
    </div></div>
    <div class="card card-round"><div class="card-header"><div class="card-title">Dashboard Widget</div></div><div class="card-body">
        <div class="row">@foreach($widget as $label => $value)<div class="col-md-4 mb-2"><div class="text-muted small">{{ $label }}</div><div class="fw-bold">{{ $value }}</div></div>@endforeach</div>
    </div></div>
</div></div>
@endsection
