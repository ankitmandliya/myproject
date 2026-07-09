@php
    $isEdit = $isEdit ?? false;
    $user = $user ?? null;
    $detail = $user?->userDetail;
    $selectedRoleId = old('role_id', $user?->roles?->first()?->id);
    $selectedStatus = old('status', $detail?->status ?? 1);
    $dobValue = old('dob', $detail?->dob ? \Illuminate\Support\Carbon::parse($detail->dob)->format('Y-m-d') : '');
    $joiningDateValue = old('joining_date', $detail?->joining_date ? \Illuminate\Support\Carbon::parse($detail->joining_date)->format('Y-m-d') : '');
    $defaultPhoto = 'assets/img/profile.jpg';
    $photoPath = trim((string) ($detail?->profile_photo ?? ''));
    $photoUrl = $photoPath !== '' && file_exists(public_path($photoPath))
        ? asset($photoPath)
        : asset($defaultPhoto);
    $errorStepMap = [
        1 => ['emp_code', 'name', 'first_name', 'last_name', 'email', 'password', 'password_confirmation'],
        2 => ['gender', 'dob', 'phone', 'address', 'profile_photo'],
        3 => ['joining_date', 'department', 'designation', 'basic_salary', 'role_id', 'status'],
        4 => ['aadhaar', 'pan'],
    ];
    $activeStep = 1;

    foreach ($errorStepMap as $stepNumber => $fields) {
        foreach ($fields as $field) {
            if ($errors->has($field)) {
                $activeStep = $stepNumber;
                break 2;
            }
        }
    }
@endphp

