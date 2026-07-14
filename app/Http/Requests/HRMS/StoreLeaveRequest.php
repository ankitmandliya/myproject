<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate leave application creation requests.
 */
class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'total_days' => ['required', 'numeric', 'min:0.5'],
            'is_half_day' => ['nullable', 'boolean'],
            'half_day' => ['nullable', 'boolean'],
            'half_day_type' => ['nullable', 'string', 'in:first_half,second_half'],
            'half_day_session' => ['nullable', 'string', 'in:first_half,second_half'],
            'emergency_leave' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'leave_type_id.required' => 'Leave type is required.',
            'leave_type_id.exists' => 'Selected leave type does not exist.',
            'from_date.required' => 'Leave start date is required.',
            'to_date.required' => 'Leave end date is required.',
            'to_date.after_or_equal' => 'Leave end date must be after or equal to the start date.',
            'total_days.required' => 'Total leave days are required.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'Employee',
            'leave_type_id' => 'Leave Type',
            'from_date' => 'From Date',
            'to_date' => 'To Date',
            'total_days' => 'Total Days',
            'is_half_day' => 'Half Day',
            'half_day_type' => 'Half Day Session',
            'half_day_session' => 'Half Day Session',
            'emergency_leave' => 'Emergency Leave',
        ];
    }
}
