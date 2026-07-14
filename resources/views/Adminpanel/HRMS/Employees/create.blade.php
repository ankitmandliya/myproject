@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Create Employee</h3>
            @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                ['label' => 'HRMS'],
                ['label' => 'Employees', 'url' => route('hrms.users.index')],
                ['label' => 'Create'],
            ]])
        </div>

        <form action="{{ route('hrms.users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('Adminpanel.HRMS.Employees._partials.form', ['roles' => $roles, 'reportingManagers' => $reportingManagers, 'isEdit' => false])
        </form>
    </div>
</div>
@endsection