<style>
    .employee-wizard .nav-item { min-width: 0; }
    .employee-wizard .nav-link { border: 1px solid #e5e7eb; white-space: nowrap; }
    .employee-wizard .nav-link.completed { background-color: #e8f5e9; border-color: #31ce36; color: #1b7f20; }
    .employee-wizard .nav-link.active { font-weight: 600; }
    .employee-step-error { display: none; }
</style>

<div class="card card-round mb-2">
    <div class="card-body py-2">
        <ul class="nav nav-pills nav-secondary employee-wizard flex-column flex-md-row gap-2" id="employeeWizardTabs" role="tablist">
            @foreach([
                1 => 'Step 1 Account',
                2 => 'Step 2 Personal',
                3 => 'Step 3 Employment',
                4 => 'Step 4 Government',
                5 => 'Step 5 Review',
            ] as $step => $label)
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100 text-center {{ $activeStep === $step ? 'active' : '' }}" id="employee-step-{{ $step }}-tab" data-bs-toggle="pill" data-bs-target="#employee-step-{{ $step }}" type="button" role="tab" aria-controls="employee-step-{{ $step }}" aria-selected="{{ $activeStep === $step ? 'true' : 'false' }}" data-step-index="{{ $step - 1 }}">
                        {{ $label }}
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="alert alert-danger employee-step-error mt-2 mb-0 py-2" id="employeeWizardStepError" role="alert"></div>
    </div>
</div>

<div class="tab-content" id="employeeWizardContent" data-active-step="{{ $activeStep }}">
    <div class="tab-pane fade {{ $activeStep === 1 ? 'show active' : '' }}" id="employee-step-1" role="tabpanel" aria-labelledby="employee-step-1-tab" tabindex="0">
        <div class="card card-round mb-2">
            <div class="card-header py-2">
                <div class="card-title">Account Information</div>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="emp_code">Employee Code <span class="text-danger">*</span></label>
                            <input type="text" name="emp_code" id="emp_code" class="form-control @error('emp_code') is-invalid @enderror" value="{{ old('emp_code', $detail?->emp_code) }}" required data-review-label="Employee Code">
                            @error('emp_code')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $detail?->first_name) }}" required data-review-label="First Name">
                            @error('first_name')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $detail?->last_name) }}" data-review-label="Last Name">
                            @error('last_name')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group py-1">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required data-review-label="Name">
                            @error('name')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group py-1">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required data-review-label="Email">
                            @error('email')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    @unless($isEdit)
                        <div class="col-md-6">
                            <div class="form-group py-1">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                                @error('password')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group py-1">
                                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required minlength="8">
                                @error('password_confirmation')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeStep === 2 ? 'show active' : '' }}" id="employee-step-2" role="tabpanel" aria-labelledby="employee-step-2-tab" tabindex="0">
        <div class="card card-round mb-2">
            <div class="card-header py-2">
                <div class="card-title">Personal Information</div>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror" data-review-label="Gender">
                                <option value="">Select Gender</option>
                                @foreach(['Male', 'Female', 'Other'] as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender', $detail?->gender) === $gender)>{{ $gender }}</option>
                                @endforeach
                            </select>
                            @error('gender')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ $dobValue }}" data-review-label="DOB">
                            @error('dob')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="phone">Mobile</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}">
                            @error('phone')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group py-1">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $detail?->address) }}</textarea>
                            @error('address')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="profile_photo">Profile Photo</label>
                            <div class="mb-2">
                                <img src="{{ $photoUrl }}" alt="Employee profile photo" id="profilePhotoPreview" class="avatar-img rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                            </div>
                            <input type="file" name="profile_photo" id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            @error('profile_photo')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeStep === 3 ? 'show active' : '' }}" id="employee-step-3" role="tabpanel" aria-labelledby="employee-step-3-tab" tabindex="0">
        <div class="card card-round mb-2">
            <div class="card-header py-2">
                <div class="card-title">Employment Information</div>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="joining_date">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" id="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ $joiningDateValue }}" required data-review-label="Joining Date">
                            @error('joining_date')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="department">Department</label>
                            <input type="text" name="department" id="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department', $detail?->department) }}" data-review-label="Department">
                            @error('department')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="designation">Designation</label>
                            <input type="text" name="designation" id="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $detail?->designation) }}" data-review-label="Designation">
                            @error('designation')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="basic_salary">Basic Salary</label>
                            <input type="number" step="0.01" min="0" name="basic_salary" id="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', $detail?->basic_salary) }}" data-review-label="Salary">
                            @error('basic_salary')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="role_id">Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required data-review-label="Role">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string) $selectedRoleId === (string) $role->id)>{{ $role->role_name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group py-1">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required data-review-label="Status">
                                <option value="1" @selected((string) $selectedStatus === '1')>Active</option>
                                <option value="0" @selected((string) $selectedStatus === '0')>Inactive</option>
                            </select>
                            @error('status')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeStep === 4 ? 'show active' : '' }}" id="employee-step-4" role="tabpanel" aria-labelledby="employee-step-4-tab" tabindex="0">
        <div class="card card-round mb-2">
            <div class="card-header py-2">
                <div class="card-title">Government Details</div>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group py-1">
                            <label for="aadhaar">Aadhaar</label>
                            <input type="text" name="aadhaar" id="aadhaar" class="form-control @error('aadhaar') is-invalid @enderror" value="{{ old('aadhaar', $detail?->aadhaar) }}" data-review-label="Aadhaar">
                            @error('aadhaar')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group py-1">
                            <label for="pan">PAN</label>
                            <input type="text" name="pan" id="pan" class="form-control @error('pan') is-invalid @enderror" value="{{ old('pan', $detail?->pan) }}" data-review-label="PAN">
                            @error('pan')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeStep === 5 ? 'show active' : '' }}" id="employee-step-5" role="tabpanel" aria-labelledby="employee-step-5-tab" tabindex="0">
        <div class="card card-round mb-2">
            <div class="card-header py-2">
                <div class="card-title">Review</div>
            </div>
            <div class="card-body py-2">
                <div class="row" id="employeeReview">
                    @foreach(['Employee Code', 'Name', 'Email', 'Gender', 'DOB', 'Department', 'Designation', 'Joining Date', 'Role', 'Status', 'Salary', 'Aadhaar', 'PAN'] as $label)
                        <div class="col-md-4 mb-2">
                            <strong>{{ $label }}</strong><br>
                            <span data-review-output="{{ $label }}">-</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-0">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between gap-2 flex-wrap">
            <button type="button" class="btn btn-light" id="employeeWizardPrevious" aria-label="Previous step">Previous</button>
            <div class="d-flex gap-2">
                <a href="{{ route('hrms.users.index') }}" class="btn btn-light">Cancel</a>
                <button type="button" class="btn btn-primary" id="employeeWizardNext" aria-label="Next step">Next</button>
                <button type="submit" class="btn btn-primary d-none" id="employeeWizardSubmit">{{ $isEdit ? 'Update' : 'Save' }}</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('#employeeWizardTabs button[data-bs-toggle="pill"]'));
    var panes = Array.prototype.slice.call(document.querySelectorAll('#employeeWizardContent .tab-pane'));
    var previousButton = document.getElementById('employeeWizardPrevious');
    var nextButton = document.getElementById('employeeWizardNext');
    var submitButton = document.getElementById('employeeWizardSubmit');
    var stepError = document.getElementById('employeeWizardStepError');
    var profileInput = document.getElementById('profile_photo');
    var profilePreview = document.getElementById('profilePhotoPreview');
    var activeIndex = Math.max(0, tabs.findIndex(function (tab) { return tab.classList.contains('active'); }));
    var maxVisitedIndex = activeIndex;

    function fieldLabel(field) {
        var label = document.querySelector('label[for="' + field.id + '"]');
        return label ? label.textContent.replace('*', '').trim() : 'This field';
    }

    function setFieldError(field, message) {
        field.classList.add('is-invalid');
        var group = field.closest('.form-group') || field.parentElement;
        var existing = group.querySelector('.client-validation-message');
        if (!existing) {
            existing = document.createElement('small');
            existing.className = 'text-danger d-block mt-1 client-validation-message';
            group.appendChild(existing);
        }
        existing.textContent = message;
    }

    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        var group = field.closest('.form-group') || field.parentElement;
        var existing = group.querySelector('.client-validation-message');
        if (existing) {
            existing.remove();
        }
    }

    function validateStep(index) {
        var fields = Array.prototype.slice.call(panes[index].querySelectorAll('input, select, textarea'));
        var valid = true;
        var firstInvalid = null;

        fields.forEach(function (field) {
            clearFieldError(field);

            if (field.type === 'hidden' || field.disabled) {
                return;
            }

            if (!field.checkValidity()) {
                valid = false;
                firstInvalid = firstInvalid || field;
                setFieldError(field, field.validity.valueMissing ? fieldLabel(field) + ' is required.' : field.validationMessage);
            }
        });

        if (!valid) {
            stepError.textContent = 'Please complete the required fields before continuing.';
            stepError.style.display = 'block';
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            stepError.textContent = '';
            stepError.style.display = 'none';
        }

        return valid;
    }

    function valueFor(selector) {
        var field = document.querySelector(selector);
        if (!field) {
            return '-';
        }
        if (field.tagName === 'SELECT') {
            return field.options[field.selectedIndex] ? field.options[field.selectedIndex].text : '-';
        }
        return field.value || '-';
    }

    function updateReview() {
        var firstName = valueFor('#first_name');
        var lastName = valueFor('#last_name');
        var displayName = valueFor('#name');
        var fullName = displayName !== '-' ? displayName : [firstName, lastName].filter(function (value) { return value !== '-'; }).join(' ');
        var values = {
            'Employee Code': valueFor('#emp_code'),
            'Name': fullName || '-',
            'Email': valueFor('#email'),
            'Gender': valueFor('#gender'),
            'DOB': valueFor('#dob'),
            'Department': valueFor('#department'),
            'Designation': valueFor('#designation'),
            'Joining Date': valueFor('#joining_date'),
            'Role': valueFor('#role_id'),
            'Status': valueFor('#status'),
            'Salary': valueFor('#basic_salary'),
            'Aadhaar': valueFor('#aadhaar'),
            'PAN': valueFor('#pan')
        };

        Object.keys(values).forEach(function (label) {
            var output = document.querySelector('[data-review-output="' + label + '"]');
            if (output) {
                output.textContent = values[label] || '-';
            }
        });
    }

    function showStep(index) {
        if (index < 0 || index >= tabs.length) {
            return;
        }

        if (window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(tabs[index]).show();
        } else {
            tabs[index].click();
        }

        activeIndex = index;
        maxVisitedIndex = Math.max(maxVisitedIndex, index);
        previousButton.disabled = activeIndex === 0;
        nextButton.classList.toggle('d-none', activeIndex === tabs.length - 1);
        submitButton.classList.toggle('d-none', activeIndex !== tabs.length - 1);
        tabs.forEach(function (tab, tabIndex) {
            tab.classList.toggle('completed', tabIndex < activeIndex || tabIndex < maxVisitedIndex && tabIndex < tabs.length - 1);
        });
        updateReview();
    }

    tabs.forEach(function (tab, index) {
        tab.addEventListener('show.bs.tab', function (event) {
            if (index > activeIndex && !validateStep(activeIndex)) {
                event.preventDefault();
            }
        });
        tab.addEventListener('shown.bs.tab', function () {
            activeIndex = index;
            showStep(activeIndex);
        });
    });

    previousButton.addEventListener('click', function () {
        showStep(activeIndex - 1);
    });

    nextButton.addEventListener('click', function () {
        if (validateStep(activeIndex)) {
            showStep(activeIndex + 1);
        }
    });

    document.querySelectorAll('#employeeWizardContent input, #employeeWizardContent select, #employeeWizardContent textarea').forEach(function (field) {
        field.addEventListener('input', function () {
            clearFieldError(field);
            updateReview();
        });
        field.addEventListener('change', function () {
            clearFieldError(field);
            updateReview();
        });
    });

    if (profileInput && profilePreview) {
        profileInput.addEventListener('change', function () {
            var file = profileInput.files && profileInput.files[0];
            if (file) {
                profilePreview.src = URL.createObjectURL(file);
            }
        });
    }

    updateReview();
    showStep(activeIndex);
});
</script>