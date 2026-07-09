<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate attendance update requests.
 */
class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for attendance updates.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'attendance_date' => ['sometimes', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'check_in' => ['nullable', 'date_format:H:i:s'],
            'check_out' => ['nullable', 'date_format:H:i:s', 'after:check_in'],
            'working_hours' => ['nullable', 'numeric', 'min:0'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'half_day' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'required', Rule::in(['Present', 'Absent', 'Leave', 'Holiday'])],
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
            'user_id.exists' => 'Selected employee does not exist.',
            'attendance_date.required' => 'Attendance date is required.',
            'attendance_date.date_format' => 'Attendance date format is invalid.',
            'attendance_date.before_or_equal' => 'Attendance date cannot be in the future.',
            'check_out.after' => 'Check-out time must be after check-in time.',
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

