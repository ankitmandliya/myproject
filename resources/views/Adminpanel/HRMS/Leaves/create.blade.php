@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Apply Leave</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Leave Management', 'url' => route('hrms.leave-apply.index')],
                    ['label' => 'Apply Leave'],
                ]])
            </div>
        </div>

        @include('Adminpanel.HRMS.Leaves._partials.form', [
            'action' => route('hrms.leave-apply.store'),
            'buttonText' => 'Submit Leave Request',
        ])
    </div>
</div>
@endsection
