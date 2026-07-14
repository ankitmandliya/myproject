@extends('Adminpanel.layout.mainlayout')

@section('content')
@php($cards = [
    ['label' => 'Employees', 'value' => $summary['employees']], ['label' => 'Processed', 'value' => $summary['processed']], ['label' => 'Skipped', 'value' => $summary['skipped']], ['label' => 'Inactive', 'value' => $summary['inactive']], ['label' => 'Carry Forward', 'value' => $summary['carry_forward']], ['label' => 'Reset', 'value' => $summary['reset']], ['label' => 'Errors', 'value' => $summary['errors']],
])
<div class="container"><div class="page-inner">
    <div class="d-flex flex-column flex-md-row align-items-md-center pt-2 pb-4 gap-3"><div><h3 class="fw-bold mb-3">Preview Closing {{ $financial_year }}</h3>@include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [['label' => 'Dashboard', 'url' => route('hrms.dashboard')], ['label' => 'HRMS'], ['label' => 'Financial Year', 'url' => route('hrms.financial-year.index')], ['label' => 'Preview']]])</div><div class="ms-md-auto d-flex gap-2 d-print-none"><button class="btn btn-label-info" disabled>Export Excel</button><button class="btn btn-label-danger" disabled>Export PDF</button><button class="btn btn-light" onclick="window.print()">Print</button></div></div>
    @include('Adminpanel.HRMS.FinancialYear._partials.summary-cards')
    <div class="card card-round"><div class="card-header d-flex align-items-center"><div class="card-title">Preview</div><div class="ms-auto d-print-none"><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeFyModal" @disabled($closed)>Close Financial Year</button></div></div><div class="card-body">@include('Adminpanel.HRMS.FinancialYear._partials.preview-table', ['rows' => $rows])</div></div>
    @component('Adminpanel.HRMS.FinancialYear._partials.confirmation-modal', ['modalId' => 'closeFyModal', 'title' => 'Close Financial Year', 'message' => 'You are about to close Financial Year ' . $financial_year . '. This action cannot be automatically reversed. Continue?'])
        <form method="POST" action="{{ route('hrms.financial-year.close') }}">@csrf<input type="hidden" name="financial_year" value="{{ $financial_year }}"><button class="btn btn-danger">Close Financial Year</button></form>
    @endcomponent
</div></div>
@endsection
