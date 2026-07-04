# HRMS Dashboard Business Logic Task (Laravel 10)

## Objective

Implement the complete **Dashboard Business Logic** for the HRMS system.

The Dashboard is responsible for aggregating data from existing HRMS services and providing a single source of truth for dashboard statistics, summaries, widgets, charts, and recent activities.

This phase must **NOT introduce any new business rules**. It should only consume existing business logic implemented in other services.

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
- ✅ Salary Business Logic

Implement dashboard logic only inside

```
app/Services/DashboardService.php
```

Create the corresponding interface if it does not already exist.

```
app/Contracts/DashboardServiceInterface.php
```

---

# SCOPE

This phase is ONLY responsible for

- Dashboard statistics
- Dashboard widgets
- Dashboard summary
- Monthly summaries
- Recent activities
- Charts data preparation

This phase MUST NOT implement

- Attendance calculations
- Leave calculations
- Salary calculations
- Role validations
- Notifications
- Authentication

Those already belong to their respective services.

---

# DEPENDENCIES

DashboardService may consume

- UserServiceInterface
- AttendanceServiceInterface
- LeaveServiceInterface
- SalaryServiceInterface
- CompanySettingServiceInterface

Use constructor dependency injection only.

Never instantiate services manually.

Example

```php
public function __construct(
    protected UserServiceInterface $userService,
    protected AttendanceServiceInterface $attendanceService,
    protected LeaveServiceInterface $leaveService,
    protected SalaryServiceInterface $salaryService,
    protected CompanySettingServiceInterface $companySettingService
) {}
```

---

# BUSINESS REQUIREMENTS

Implement the following methods.

---

## 1. getDashboardSummary()

Purpose

Return complete dashboard summary.

Return

```php
[
    'employees' => [],
    'attendance' => [],
    'leave' => [],
    'salary' => [],
    'company' => []
]
```

---

## 2. getEmployeeStatistics()

Return

```php
[
    'total_employees',
    'active_employees',
    'inactive_employees'
]
```

Use existing UserService.

---

## 3. getAttendanceStatistics()

Return

```php
[
    'present',
    'absent',
    'late',
    'half_day'
]
```

Use AttendanceService.

---

## 4. getLeaveStatistics()

Return

```php
[
    'pending',
    'approved',
    'rejected',
    'total'
]
```

Use LeaveService.

---

## 5. getSalaryStatistics()

Return

```php
[
    'generated',
    'pending'
]
```

Use SalaryService.

---

## 6. getCompanyStatistics()

Return

```php
[
    'office_start_time',
    'office_end_time',
    'weekly_off',
    'salary_date'
]
```

Use CompanySettingService.

---

## 7. getRecentLeaves(int $limit = 10)

Return

```
Collection
```

Include

- Employee
- Leave Type
- Status
- From Date
- To Date

---

## 8. getRecentAttendance(int $limit = 10)

Return

```
Collection
```

Include

- Employee
- Check In
- Check Out
- Status

---

## 9. getRecentSalarySlips(int $limit = 10)

Return

```
Collection
```

---

## 10. getUpcomingHolidays(int $limit = 10)

Purpose

Fetch upcoming holidays.

Use Holiday model.

Return

```
Collection
```

---

## 11. getMonthlyAttendanceChart(int $month, int $year)

Purpose

Prepare attendance chart data.

Return

```php
[
    'labels' => [],
    'values' => []
]
```

Do NOT generate charts.

Only prepare chart data.

---

## 12. getMonthlyLeaveChart(int $month, int $year)

Return

```php
[
    'labels' => [],
    'values' => []
]
```

---

## 13. getMonthlySalaryChart(int $month, int $year)

Return

```php
[
    'labels' => [],
    'values' => []
]
```

---

## 14. getSystemOverview()

Return

```php
[
    'employees',
    'attendance',
    'leave',
    'salary',
    'company'
]
```

---

## 15. getDashboardWidgets()

Purpose

Return all dashboard widget data.

Return

```php
array
```

---

# QUERY RULES

Use

- Existing services
- Eloquent
- Relationships
- Eager Loading

Avoid

- Raw SQL
- Duplicate queries
- Business logic duplication

---

# PERFORMANCE RULES

- Never duplicate calculations already implemented.
- Consume existing service methods.
- Prevent N+1 queries.
- Use eager loading.
- Limit recent records.
- Optimize dashboard loading.

---

# BUSINESS RULES

DashboardService MUST act only as an aggregator.

It MUST NOT

- calculate salary
- calculate attendance
- approve leave
- reject leave
- validate permissions

Instead, call existing service methods.

---

# ERROR HANDLING

Throw meaningful exceptions only when required.

Do NOT silently fail.

Return empty collections where appropriate.

---

# FUTURE ENHANCEMENTS

Do NOT implement

- Dashboard caching
- Real-time dashboard
- Live refresh
- WebSockets
- Graph rendering
- Analytics
- Export reports
- Notifications

These will be implemented in future phases.

---

# OUT OF SCOPE

Do NOT modify

- Controllers
- Routes
- Blade
- APIs
- Models
- Migrations
- Seeders
- Existing Services
- Business Rules

---

# CODE QUALITY RULES

Follow

- PSR-12
- SOLID
- DRY
- Constructor Dependency Injection
- Strict Return Types
- Type Hinting

DashboardService must remain lightweight and reusable.

---

# VERIFICATION

Implementation must pass

```bash
docker compose exec app php -l app/Services/DashboardService.php
```

```bash
docker compose exec app php -l app/Contracts/DashboardServiceInterface.php
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

- DashboardService is created.
- DashboardServiceInterface is created.
- Dashboard statistics are implemented.
- Dashboard widgets are implemented.
- Chart data preparation methods are implemented.
- Dashboard only aggregates existing services.
- No business logic is duplicated.
- Existing functionality remains unaffected.
- No controllers, routes, migrations, models, seeders, or Blade files are modified.
- Code follows Laravel 10 best practices and is production-ready.