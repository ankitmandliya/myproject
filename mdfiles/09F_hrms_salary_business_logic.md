# HRMS Salary Business Logic Task (Laravel 10)

## Objective

Implement the complete **Salary Management Business Logic** for the HRMS system.

This phase is responsible for salary generation, salary calculation, salary history, salary reports, deductions, payroll validation, and salary slip retrieval.

All business logic must be implemented **only inside the existing SalaryService**.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

The following modules are already completed.

- ✅ Rule Book
- ✅ Migrations
- ✅ Models
- ✅ Seeders
- ✅ Service Architecture
- ✅ Form Requests
- ✅ Service Skeleton
- ✅ Controllers
- ✅ Routes
- ✅ Company Setting Business Logic
- ✅ Role Permission Business Logic
- ✅ User Business Logic
- ✅ Attendance Business Logic
- ✅ Leave Apply Business Logic

Implement business logic only inside

```
app/Services/SalaryService.php
```

Do NOT modify

- Controllers
- Models
- Routes
- Views
- Migrations
- Seeders

---

# RESPONSIBILITIES

SalaryService is responsible ONLY for

- Salary Generation
- Salary Calculation
- Salary Slip Retrieval
- Payroll Validation
- Monthly Payroll
- Salary Reports

It MUST NOT contain

- Attendance business logic
- Leave business logic
- Notification logic
- Dashboard logic

---

# DEPENDENCIES

SalaryService may consume

- UserServiceInterface
- AttendanceServiceInterface
- LeaveServiceInterface
- CompanySettingServiceInterface

Use constructor dependency injection only.

---

# BUSINESS REQUIREMENTS

Implement the following methods.

---

## 1. generateMonthlySalary(int $userId, int $month, int $year)

Purpose

Generate salary for a specific employee.

Rules

- User must exist.
- User must be active.
- Salary must not already exist for the same month/year.
- Fetch employee basic salary.
- Calculate net salary.
- Store salary record.

Return

```
SalarySlip
```

---

## 2. generatePayroll(int $month, int $year)

Purpose

Generate salary for all active employees.

Rules

- Process only active employees.
- Skip employees whose salary already exists.
- Generate salary one by one.

Return

```
Collection
```

---

## 3. calculateNetSalary(int $userId, int $month, int $year)

Purpose

Calculate employee salary.

Formula

```
Net Salary

=

Basic Salary

+ Allowance

+ Overtime

-

Deduction

-

Leave Deduction
```

Return

```
array
```

Example

```php
[
    'basic_salary' => 30000,
    'allowance' => 5000,
    'deduction' => 1000,
    'overtime' => 500,
    'leave_deduction' => 0,
    'net_salary' => 34500
]
```

---

## 4. calculateAllowance(int $userId)

Purpose

Calculate monthly allowance.

Current Rule

Return stored allowance.

Future enhancement may calculate dynamically.

Return

```
float
```

---

## 5. calculateDeduction(int $userId)

Purpose

Calculate manual deductions.

Return

```
float
```

---

## 6. calculateLeaveDeduction(int $userId, int $month, int $year)

Purpose

Calculate salary deduction due to leave.

Current Rule

Use approved leave records only.

Future enhancement can introduce leave balance logic.

Return

```
float
```

---

## 7. calculateOvertime(int $userId, int $month, int $year)

Purpose

Calculate overtime amount.

Current Rule

Return stored overtime amount.

Future enhancement may calculate using attendance.

Return

```
float
```

---

## 8. getSalarySlip(int $userId, int $month, int $year)

Return

```
SalarySlip|null
```

---

## 9. getSalaryHistory(int $userId)

Return

```
Collection
```

---

## 10. getMonthlyPayroll(int $month, int $year)

Return

```
Collection
```

Use eager loading.

---

## 11. getSalaryReport(int $month, int $year)

Return

```
Collection
```

Include

- Employee
- Basic Salary
- Net Salary

---

## 12. salaryExists(int $userId, int $month, int $year)

Return

```
bool
```

---

## 13. deleteSalarySlip(int $salaryId)

Purpose

Delete salary record.

Rules

Delete only if business rules allow.

Return

```
bool
```

---

## 14. getEmployeeSalarySummary(int $userId)

Return

```
array
```

Include

- Total Salary Records
- Latest Salary
- Average Salary
- Highest Salary
- Lowest Salary

---

## 15. getLatestSalary(int $userId)

Return

```
SalarySlip|null
```

---

# DATABASE TRANSACTION RULES

Use database transactions for

- generateMonthlySalary()
- generatePayroll()
- deleteSalarySlip()

Rollback automatically on failure.

---

# QUERY RULES

Use

- Eloquent
- Relationships
- Eager Loading

Avoid

- Raw SQL
- Manual joins

---

# PERFORMANCE RULES

Prevent

- N+1 queries
- Duplicate queries

Use eager loading wherever possible.

Use pagination for reports if necessary.

---

# VALIDATION RULES

Validate

- User exists
- User active
- Salary record uniqueness
- Valid month
- Valid year

Throw meaningful exceptions.

---

# BUSINESS RULES

Salary generation should

- Prevent duplicate salary slips.
- Always calculate latest values.
- Generate only one salary slip per employee per month.

---

# CURRENT CALCULATION RULES

For this phase use only

```
Net Salary

=

Basic Salary

+ Allowance

+ Overtime

-

Deduction

-

Leave Deduction
```

Do NOT implement

- Tax calculation
- PF
- ESI
- Professional Tax
- Bonus
- Incentives
- Gratuity
- TDS
- Loan deduction

These will be implemented in future phases.

---

# FUTURE ENHANCEMENT

Future versions may include

- Income Tax
- PF
- ESI
- Bonus
- Loan Recovery
- Incentive
- Reimbursement
- Payroll Lock
- Payslip PDF
- Payroll Approval

Do NOT implement them now.

---

# ERROR HANDLING

Throw exceptions for

- User not found
- Inactive user
- Salary already generated
- Invalid month
- Invalid year
- Salary record not found

Never silently fail.

---

# OUT OF SCOPE

Do NOT implement

- Controllers
- Routes
- Blade
- APIs
- PDF generation
- Email
- Notification
- Events
- Queues

---

# CODE QUALITY RULES

Follow

- PSR-12
- SOLID
- DRY
- Constructor Dependency Injection
- Strict Return Types
- Type Hinting

---

# VERIFICATION

Implementation must pass

```bash
docker compose exec app php -l app/Services/SalaryService.php
```

```bash
docker compose exec app php artisan optimize:clear
```

```bash
docker compose exec app php artisan optimize
```

```bash
docker compose exec app php artisan route:list
```

```bash
docker compose exec app php artisan about
```

---

# SUCCESS CRITERIA

Task is complete when

- SalaryService contains all salary business logic.
- Monthly salary generation is implemented.
- Duplicate salary generation is prevented.
- Net salary calculation is implemented.
- Salary history is available.
- Salary reports are available.
- Payroll generation is implemented.
- Database transactions are used where required.
- Controllers remain thin.
- Existing functionality remains unaffected.
- No controllers, routes, migrations, models, or views are modified.
- Code follows Laravel 10 best practices and is production-ready.