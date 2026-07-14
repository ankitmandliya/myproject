<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate leave rejection requests.
 */
class RejectLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for leave rejection.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approved_by' => ['nullable', 'integer', 'exists:users,id'],
            'approved_at' => ['nullable', 'date'],
            'remarks' => ['required', 'string', 'min:10', 'max:1000'],
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
            'approved_by.exists' => 'Selected approver does not exist.',
            'approved_at.date' => 'Rejection date must be a valid date.',
            'remarks.required' => 'Rejection remarks are required.',
            'remarks.min' => 'Rejection remarks must be at least 10 characters.',
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
            'approved_by' => 'Rejected By',
            'approved_at' => 'Rejected At',
            'remarks' => 'Remarks',
        ];
    }
}

