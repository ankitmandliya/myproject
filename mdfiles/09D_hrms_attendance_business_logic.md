# HRMS Attendance Business Logic Task (Laravel 10)

## Objective

Implement the complete business logic for the **Attendance Management** module.

Attendance is the foundation of the HRMS system. It is responsible for employee check-in/check-out, working hour calculation, late arrival detection, half-day detection, attendance status management, and attendance reporting.

The implementation must be done **only inside the existing AttendanceService**.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

The following modules are already completed:

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

Implement business logic only inside:

```
app/Services/AttendanceService.php
```

Do NOT modify:

- Controllers
- Models
- Routes
- Views
- Migrations
- Seeders
- CompanySettingService
- UserService
- RolePermissionService

---

# 3. Responsibilities

AttendanceService is responsible ONLY for:

- Employee Check-In
- Employee Check-Out
- Working Hours Calculation
- Late Detection
- Half-Day Detection
- Daily Attendance
- Monthly Attendance
- Attendance Reports

It MUST NOT contain:

- Leave logic
- Salary logic
- Notification logic
- Dashboard logic

---

# DEPENDENCIES

AttendanceService may consume:

- UserServiceInterface
- CompanySettingServiceInterface

Use constructor dependency injection only.

---

# BUSINESS REQUIREMENTS

Implement the following methods.

---

## 1. markCheckIn(int $userId, array $data)

Purpose

Create today's attendance.

Rules

- Employee must exist.
- Employee must be active.
- Only one check-in per day.
- Check-in cannot be duplicated.
- Default status = Present.

Store

- attendance_date
- check_in

Return

```
Attendance
```

---

## 2. markCheckOut(int $userId)

Purpose

Mark employee checkout.

Rules

- Today's attendance must exist.
- Checkout cannot happen twice.
- Checkout time must be greater than check-in.
- Automatically calculate working hours.

Return

```
Attendance
```

---

## 3. calculateWorkingHours(int $attendanceId)

Purpose

Calculate total working hours.

Formula

```
Working Hours

=

CheckOut - CheckIn
```

Update

```
working_hours
```

Return

```
float
```

---

## 4. calculateLateMinutes(int $attendanceId)

Purpose

Calculate late arrival.

Rules

Use Company Setting

```
Office Start Time

Late Threshold
```

Example

```
Office Start

10:00

Late Threshold

15 minutes

Employee Check In

10:22

Late Minutes

7
```

Update

```
late_minutes
```

Return

```
int
```

---

## 5. detectHalfDay(int $attendanceId)

Purpose

Determine Half-Day.

Use Company Setting

```
half_day_after_minutes
```

Rules

If

```
Working Hours

<

Required Hours
```

Then

```
half_day = true
```

Return

```
bool
```

---

## 6. updateAttendanceStatus(int $attendanceId)

Purpose

Determine attendance status.

Possible Status

```
Present

Absent

Leave

Holiday
```

Rules

- Present after successful check-in.
- Leave and Holiday integration will be handled in later phases.
- Keep method reusable.

Return

```
Attendance
```

---

## 7. getTodayAttendance(int $userId)

Return

```
Attendance|null
```

---

## 8. getAttendanceByDate(int $userId, Carbon $date)

Return

```
Attendance|null
```

---

## 9. getMonthlyAttendance(int $userId, int $month, int $year)

Return

```
Collection
```

Requirements

- Ordered by attendance_date ascending.

---

## 10. getAttendanceBetweenDates(int $userId, Carbon $from, Carbon $to)

Return

```
Collection
```

---

## 11. getUserAttendanceSummary(int $userId, int $month, int $year)

Return

```
array
```

Include

```
Total Days

Present

Absent

Leave

Holiday

Late Days

Half Days
```

---

## 12. getAttendanceReport(int $month, int $year)

Purpose

Generate attendance report for all employees.

Return

```
Collection
```

Use eager loading.

---

## 13. hasCheckedInToday(int $userId)

Return

```
bool
```

---

## 14. hasCheckedOutToday(int $userId)

Return

```
bool
```

---

## 15. isLate(int $attendanceId)

Return

```
bool
```

---

## 16. isHalfDay(int $attendanceId)

Return

```
bool
```

---

## 17. deleteAttendance(int $attendanceId)

Purpose

Delete attendance record.

Return

```
bool
```

---

# DATABASE TRANSACTION RULES

Use transactions for

- markCheckIn()
- markCheckOut()
- deleteAttendance()

Rollback automatically on failure.

---

# COMPANY SETTINGS INTEGRATION

AttendanceService MUST obtain these values from CompanySettingService.

Never hardcode.

Use

```
Office Start Time

Office End Time

Late Threshold

Half Day Threshold

Weekly Off
```

---

# DATE & TIME RULES

Use

```
Carbon
```

only.

Do NOT use

```
date()

strtotime()

time()
```

---

# QUERY RULES

Use Eloquent only.

Use relationships wherever possible.

Avoid

- Raw SQL
- Manual joins

---

# PERFORMANCE RULES

Use eager loading.

Prevent

- N+1 queries
- Duplicate queries

Use pagination where reporting becomes large.

---

# VALIDATION RULES

Before processing

Validate

- User exists.
- User active.
- Attendance exists.
- Check-in not duplicated.
- Check-out valid.
- Attendance date valid.

Throw meaningful exceptions.

---

# ERROR HANDLING

Throw exceptions for

- User not found
- Inactive user
- Duplicate check-in
- Duplicate checkout
- Attendance not found
- Invalid checkout time
- Invalid attendance date

Never silently fail.

---

# FUTURE INTEGRATION

AttendanceService will later integrate with

- LeaveService
- SalaryService
- DashboardService
- NotificationService

Design methods to remain reusable.

Do NOT implement those integrations in this phase.

---

# OUT OF SCOPE

Do NOT implement

- Controllers
- Routes
- Blade
- APIs
- Notifications
- Events
- Queues
- Middleware
- Salary calculation
- Leave approval

---

# CODE QUALITY RULES

Follow

- PSR-12
- SOLID
- DRY
- Constructor Dependency Injection
- Strict Return Types
- Type Hinting

No duplicate business logic.

---

# VERIFICATION

Implementation must pass

```bash
docker compose exec app php -l app/Services/AttendanceService.php
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

- AttendanceService contains all attendance business logic.
- Check-in and check-out workflow is fully implemented.
- Working hours are calculated automatically.
- Late arrival is calculated using Company Settings.
- Half-day detection is implemented.
- Attendance summaries and reports are available.
- Carbon is used for all date/time operations.
- Database transactions are used where required.
- Eager loading prevents N+1 queries.
- Controllers remain thin.
- No models, routes, migrations, controllers, or views are modified.
- Existing functionality remains unaffected.
- Code follows Laravel 10 best practices and is production-ready.