@extends('Adminpanel.layout.mainlayout')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Employee</h3>
            @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                ['label' => 'HRMS'],
                ['label' => 'Employees', 'url' => route('hrms.users.index')],
                ['label' => 'Edit'],
            ]])
        </div>

        <form action="{{ route('hrms.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('Adminpanel.HRMS.Employees._partials.form', ['user' => $user, 'roles' => $roles, 'isEdit' => true])
        </form>
    </div>
</div>
@endsection