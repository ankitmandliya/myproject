# HRMS Service Architecture Task (Laravel 10)

## Objective

This document defines the **Service Layer architecture** for the HRMS system.

The purpose of this phase is to create a clean, scalable, and production-ready Service Layer structure using Laravel 10 best practices.

This phase is responsible for creating only the **service architecture (skeleton)**, including interfaces, dependency injection, service bindings, and method definitions.

**No business logic should be implemented in this phase.**

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version Constraint

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.
- Follow Laravel 10 conventions only.

---

## 2. Scope Restriction (CRITICAL)

This phase ONLY includes:

- Service class creation
- Service interface creation
- Method signatures
- Constructor dependency injection
- Service container bindings
- Folder structure

Do NOT implement:

- Business logic
- Database queries
- Calculations
- Validation
- Workflows
- Events
- Queues
- Jobs
- Notifications
- Caching
- Transactions

---

## 3. Architecture Principles

The implementation MUST follow:

- SOLID Principles
- DRY Principle
- Separation of Concerns
- Dependency Injection
- Interface Segregation Principle
- Single Responsibility Principle

Controllers should depend on interfaces instead of concrete classes.

---

## 4. Service Layer Rule

Services must act only as architectural placeholders.

Services MUST NOT contain:

- Business rules
- Eloquent queries
- Query Builder logic
- Raw SQL
- Validation logic
- Authorization logic

Only create method definitions with TODO placeholders.

Example:

```php
public function markCheckIn(int $userId, array $data): void
{
    // TODO: Implement business logic.
}
```

---

# DIRECTORY STRUCTURE

Create the following structure:

```text
app/
│
├── Contracts/
│     ├── AttendanceServiceInterface.php
│     ├── LeaveServiceInterface.php
│     ├── SalaryServiceInterface.php
│     ├── RolePermissionServiceInterface.php
│     └── CompanySettingServiceInterface.php
│
├── Services/
│     ├── AttendanceService.php
│     ├── LeaveService.php
│     ├── SalaryService.php
│     ├── RolePermissionService.php
│     └── CompanySettingService.php
│
└── Providers/
      └── AppServiceProvider.php
```

---

# REQUIRED SERVICE CLASSES

Create the following services.

- AttendanceService
- LeaveService
- SalaryService
- RolePermissionService
- CompanySettingService

---

# REQUIRED INTERFACES

Create the following interfaces.

- AttendanceServiceInterface
- LeaveServiceInterface
- SalaryServiceInterface
- RolePermissionServiceInterface
- CompanySettingServiceInterface

Every service MUST implement its corresponding interface.

Example:

```php
class AttendanceService implements AttendanceServiceInterface
{
}
```

---

# CONSTRUCTOR DEPENDENCY INJECTION

Use Constructor Property Promotion.

Example:

```php
public function __construct(
    protected Attendance $attendance,
    protected CompanySetting $companySetting
) {
}
```

Do NOT instantiate models manually.

Incorrect:

```php
new Attendance();
```

Correct:

Use dependency injection only.

---

# METHOD DESIGN RULES

Every method should contain:

- Method signature
- Return type
- Empty body
- TODO comment

Example:

```php
public function getSalaryDate(): int
{
    // TODO: Implement.
}
```

---

# ATTENDANCE SERVICE

Methods

```php
public function markCheckIn(int $userId, array $data): void;

public function markCheckOut(int $userId): void;

public function calculateWorkingHours(int $attendanceId): float;

public function getMonthlyAttendance(
    int $userId,
    string $month
): \Illuminate\Database\Eloquent\Collection;
```

---

# LEAVE SERVICE

Methods

```php
public function applyLeave(
    int $userId,
    array $data
): void;

public function approveLeave(
    int $leaveId
): void;

public function rejectLeave(
    int $leaveId
): void;

public function validateLeave(
    int $userId,
    array $data
): bool;
```

---

# SALARY SERVICE

Methods

```php
public function generateMonthlySalary(
    int $userId,
    string $month,
    string $year
): void;

public function calculateNetSalary(
    int $userId
): float;

public function getSalarySlip(
    int $userId,
    string $month
): ?SalarySlip;
```

---

# ROLE PERMISSION SERVICE

Methods

```php
public function hasPermission(
    int $userId,
    string $permission
): bool;

public function hasRole(
    int $userId,
    string $role
): bool;

public function getUserPermissions(
    int $userId
): array;
```

---

# COMPANY SETTING SERVICE

Methods

```php
public function getOfficeStartTime(): string;

public function getOfficeEndTime(): string;

public function getLateThreshold(): int;

public function getSalaryDate(): int;
```

---

# RETURN TYPE RULES

Every public method MUST declare return types.

Allowed return types:

- void
- bool
- int
- float
- string
- array
- Collection
- Model
- Nullable Model

Do NOT return:

- View
- RedirectResponse
- JsonResponse
- Response

---

# PHPDOC REQUIREMENT

Every class MUST include PHPDoc.

Every public method MUST include PHPDoc.

Example:

```php
/**
 * Mark employee check-in.
 *
 * @param int $userId
 * @param array $data
 *
 * @return void
 */
```

---

# STRICT TYPING

Every PHP file MUST begin with:

```php
declare(strict_types=1);
```

---

# CODING STANDARD

Follow:

- PSR-12 Coding Standard
- Laravel 10 Coding Standards
- Constructor Property Promotion
- Typed Properties
- Return Type Declarations

---

# DATABASE RULE

This phase MUST NOT execute:

- Model::create()
- Model::update()
- Model::delete()
- Model::where()
- DB::
- Query Builder
- Raw SQL

Only dependency injection is allowed.

---

# VALIDATION RULE

Validation MUST NOT be implemented.

Validation will be handled later using:

- Form Requests
- Service Layer

This phase only prepares the architecture.

---

# EVENTS

Do NOT create:

- Events
- Listeners
- Notifications
- Jobs
- Queues

The architecture should only be future-ready.

---

# TRANSACTIONS

Do NOT implement:

```php
DB::transaction()
```

This will be implemented in the business logic phase.

---

# CACHE

Do NOT implement:

- Cache
- Redis
- Memcached

---

# ERROR HANDLING

Do NOT implement:

- try/catch
- Custom Exceptions
- Business Exceptions

Future phases will handle exceptions.

---

# SERVICE CONTAINER BINDING

Register every interface inside:

```
App\Providers\AppServiceProvider
```

Example:

```php
$this->app->bind(
    AttendanceServiceInterface::class,
    AttendanceService::class
);
```

Bind all five services.

---

# CONTROLLER RULE

Controllers must eventually depend on interfaces.

Example:

```php
public function __construct(
    protected AttendanceServiceInterface $attendanceService
) {
}
```

Controllers must never instantiate services manually.

---

# OUT OF SCOPE

Do NOT generate:

- Controllers
- Routes
- Blade Views
- APIs
- Migrations
- Seeders
- Models
- Business Logic
- Validation
- Authentication
- Authorization
- Middleware
- Events
- Listeners
- Jobs
- Queues
- Tests

---

# SUCCESS CRITERIA

This phase is complete when:

- All service interfaces are created.
- All service classes are created.
- Every service implements its interface.
- Constructor dependency injection is used.
- Return types are declared.
- PHPDoc is present.
- Strict typing is enabled.
- PSR-12 standards are followed.
- Service container bindings are added.
- No business logic exists.
- No database queries exist.
- No validation exists.
- No events are implemented.
- Code is Laravel 10 compatible.
- Architecture is production-ready and ready for the next implementation phase.