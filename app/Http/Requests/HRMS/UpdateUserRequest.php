<?php

declare(strict_types=1);

namespace App\Http\Requests\HRMS;

use App\Models\UserDetail;
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
        $userId = (int) $this->route('user');
        $userDetailId = $this->route('user_detail') ?? $this->route('userDetail');

        if ($userDetailId === null && $userId > 0) {
            $userDetailId = UserDetail::where('user_id', $userId)->value('id');
        }

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
            'joining_date' => ['sometimes', 'required', 'date'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'reporting_manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'aadhaar' => ['nullable', 'string', 'max:20', Rule::unique('user_details', 'aadhaar')->ignore($userDetailId)],
            'pan' => ['nullable', 'string', 'max:20', Rule::unique('user_details', 'pan')->ignore($userDetailId)],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['nullable', 'boolean'],
            'role_id' => ['sometimes', 'required', 'integer', 'exists:role_master,id'],
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
            'joining_date.required' => 'Employee joining date is required.',
            'role_id.required' => 'Employee role is required.',
            'reporting_manager_id.exists' => 'Selected reporting manager is not available.',
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
            'reporting_manager_id' => 'Reporting Manager',
            'basic_salary' => 'Basic Salary',
            'profile_photo' => 'Profile Photo',
            'aadhaar' => 'Aadhaar',
            'pan' => 'PAN',
        ];
    }
}
