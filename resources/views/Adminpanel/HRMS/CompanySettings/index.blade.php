@extends('Adminpanel.layout.mainlayout')

@section('title', 'Company Settings')

@section('content')
@php
    $levels = $settings->leave_approval_levels ?? ['manager', 'hr', 'admin'];
@endphp
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Company Settings</h3>
                @include('Adminpanel.layout.breadcrumb', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('hrms.dashboard')],
                    ['label' => 'HRMS'],
                    ['label' => 'Company Settings'],
                ]])
            </div>
        </div>

        <form action="{{ route('hrms.company-setting.update') }}" method="POST" class="js-loading-form">
            @csrf
            @method('PUT')
            <div class="card card-round">
                <div class="card-header"><div class="card-title">Attendance & Payroll</div></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Office Start Time</label><input type="time" step="1" name="office_start_time" value="{{ old('office_start_time', $settings->office_start_time) }}" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Office End Time</label><input type="time" step="1" name="office_end_time" value="{{ old('office_end_time', $settings->office_end_time) }}" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Weekly Off</label><input type="text" name="weekly_off" value="{{ old('weekly_off', $settings->weekly_off) }}" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Late After Minutes</label><input type="number" min="0" name="late_after_minutes" value="{{ old('late_after_minutes', $settings->late_after_minutes) }}" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Half Day After Minutes</label><input type="number" min="0" name="half_day_after_minutes" value="{{ old('half_day_after_minutes', $settings->half_day_after_minutes) }}" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Salary Date</label><input type="number" min="1" max="31" name="salary_date" value="{{ old('salary_date', $settings->salary_date) }}" class="form-control" required></div></div>
                    </div>
                </div>
            </div>

            <div class="card card-round">
                <div class="card-header"><div class="card-title">Leave Approval Workflow</div></div>
                <div class="card-body">
                    <input type="hidden" name="leave_auto_approval" value="0">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="leave_auto_approval" name="leave_auto_approval" value="1" @checked(old('leave_auto_approval', $settings->leave_auto_approval))>
                        <label class="form-check-label" for="leave_auto_approval">Enable Leave Auto Approval</label>
                    </div>
                    <label class="form-label fw-semibold">Approval Levels</label>
                    <div class="row">
                        @foreach(['manager' => 'Reporting Manager', 'hr' => 'HR', 'admin' => 'Admin'] as $value => $label)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="level_{{ $value }}" name="leave_approval_levels[]" value="{{ $value }}" @checked(in_array($value, old('leave_approval_levels', $levels), true))>
                                    <label class="form-check-label" for="level_{{ $value }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card card-round">
                <div class="card-header"><div class="card-title">Notification Preferences</div></div>
                <div class="card-body">
                    <div class="row">
                        @foreach([
                            'enable_notifications' => 'Enable Notifications',
                            'enable_leave_reminders' => 'Enable Leave Reminders',
                            'enable_approval_reminders' => 'Enable Approval Reminders',
                            'enable_low_balance_alerts' => 'Enable Low Balance Alerts',
                        ] as $field => $label)
                            <div class="col-md-6 mb-2">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="{{ $field }}" name="{{ $field }}" value="1" @checked(old($field, $settings->{$field} ?? true))>
                                    <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary" data-loading-text="Saving..."><span class="spinner-border spinner-border-sm me-1 d-none"></span> Save Settings</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"]');
            if (!button) return;
            button.disabled = true;
            var spinner = button.querySelector('.spinner-border');
            if (spinner) spinner.classList.remove('d-none');
        });
    });
});
</script>
@endsection

