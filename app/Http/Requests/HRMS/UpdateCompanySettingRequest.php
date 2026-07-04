<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate company setting update requests.
 */
class UpdateCompanySettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for company settings.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'office_start_time' => ['required', 'date_format:H:i:s'],
            'office_end_time' => ['required', 'date_format:H:i:s'],
            'late_after_minutes' => ['required', 'integer', 'min:0'],
            'half_day_after_minutes' => ['required', 'integer', 'min:0'],
            'salary_date' => ['required', 'integer', 'min:1', 'max:31'],
            'weekly_off' => ['required', 'string', 'max:50'],
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
            'office_start_time.required' => 'Office start time is required.',
            'office_end_time.required' => 'Office end time is required.',
            'salary_date.max' => 'Salary date must be between 1 and 31.',
            'weekly_off.required' => 'Weekly off day is required.',
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
            'office_start_time' => 'Office Start Time',
            'office_end_time' => 'Office End Time',
            'late_after_minutes' => 'Late After Minutes',
            'half_day_after_minutes' => 'Half Day After Minutes',
            'salary_date' => 'Salary Date',
            'weekly_off' => 'Weekly Off',
        ];
    }
}
