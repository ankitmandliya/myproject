<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate leave application update requests.
 */
class UpdateLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for leave application updates.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'leave_type_id' => ['sometimes', 'required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['sometimes', 'required', 'date'],
            'to_date' => ['sometimes', 'required', 'date', 'after_or_equal:from_date'],
            'total_days' => ['sometimes', 'required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::in(['Pending', 'Approved', 'Rejected'])],
            'approved_by' => ['nullable', 'integer', 'exists:users,id'],
            'approved_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'leave_type_id.exists' => 'Selected leave type does not exist.',
            'to_date.after_or_equal' => 'Leave end date must be after or equal to the start date.',
            'status.in' => 'Leave status must be Pending, Approved, or Rejected.',
            'approved_by.exists' => 'Selected approver does not exist.',
        ];
    }

    /**
     * Get custom attribute names.
     *
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
            'approved_by' => 'Approved By',
            'approved_at' => 'Approved At',
        ];
    }
}
