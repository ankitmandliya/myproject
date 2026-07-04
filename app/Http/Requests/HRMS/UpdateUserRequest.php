<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate employee update requests.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules for employee updates.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user');
        $userDetailId = $this->route('user_detail') ?? $this->route('userDetail');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'emp_code' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('user_details', 'emp_code')->ignore($userDetailId)],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'dob' => ['nullable', 'date'],
            'joining_date' => ['nullable', 'date'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'aadhaar' => ['nullable', 'string', 'max:20', Rule::unique('user_details', 'aadhaar')->ignore($userDetailId)],
            'pan' => ['nullable', 'string', 'max:20', Rule::unique('user_details', 'pan')->ignore($userDetailId)],
            'profile_photo' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
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
            'email.unique' => 'This employee email is already registered.',
            'emp_code.unique' => 'This employee code is already in use.',
            'first_name.required' => 'Employee first name is required.',
            'aadhaar.unique' => 'This Aadhaar number is already in use.',
            'pan.unique' => 'This PAN number is already in use.',
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
            'emp_code' => 'Employee Code',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'dob' => 'Date of Birth',
            'joining_date' => 'Joining Date',
            'basic_salary' => 'Basic Salary',
            'profile_photo' => 'Profile Photo',
            'aadhaar' => 'Aadhaar',
            'pan' => 'PAN',
        ];
    }
}
