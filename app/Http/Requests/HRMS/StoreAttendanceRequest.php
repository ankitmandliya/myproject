<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate attendance creation requests.
 */
class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for attendance creation.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'attendance_date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i:s'],
            'check_out' => ['nullable', 'date_format:H:i:s'],
            'working_hours' => ['nullable', 'numeric', 'min:0'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'half_day' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['Present', 'Absent', 'Leave', 'Holiday'])],
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
            'user_id.required' => 'Employee is required for attendance.',
            'user_id.exists' => 'Selected employee does not exist.',
            'attendance_date.required' => 'Attendance date is required.',
            'status.required' => 'Attendance status is required.',
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
            'attendance_date' => 'Attendance Date',
            'check_in' => 'Check In',
            'check_out' => 'Check Out',
            'working_hours' => 'Working Hours',
            'late_minutes' => 'Late Minutes',
            'half_day' => 'Half Day',
        ];
    }
}
