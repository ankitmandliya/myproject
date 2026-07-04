# HRMS Leave Apply Business Logic Task (Laravel 10)

## Objective

Implement the complete business logic for the **Leave Management** module.

This phase is responsible for handling the complete employee leave lifecycle including leave application, validation, approval workflow, rejection workflow, leave cancellation, overlapping leave detection, leave balance validation, and reporting.

All business logic must be implemented **only inside the existing LeaveService**.

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

Implement business logic only inside

```
app/Services/LeaveService.php
```

Do NOT modify

- Controllers
- Models
- Routes
- Views
- Migrations
- Seeders

---

# 3. Responsibilities

LeaveService is responsible ONLY for

- Apply Leave
- Leave Validation
- Leave Approval
- Leave Rejection
- Leave Cancellation
- Leave Status Management
- Leave Reports

It MUST NOT contain

- Attendance calculation
- Salary calculation
- Notification logic
- Dashboard logic

---

# DEPENDENCIES

LeaveService may consume

- UserServiceInterface
- AttendanceServiceInterface
- CompanySettingServiceInterface

Use constructor dependency injection only.

---

# BUSINESS REQUIREMENTS

Implement the following methods.

---

## 1. applyLeave(int $userId, array $data)

Purpose

Employee submits a leave request.

Rules

- User must exist.
- User must be active.
- Leave type must exist.
- From Date must not be greater than To Date.
- Dates cannot be in invalid format.
- Leave cannot overlap existing approved or pending leave.
- Calculate total leave days automatically.
- Default status = Pending.

Store

- user_id
- leave_type_id
- from_date
- to_date
- total_days
- reason
- status

Return

```
LeaveApply
```

---

## 2. validateLeave(int $userId, array $data)

Purpose

Validate leave before applying.

Validate

- Leave type
- Date range
- User
- Overlapping leave
- Future dates

Return

```
bool
```

---

## 3. calculateLeaveDays(Carbon $from, Carbon $to)

Purpose

Calculate leave duration.

Rules

Calculate total calendar days.

Return

```
int
```

---

## 4. hasOverlappingLeave(int $userId, Carbon $from, Carbon $to)

Purpose

Detect overlapping leave.

Return

```
bool
```

---

## 5. approveLeave(int $leaveId, int $approvedBy)

Purpose

Approve leave request.

Rules

- Leave must exist.
- Status must be Pending.
- Store approved_by.
- Store approved_at.
- Status becomes Approved.

Return

```
LeaveApply
```

---

## 6. rejectLeave(int $leaveId, int $approvedBy)

Purpose

Reject leave request.

Rules

- Leave must exist.
- Status must be Pending.
- Store approver.
- Store approval date.
- Status becomes Rejected.

Return

```
LeaveApply
```

---

## 7. cancelLeave(int $leaveId)

Purpose

Employee cancels leave.

Rules

Only Pending leave can be cancelled.

Return

```
LeaveApply
```

---

## 8. getLeave(int $leaveId)

Return

```
LeaveApply|null
```

---

## 9. getUserLeaves(int $userId)

Return

```
Collection
```

Use eager loading.

---

## 10. getPendingLeaves()

Return

```
Collection
```

---

## 11. getApprovedLeaves()

Return

```
Collection
```

---

## 12. getRejectedLeaves()

Return

```
Collection
```

---

## 13. getLeavesByStatus(string $status)

Return

```
Collection
```

---

## 14. getLeavesByMonth(int $month, int $year)

Return

```
Collection
```

---

## 15. getLeavesBetweenDates(Carbon $from, Carbon $to)

Return

```
Collection
```

---

## 16. getEmployeeLeaveSummary(int $userId)

Return

```
array
```

Include

```
Total Applied

Pending

Approved

Rejected

Cancelled

Total Leave Days
```

---

## 17. getLeaveReport(int $month, int $year)

Purpose

Generate leave report for all employees.

Return

```
Collection
```

Use eager loading.

---

## 18. isLeaveApproved(int $leaveId)

Return

```
bool
```

---

## 19. isLeavePending(int $leaveId)

Return

```
bool
```

---

## 20. isLeaveRejected(int $leaveId)

Return

```
bool
```

---

## 21. deleteLeave(int $leaveId)

Purpose

Delete leave request.

Rules

Only Pending leave may be deleted.

Return

```
bool
```

---

# DATABASE TRANSACTION RULES

Use transactions for

- applyLeave()
- approveLeave()
- rejectLeave()
- cancelLeave()
- deleteLeave()

Rollback automatically on failure.

---

# DATE RULES

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

Use eager loading.

Avoid

- Raw SQL
- Manual joins

---

# PERFORMANCE RULES

Prevent

- N+1 queries
- Duplicate queries

Use pagination where reports become large.

---

# VALIDATION RULES

Before processing validate

- User exists
- User active
- Leave exists
- Leave type exists
- Valid dates
- Duplicate leave
- Overlapping leave
- Leave status

Throw meaningful exceptions.

---

# ERROR HANDLING

Throw exceptions for

- User not found
- Inactive user
- Leave not found
- Leave type not found
- Invalid date range
- Duplicate leave
- Overlapping leave
- Invalid status transition

Never silently fail.

---

# FUTURE INTEGRATION

LeaveService will later integrate with

- AttendanceService
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
- Salary deduction
- Attendance marking

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
docker compose exec app php -l app/Services/LeaveService.php
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

- LeaveService contains all leave business logic.
- Leave application workflow is fully implemented.
- Leave approval and rejection workflow is implemented.
- Leave cancellation is implemented.
- Leave overlap validation is implemented.
- Total leave days are calculated automatically.
- Leave reports and summaries are available.
- Carbon is used for all date operations.
- Database transactions are used where required.
- Controllers remain thin.
- No models, routes, migrations, controllers, or views are modified.
- Existing functionality remains unaffected.
- Code follows Laravel 10 best practices and is production-ready.
```