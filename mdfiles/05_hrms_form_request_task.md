# HRMS Form Request Layer Task (Laravel 10)

## Objective

This document defines the Form Request layer for the HRMS module.

The purpose of this phase is to centralize request validation using Laravel Form Request classes, ensuring controllers receive only validated data.

No business logic should be implemented in this phase.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Scope Restriction

This phase ONLY includes:

- Form Request classes
- Validation rules
- Authorization methods
- Custom validation messages
- Attribute names

Do NOT implement:

- Controllers
- Services
- Database queries
- Business logic
- Events
- Jobs
- Notifications
- APIs
- Blade changes

---

## 3. Architecture Rules

Follow:

- SOLID
- DRY
- Single Responsibility Principle

Validation belongs only inside Form Requests.

Controllers must never call:

```php
$request->validate(...)
```

All validation must be handled through dedicated Form Request classes.

---

# DIRECTORY STRUCTURE

Create requests inside:

```
app/
└── Http/
    └── Requests/
        └── HRMS/
```

---

# REQUEST CLASSES TO CREATE

## User

- StoreUserRequest
- UpdateUserRequest

---

## Attendance

- StoreAttendanceRequest
- UpdateAttendanceRequest

---

## Leave

- StoreLeaveRequest
- UpdateLeaveRequest
- ApproveLeaveRequest
- RejectLeaveRequest

---

## Salary

- GenerateSalaryRequest

---

## Role

- StoreRoleRequest
- UpdateRoleRequest

---

## Permission

- StoreRolePermissionRequest
- UpdateRolePermissionRequest

---

## Company Setting

- UpdateCompanySettingRequest

---

# AUTHORIZATION

Every request MUST contain:

```php
public function authorize(): bool
{
    return true;
}
```

Authorization will be implemented in a later phase.

---

# VALIDATION RULES

Validation must only contain Laravel validation rules.

No database operations.

No calculations.

No service calls.

Example:

```php
'name' => [
    'required',
    'string',
    'max:255',
];
```

---

# CUSTOM MESSAGES

Every request should define:

```php
public function messages(): array
```

Provide meaningful validation messages.

Example:

```php
'name.required' => 'Employee name is required.',
```

---

# ATTRIBUTE NAMES

Every request should define:

```php
public function attributes(): array
```

Example:

```php
return [
    'emp_code' => 'Employee Code',
];
```

---

# COMMON VALIDATION GUIDELINES

Use appropriate Laravel rules such as:

- required
- nullable
- string
- integer
- numeric
- boolean
- email
- date
- max
- min
- exists
- unique
- in
- array

Use Rule::unique()->ignore() where appropriate for update requests.

---

# PHPDOC

Every class must include PHPDoc.

Every public method must include PHPDoc.

---

# STRICT TYPES

Every PHP file must begin with:

```php
declare(strict_types=1);
```

---

# CODING STANDARD

Follow:

- PSR-12
- Laravel 10 coding standards
- Typed return values

---

# CONTROLLER RULE

Controllers should receive Form Requests.

Example:

```php
public function store(StoreLeaveRequest $request)
{
    return $this->leaveService->applyLeave(
        auth()->id(),
        $request->validated()
    );
}
```

Controllers must never perform validation.

---

# OUT OF SCOPE

Do NOT generate:

- Controllers
- Services
- Models
- Migrations
- Seeders
- Business logic
- Events
- Notifications
- Jobs
- APIs
- Blade files

---

# SUCCESS CRITERIA

The task is complete when:

- All Form Request classes are created.
- Validation rules are defined.
- Authorization methods exist.
- Custom messages are added.
- Attribute names are defined.
- No controllers are modified.
- No business logic is added.
- Code follows Laravel 10 standards.
- Code is production-ready.