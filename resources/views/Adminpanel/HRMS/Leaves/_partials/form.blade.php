@php
    $leave = $leave ?? null;
    $leaveTypes = $leaveTypes ?? collect();
    $currentUser = $employee ?? auth()->user();
    $currentDetail = $currentUser?->userDetail;
    $selectedUserId = old('user_id', $leave?->user_id ?? $currentUser?->id);
    $selectedLeaveTypeId = old('leave_type_id', $leave?->leave_type_id);
    $selectedLeaveTypeName = optional($leaveTypes->firstWhere('id', (int) $selectedLeaveTypeId))->leave_name ?? '-';
    $leaveBalances = collect($leaveBalances ?? ($summary['balances'] ?? []));
    $totalDays = old('total_days', $leave?->payable_leave_days ?? $leave?->total_days ?? 1);
    $requestedDays = old('requested_days', $leave?->requested_days ?? $totalDays);
    $holidayDays = old('holiday_days', $leave?->holiday_days ?? 0);
    $weeklyOffDays = old('weekly_off_days', $leave?->weekly_off_days ?? 0);
    $sandwichDays = old('sandwich_days', $leave?->sandwich_days ?? 0);
    $payableLeaveDays = old('payable_leave_days', $leave?->payable_leave_days ?? $totalDays);
    $hasServerCalculation = $leave && $leave->payable_leave_days !== null;
    $attachmentName = old('attachment_name', 'No attachment selected');
    $initialStep = (int) old('current_step', 1);
    if ($errors->has('leave_type_id') || $errors->has('from_date') || $errors->has('to_date') || $errors->has('total_days')) {
        $initialStep = 2;
    } elseif ($errors->has('reason')) {
        $initialStep = 3;
    }
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="leaveWizardForm" data-initial-step="{{ $initialStep }}" data-calculate-url="{{ route('hrms.leave.calculate') }}" novalidate>
    @csrf
    @isset($method)
        @method($method)
    @endisset
    <input type="hidden" name="current_step" id="current_step" value="{{ $initialStep }}">
    <input type="hidden" name="attachment_name" id="attachment_name" value="{{ $attachmentName }}">

    <div class="card card-round">
        <div class="card-body pb-2">
            <div class="row text-center leave-wizard-steps" role="tablist" aria-label="Leave application steps">
                @foreach ([1 => 'Employee', 2 => 'Leave Details', 3 => 'Reason', 4 => 'Review'] as $stepNumber => $stepLabel)
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="btn btn-light btn-round w-100 leave-step-indicator" data-step-target="{{ $stepNumber }}" title="{{ $stepLabel }}" aria-label="Step {{ $stepNumber }} {{ $stepLabel }}">
                            <span class="badge badge-secondary me-1">{{ $stepNumber }}</span>{{ $stepLabel }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card card-round leave-step" data-step="1">
        <div class="card-header"><div class="card-title">Step 1: Employee Information</div></div>
        <div class="card-body">
            <input type="hidden" name="user_id" value="{{ $selectedUserId }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="employee_display">Employee <span class="text-danger">*</span></label>
                        <input type="text" id="employee_display" class="form-control" value="{{ $currentUser?->name ?? 'Current Employee' }}" readonly data-review="Employee">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="employee_code_display">Employee Code</label>
                        <input type="text" id="employee_code_display" class="form-control" value="{{ $currentDetail?->emp_code ?? '-' }}" readonly data-review="Employee Code">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="department_display">Department</label>
                        <input type="text" id="department_display" class="form-control" value="{{ $currentDetail?->department ?? '-' }}" readonly data-review="Department">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="designation_display">Designation</label>
                        <input type="text" id="designation_display" class="form-control" value="{{ $currentDetail?->designation ?? '-' }}" readonly data-review="Designation">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-round leave-step" data-step="2">
        <div class="card-header"><div class="card-title">Step 2: Leave Details</div></div>
        <div class="card-body">
                        @if ($leaveBalances->isNotEmpty())
                <div class="row mb-3" id="leaveBalanceCards">
                    @foreach ($leaveBalances as $balance)
                        @php
                            $balanceLeaveTypeId = (string) ($balance['leave_type_id'] ?? '');
                            $isSelectedBalance = $balanceLeaveTypeId !== '' && (string) $selectedLeaveTypeId === $balanceLeaveTypeId;
                        @endphp
                        <div class="col-sm-6 col-lg-4 mb-3">
                            <div class="border rounded p-3 h-100 leave-balance-card {{ $isSelectedBalance ? 'active border-primary' : '' }}" data-balance-card data-leave-type-id="{{ $balanceLeaveTypeId }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="fw-bold text-break">{{ $balance['leave_type'] ?? '-' }}</div>
                                    <span class="badge badge-light">FY {{ $balance['financial_year'] ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1"><span>Allocated</span><strong>{{ number_format((float) ($balance['allocated'] ?? 0), 2) }}</strong></div>
                                <div class="d-flex justify-content-between mb-1"><span>Used</span><strong>{{ number_format((float) ($balance['used'] ?? 0), 2) }}</strong></div>
                                <div class="d-flex justify-content-between mb-1"><span>Carry Forward</span><strong>{{ number_format((float) ($balance['carry_forward'] ?? 0), 2) }}</strong></div>
                                <div class="d-flex justify-content-between"><span>Remaining</span><strong>{{ number_format((float) ($balance['remaining'] ?? 0), 2) }}</strong></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id" id="leave_type_id" class="form-control @error('leave_type_id') is-invalid @enderror" required data-review="Leave Type">
                            <option value="">Select Leave Type</option>
                            @foreach ($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}" @selected((string) $selectedLeaveTypeId === (string) $leaveType->id)>
                                    {{ $leaveType->leave_name ?? $leaveType->name ?? 'Leave Type' }}
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="from_date">From Date <span class="text-danger">*</span></label>
                        <input type="date" name="from_date" id="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ old('from_date', optional($leave?->from_date)->format('Y-m-d')) }}" required data-review="From Date">
                        @error('from_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="to_date">To Date <span class="text-danger">*</span></label>
                        <input type="date" name="to_date" id="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ old('to_date', optional($leave?->to_date)->format('Y-m-d')) }}" required data-review="To Date">
                        @error('to_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="total_days">Total Days <span class="text-danger">*</span></label>
                        <input type="number" name="total_days" id="total_days" class="form-control @error('total_days') is-invalid @enderror" value="{{ $totalDays }}" min="0.5" step="0.5" readonly required data-review="Total Days" data-live-field="payable_leave_days">
                        @error('total_days')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="is_half_day">Half Day</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="is_half_day" id="is_half_day" class="form-check-input" value="1" @checked(old('is_half_day', $leave?->leave_calculation_json['half_day'] ?? false)) data-review="Half Day">
                            <label class="form-check-label" for="is_half_day">Yes</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="half_day_type">Half Day Session</label>
                        <select name="half_day_type" id="half_day_type" class="form-control" data-review="Half Day Session">
                            <option value="first_half" @selected(old('half_day_type', $leave?->leave_calculation_json['half_day_type'] ?? 'first_half') === 'first_half')>First Half</option>
                            <option value="second_half" @selected(old('half_day_type', $leave?->leave_calculation_json['half_day_type'] ?? 'first_half') === 'second_half')>Second Half</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="emergency_leave">Emergency Leave</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="emergency_leave" id="emergency_leave" class="form-check-input" value="1" @checked(old('emergency_leave', false)) data-review="Emergency Leave">
                            <label class="form-check-label" for="emergency_leave">Yes</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="calc_requested_days">Requested Days</label>
                        <input type="number" id="calc_requested_days" class="form-control" value="{{ $requestedDays }}" step="0.5" readonly data-review="Requested Days" data-live-field="requested_days">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="calc_holiday_days">Holiday Days</label>
                        <input type="number" id="calc_holiday_days" class="form-control" value="{{ $holidayDays }}" step="0.5" readonly data-review="Holiday Days" data-live-field="holiday_days">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="calc_weekly_off_days">Weekly Off Days</label>
                        <input type="number" id="calc_weekly_off_days" class="form-control" value="{{ $weeklyOffDays }}" step="0.5" readonly data-review="Weekly Off Days" data-live-field="weekly_off_days">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="calc_sandwich_days">Sandwich Days</label>
                        <input type="number" id="calc_sandwich_days" class="form-control" value="{{ $sandwichDays }}" step="0.5" readonly data-review="Sandwich Days" data-live-field="sandwich_days">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="calc_payable_leave_days">Final Payable Days</label>
                        <input type="number" id="calc_payable_leave_days" class="form-control" value="{{ $payableLeaveDays }}" step="0.5" readonly data-review="Final Payable Days" data-live-field="payable_leave_days">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="calc_remaining_balance">Remaining Balance</label>
                        <input type="text" id="calc_remaining_balance" class="form-control" value="-" readonly data-review="Remaining Balance" data-live-field="remaining_balance">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="calc_balance_after_approval">Balance After Approval</label>
                        <input type="text" id="calc_balance_after_approval" class="form-control" value="-" readonly data-review="Balance After Approval" data-live-field="balance_after_approval">
                    </div>
                </div>
                <div class="col-12">
                    <div class="alert alert-warning d-none" id="leaveCalculationWarning"></div>
                    <div class="border rounded p-3 d-none" id="leaveCalendarPreview">
                        <div class="fw-bold mb-2">Calendar Preview</div>
                        <div class="d-flex flex-wrap gap-2" id="leaveCalendarPreviewDates"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-round leave-step" data-step="3">
        <div class="card-header"><div class="card-title">Step 3: Reason</div></div>
        <div class="card-body">
            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="5" data-review="Reason">{{ old('reason', $leave?->reason) }}</textarea>
                @error('reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="attachment_placeholder">Attachment</label>
                <input type="file" id="attachment_placeholder" class="form-control" aria-describedby="attachmentHelp">
                <small id="attachmentHelp" class="form-text text-muted">Attachment upload is a UI placeholder; selected filename is shown for review.</small>
                <div class="mt-2 text-muted" id="attachment_filename_display">{{ $attachmentName }}</div>
            </div>
        </div>
    </div>

    <div class="card card-round leave-step" data-step="4">
        <div class="card-header"><div class="card-title">Step 4: Review</div></div>
        <div class="card-body">
            <div class="row" id="leaveReviewList">
                @foreach (['Employee', 'Employee Code', 'Department', 'Designation', 'Leave Type', 'From Date', 'To Date', 'Total Days', 'Half Day', 'Half Day Session', 'Emergency Leave', 'Requested Days', 'Holiday Days', 'Weekly Off Days', 'Sandwich Days', 'Final Payable Days', 'Remaining Balance', 'Balance After Approval', 'Reason', 'Attachment', 'Status'] as $label)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fw-bold text-break" data-review-output="{{ $label }}">{{ $label === 'Attachment' ? $attachmentName : ($label === 'Status' ? ($leave?->status ?? 'Pending') : '-') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card card-round">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-2">
            <button type="button" class="btn btn-light" id="leavePrevBtn" title="Previous step">
                <i class="fas fa-arrow-left me-1"></i> Previous
            </button>
            <div class="text-md-end">
                <a href="{{ route('hrms.leave-apply.index') }}" class="btn btn-light me-2">Cancel</a>
                <button type="button" class="btn btn-primary" id="leaveNextBtn" title="Next step">
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="submit" class="btn btn-primary d-none" id="leaveSubmitBtn" data-loading-text="Saving...">
                    <span class="spinner-border spinner-border-sm me-1 d-none" aria-hidden="true"></span>
                    <i class="fas fa-save me-1"></i> {{ $buttonText ?? 'Submit Leave Request' }}
                </button>
            </div>
        </div>
    </div>
</form>

<style>
    .leave-step-indicator.active { color: #fff; background-color: #1572e8; border-color: #1572e8; }
    .leave-step-indicator.completed { color: #1572e8; background-color: #eef5ff; border-color: #b9d7ff; }
    .leave-step.d-none { display: none !important; }
    .leave-balance-card.active { background-color: #f4f9ff; box-shadow: 0 0 0 1px rgba(21, 114, 232, .15); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('leaveWizardForm');
    if (!form) {
        return;
    }

    var currentStepInput = document.getElementById('current_step');
    var currentStep = parseInt(form.dataset.initialStep || currentStepInput.value || '1', 10);
    var steps = Array.prototype.slice.call(form.querySelectorAll('.leave-step'));
    var indicators = Array.prototype.slice.call(form.querySelectorAll('.leave-step-indicator'));
    var prevBtn = document.getElementById('leavePrevBtn');
    var nextBtn = document.getElementById('leaveNextBtn');
    var submitBtn = document.getElementById('leaveSubmitBtn');
    var attachmentInput = document.getElementById('attachment_placeholder');
    var attachmentHidden = document.getElementById('attachment_name');
    var attachmentDisplay = document.getElementById('attachment_filename_display');
    var leaveTypeSelect = document.getElementById('leave_type_id');
    var balanceCards = Array.prototype.slice.call(form.querySelectorAll('[data-balance-card]'));
    var liveFields = Array.prototype.slice.call(form.querySelectorAll('[data-live-field]'));
    var warningBox = document.getElementById('leaveCalculationWarning');
    var preview = document.getElementById('leaveCalendarPreview');
    var previewDates = document.getElementById('leaveCalendarPreviewDates');
    var calculationTimer = null;
    var calculationController = null;
    var latestCalculation = null;

    function fieldValue(id) {
        var field = document.getElementById(id);
        if (!field) {
            return '';
        }
        return field.type === 'checkbox' ? (field.checked ? '1' : '0') : field.value;
    }

    function reviewValue(element) {
        if (element.type === 'checkbox') {
            return element.checked ? 'YES' : 'NO';
        }
        if (element.tagName === 'SELECT') {
            return element.options[element.selectedIndex] ? element.options[element.selectedIndex].text.trim() : '-';
        }

        return (element.value || '').trim() || '-';
    }

    function formatValue(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }
        return String(value);
    }

    function syncBalanceCards() {
        var selectedLeaveType = leaveTypeSelect ? leaveTypeSelect.value : '';
        balanceCards.forEach(function (card) {
            var isActive = selectedLeaveType !== '' && card.dataset.leaveTypeId === selectedLeaveType;
            card.classList.toggle('active', isActive);
            card.classList.toggle('border-primary', isActive);
        });
    }

    function setWarning(message) {
        if (!warningBox) {
            return;
        }
        warningBox.textContent = message || '';
        warningBox.classList.toggle('d-none', !message);
    }

    function syncReview() {
        syncBalanceCards();
        form.querySelectorAll('[data-review]').forEach(function (element) {
            var output = form.querySelector('[data-review-output="' + element.dataset.review + '"]');
            if (output) {
                output.textContent = reviewValue(element);
            }
        });

        var attachmentOutput = form.querySelector('[data-review-output="Attachment"]');
        if (attachmentOutput) {
            attachmentOutput.textContent = attachmentHidden.value || 'No attachment selected';
        }
    }

    function setReviewValue(label, value) {
        var output = form.querySelector('[data-review-output="' + label + '"]');
        if (output) {
            output.textContent = formatValue(value);
        }
    }

    function renderPreview(data) {
        if (!preview || !previewDates) {
            return;
        }
        previewDates.innerHTML = '';
        var groups = [
            ['Requested', data.requested_dates || []],
            ['Holiday', data.holiday_dates || []],
            ['Weekly Off', data.weekly_off_dates || []],
            ['Sandwich', data.sandwich_dates || []]
        ];
        if (data.half_day && data.requested_dates && data.requested_dates.length) {
            groups.push(['Half Day', data.requested_dates]);
        }
        groups.forEach(function (group) {
            group[1].forEach(function (date) {
                var badge = document.createElement('span');
                badge.className = 'badge badge-light border';
                badge.textContent = group[0] + ': ' + date;
                previewDates.appendChild(badge);
            });
        });
        preview.classList.toggle('d-none', previewDates.children.length === 0);
    }

    function renderCalculation(data) {
        latestCalculation = data;
        liveFields.forEach(function (field) {
            var key = field.dataset.liveField;
            if (Object.prototype.hasOwnProperty.call(data, key)) {
                field.value = formatValue(data[key]);
            }
        });
        setReviewValue('Total Days', data.payable_leave_days);
        setReviewValue('Requested Days', data.requested_days);
        setReviewValue('Holiday Days', data.holiday_days);
        setReviewValue('Weekly Off Days', data.weekly_off_days);
        setReviewValue('Sandwich Days', data.sandwich_days);
        setReviewValue('Final Payable Days', data.payable_leave_days);
        setReviewValue('Remaining Balance', data.remaining_balance);
        setReviewValue('Balance After Approval', data.balance_after_approval);
        setReviewValue('Emergency Leave', data.emergency_leave_label);
        setReviewValue('Half Day', data.half_day ? 'YES' : 'NO');
        setReviewValue('Half Day Session', data.half_day_session || '-');
        setWarning(data.warning || null);
        submitBtn.disabled = data.can_submit === false;
        renderPreview(data);
        syncReview();
    }

    function requestCalculation() {
        var leaveType = fieldValue('leave_type_id');
        var fromDate = fieldValue('from_date');
        var toDate = fieldValue('to_date');
        if (!leaveType || !fromDate || !toDate || !form.dataset.calculateUrl) {
            return;
        }
        if (calculationController) {
            calculationController.abort();
        }
        calculationController = new AbortController();
        var payload = new FormData();
        payload.append('_token', form.querySelector('input[name="_token"]').value);
        payload.append('employee_id', form.querySelector('input[name="user_id"]').value);
        payload.append('leave_type_id', leaveType);
        payload.append('from_date', fromDate);
        payload.append('to_date', toDate);
        payload.append('is_half_day', fieldValue('is_half_day'));
        payload.append('half_day_type', fieldValue('half_day_type'));
        payload.append('emergency_leave', fieldValue('emergency_leave'));

        fetch(form.dataset.calculateUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: payload,
            signal: calculationController.signal
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw data;
                    }
                    return data;
                });
            })
            .then(renderCalculation)
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                latestCalculation = null;
                submitBtn.disabled = true;
                setWarning((error && error.warning) || 'Unable to calculate leave.');
            });
    }

    function scheduleCalculation() {
        window.clearTimeout(calculationTimer);
        calculationTimer = window.setTimeout(requestCalculation, 300);
    }

    function setStep(step) {
        currentStep = Math.max(1, Math.min(4, step));
        currentStepInput.value = currentStep;
        steps.forEach(function (stepEl) {
            stepEl.classList.toggle('d-none', parseInt(stepEl.dataset.step, 10) !== currentStep);
        });
        indicators.forEach(function (indicator) {
            var target = parseInt(indicator.dataset.stepTarget, 10);
            indicator.classList.toggle('active', target === currentStep);
            indicator.classList.toggle('completed', target < currentStep);
            indicator.querySelector('.badge').className = 'badge me-1 ' + (target <= currentStep ? 'badge-light' : 'badge-secondary');
        });
        prevBtn.disabled = currentStep === 1;
        nextBtn.classList.toggle('d-none', currentStep === 4);
        submitBtn.classList.toggle('d-none', currentStep !== 4);
        if (currentStep === 4) {
            requestCalculation();
        }
        syncReview();
    }

    function validateStep(step) {
        var valid = true;
        var stepEl = form.querySelector('.leave-step[data-step="' + step + '"]');
        if (!stepEl) {
            return true;
        }

        stepEl.querySelectorAll('input, select, textarea').forEach(function (field) {
            if (field.disabled || field.readOnly && !field.required) {
                return;
            }

            var feedback = field.parentElement.querySelector('.client-invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
            field.classList.remove('is-invalid');

            if (!field.checkValidity()) {
                valid = false;
                field.classList.add('is-invalid');
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block client-invalid-feedback';
                feedback.textContent = field.validationMessage || 'This field is required.';
                field.parentElement.appendChild(feedback);
            }
        });

        if (!valid) {
            var firstInvalid = stepEl.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus({ preventScroll: true });
            }
        }

        return valid;
    }

    indicators.forEach(function (indicator) {
        indicator.addEventListener('click', function () {
            var target = parseInt(indicator.dataset.stepTarget, 10);
            if (target <= currentStep || validateStep(currentStep)) {
                setStep(target);
            }
        });
    });

    nextBtn.addEventListener('click', function () {
        if (validateStep(currentStep)) {
            setStep(currentStep + 1);
        }
    });

    prevBtn.addEventListener('click', function () {
        setStep(currentStep - 1);
    });

    form.querySelectorAll('[data-review]').forEach(function (element) {
        element.addEventListener('input', syncReview);
        element.addEventListener('change', syncReview);
    });

    ['leave_type_id', 'from_date', 'to_date', 'is_half_day', 'half_day_type', 'emergency_leave'].forEach(function (id) {
        var field = document.getElementById(id);
        if (field) {
            field.addEventListener('change', scheduleCalculation);
            field.addEventListener('input', scheduleCalculation);
        }
    });

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function () {
            var name = attachmentInput.files && attachmentInput.files.length ? attachmentInput.files[0].name : 'No attachment selected';
            attachmentHidden.value = name;
            attachmentDisplay.textContent = name;
            syncReview();
        });
    }

    form.addEventListener('submit', function (event) {
        if (!validateStep(currentStep) || !latestCalculation || latestCalculation.can_submit === false) {
            event.preventDefault();
            requestCalculation();
            return;
        }
        if (form.dataset.submitted === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitted = 'true';
        submitBtn.disabled = true;
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
        submitBtn.childNodes[submitBtn.childNodes.length - 1].textContent = ' ' + (submitBtn.dataset.loadingText || 'Saving...');
    });

    setStep(currentStep);
    scheduleCalculation();
});
</script>



