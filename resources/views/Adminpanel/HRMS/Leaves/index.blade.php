@extends('Adminpanel.layout.mainlayout')

@section('content')
@php
    $canManageLeave = $canManageLeave ?? false;
    $canApproveLeave = $canApproveLeave ?? $canManageLeave;
@endphp

<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Leave Management</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave Management'],
                ]])
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <button type="button" class="btn btn-label-info btn-round me-2" disabled>
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-light btn-round me-2" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <a href="{{ route('hrms.leave-apply.calendar') }}" class="btn btn-light btn-round me-2">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar
                </a>
                @if ($canApproveLeave)
                    <a href="{{ route('hrms.leave-apply.approvals') }}" class="btn btn-light btn-round me-2">
                        <i class="fas fa-user-check me-1"></i> Approvals
                    </a>
                @endif
                <a href="{{ route('hrms.leave-apply.create') }}" class="btn btn-primary btn-round">
                    <i class="fas fa-plus me-1"></i> Apply Leave
                </a>
            </div>
        </div>

        @include('Adminpanel.HRMS.Leaves._partials.summary-cards')
        @include('Adminpanel.HRMS.Leaves._partials.filters')

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Leave Requests</div>
            </div>
            <div class="card-body">
                @include('Adminpanel.HRMS.Leaves._partials.leave-table')

                @if (method_exists($leaves, 'links') && $leaves->count() > 0)
                    <div class="mt-3">
                        {{ $leaves->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
