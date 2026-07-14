<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate leave approval requests.
 */
class ApproveLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for leave approval.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approved_by' => ['nullable', 'integer', 'exists:users,id'],
            'approved_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
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
            'approved_at.date' => 'Approval date must be a valid date.',
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
            'approved_by' => 'Approved By',
            'approved_at' => 'Approved At',
            'remarks' => 'Remarks',
        ];
    }
}

