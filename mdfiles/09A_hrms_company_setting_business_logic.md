# HRMS Company Setting Business Logic Task (Laravel 10)

## Objective

Implement the complete business logic for the Company Setting module.

This module acts as the **single source of truth** for all HRMS configuration values used throughout the system, such as office timings, attendance thresholds, salary generation settings, and weekly off configuration.

The implementation must be done inside the existing **CompanySettingService** only.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

Use the existing project architecture.

Already completed:

- ✅ Migrations
- ✅ Models
- ✅ Seeders
- ✅ Service Interfaces
- ✅ Service Skeleton
- ✅ Controllers
- ✅ Form Requests
- ✅ Routes

Only implement business logic inside the existing CompanySettingService.

Do NOT create:

- Controllers
- Models
- Routes
- Views
- Migrations
- Seeders

---

# 3. Single Responsibility Rule

CompanySettingService is responsible ONLY for:

- Reading company settings
- Updating company settings
- Providing reusable configuration values
- Validating business configuration

It MUST NOT contain:

- Attendance logic
- Leave logic
- Salary logic
- Permission logic

---

# 4. Service to Implement

Implement only:

app/Services/CompanySettingService.php

Do NOT modify other services.

---

# BUSINESS REQUIREMENTS

The service must expose reusable methods that will later be consumed by:

- AttendanceService
- LeaveService
- SalaryService
- DashboardService

---

# METHODS TO IMPLEMENT

Implement the following methods.

---

## 1. getSettings()

Purpose

Return the complete company settings record.

Return Type

```
CompanySetting
```

Behavior

- Fetch first record
- Throw exception if settings do not exist

---

## 2. updateSettings(array $data)

Purpose

Update company settings.

Allowed Fields

- office_start_time
- office_end_time
- late_after_minutes
- half_day_after_minutes
- salary_date
- weekly_off

Return

Updated CompanySetting model.

---

## 3. getOfficeStartTime()

Return

```
string
```

Example

```
10:00:00
```

---

## 4. getOfficeEndTime()

Return

```
18:00:00
```

---

## 5. getLateThreshold()

Return

```
int
```

Example

```
15
```

Meaning

Employee becomes late after:

Office Start Time
+
Late Threshold Minutes

---

## 6. getHalfDayThreshold()

Return

```
int
```

Example

```
120
```

Meaning

Late by more than configured minutes may become Half Day according to future Attendance logic.

(No attendance calculation should be implemented here.)

---

## 7. getSalaryDate()

Return

```
int
```

Example

```
5
```

---

## 8. getWeeklyOff()

Return

```
string
```

Example

```
Sunday
```

---

## 9. isWeeklyOff(Carbon $date)

Purpose

Determine whether a given date is weekly off.

Example

```
Sunday
```

returns

```
true
```

---

## 10. isOfficeOpen(Carbon $date)

Purpose

Return whether office should be open.

Rules

- Weekly off → Closed
- Otherwise → Open

Do NOT consider holidays in this phase.

Holiday integration will be implemented later.

---

# VALIDATION RULES

Service should validate:

Office Start Time

- required

Office End Time

- required

Office End Time must be greater than Office Start Time

Late Threshold

- integer
- >=0

Half Day Threshold

- integer
- greater than Late Threshold

Salary Date

- between 1 and 31

Weekly Off

Allowed values

- Monday
- Tuesday
- Wednesday
- Thursday
- Friday
- Saturday
- Sunday

Invalid configuration should throw a meaningful exception.

---

# DATABASE RULES

Use Eloquent only.

Do NOT use raw SQL.

Use:

- first()
- update()
- find()

No Query Builder.

---

# PERFORMANCE RULES

- Query CompanySetting only once where possible.
- Avoid duplicate database calls.
- Reuse loaded model within the service.
- Keep implementation ready for future caching.
- Do NOT implement caching in this phase.

---

# ERROR HANDLING

Throw exceptions when:

- Settings record not found
- Invalid office timings
- Invalid salary date
- Invalid weekly off
- Invalid threshold values

Never silently fail.

---

# OUT OF SCOPE

Do NOT implement:

- Attendance calculations
- Leave calculations
- Salary calculations
- Holiday calculations
- Events
- Notifications
- Queues
- Caching
- Middleware

---

# CODE QUALITY RULES

Follow:

- PSR-12
- SOLID
- DRY
- Type Hinting
- Return Types
- Constructor Dependency Injection

No duplicate logic.

---

# VERIFICATION

The implementation must pass:

```bash
docker compose exec app php -l app/Services/CompanySettingService.php
```

```bash
docker compose exec app php artisan optimize:clear
```

```bash
docker compose exec app php artisan optimize
```

```bash
docker compose exec app php artisan about
```

---

# SUCCESS CRITERIA

Task is complete when:

- CompanySettingService contains all required business logic.
- All methods are reusable by other services.
- Validation rules are enforced.
- No controllers contain business logic.
- No new models, routes, migrations, or views are created.
- Code follows Laravel 10 best practices.
- Existing functionality remains unaffected.