# 10F7_hrms_leave_attendance_integration.md

## Module

HRMS → Leave Management + Attendance Management

---

# Objective

Integrate the Leave Module with the Attendance Module so that approved leave automatically affects attendance, reports, dashboard, payroll preparation, and attendance marking.

This phase **must not duplicate business logic**.

Attendance must always consume the Leave module's approved leave records and leave calculation snapshot.

---

# IMPORTANT

Laravel 10 only.

Reuse existing

- AttendanceService
- LeaveService
- LeaveApprovalService
- LeavePolicyService
- AttendanceController
- Attendance Calendar
- Employee Calendar
- Attendance Reports
- Attendance Widget
- Company Settings
- Holiday
- EmployeeLeaveBalance

Never duplicate calculations.

Attendance must only consume Leave data.

---

# BUSINESS FLOW

```
Employee

↓

Apply Leave

↓

Approval Workflow

↓

Approved Leave

↓

Attendance Integration

↓

Attendance Calendar

↓

Attendance Reports

↓

Payroll
```

Attendance should never independently decide Leave status.

---

# SINGLE SOURCE OF TRUTH

Attendance must always read

```
Approved Leave

+

Leave Snapshot

+

Company Settings

+

Holiday Table
```

Never recalculate leave duration.

---

# ATTENDANCE STATUS PRIORITY

When determining attendance for a day, follow this priority order:

```
1. Company Holiday
2. Approved Leave
3. Weekly Off
4. Present
5. Late
6. Half Day
7. LWP
8. Absent
```

Never display two statuses for the same day unless it is a valid half-day combination.

---

# APPROVED LEAVE

When a leave is approved

Attendance for those dates should automatically become

```
Leave
```

No attendance marking required.

---

# ATTENDANCE TOGGLE

Header Attendance Widget

Current behavior

```
Toggle ON

Toggle OFF
```

New behavior

If today is approved leave

Hide toggle.

Display

```
Today's Status

Casual Leave

Approved

Attendance not required today
```

Disable attendance actions.

---

# CHECK-IN VALIDATION

If today is approved leave

Reject

```
Check In
```

Return

```
You are already on approved leave today.
```

---

# CHECK-OUT VALIDATION

Reject

```
Check Out
```

if attendance never started.

Reject

if today is approved leave.

---

# CALENDAR

Attendance Calendar

Show

```
CL

SL

EL

LWP

ML

PL

```

instead of

```
Absent
```

Employee Calendar

HR Calendar

Admin Calendar

must all display approved leave.

---

# LEAVE BADGES

Use Bootstrap badges.

Example

```
CL

Blue

SL

Green

EL

Purple

LWP

Dark

ML

Pink

PL

Info
```

Configurable through helper.

---

# ATTENDANCE HISTORY

Attendance History

Display

```
Present

Late

Half Day

Leave

Holiday

Weekly Off

LWP

Absent
```

Leave rows should display

```
Leave Type

Reason

Approved By
```

---

# HALF DAY LEAVE

Morning Half Leave

↓

Employee checks in after lunch

Attendance should show

```
First Half Leave

Second Half Present
```

Afternoon Half Leave

↓

Morning Present

↓

Afternoon Leave

Attendance Summary

```
Half Day
```

Do not mark Absent.

---

# LWP

Approved Leave Without Pay

Attendance Status

```
LWP
```

Later consumed by Payroll.

Do not deduct balance.

---

# HOLIDAY

If company holiday exists

Show

```
Holiday
```

Do not show Leave.

Holiday has higher priority.

---

# WEEKLY OFF

Weekly Off

should display only if

No Leave

No Holiday

No Attendance

---

# ATTENDANCE REPORTS

Update reports.

Include

```
Present

Late

Half Day

Leave

Holiday

Weekly Off

LWP

Absent
```

Totals

```
Working Days

Present

Late

Half Day

Leave

Holiday

Weekly Off

LWP

Absent
```

---

# DASHBOARD

Employee Dashboard

Today's Status

```
Present

Late

Leave

Holiday

Weekly Off
```

HR Dashboard

```
Present Today

Leave Today

Late Today

Absent Today

Holiday

Weekly Off
```

---

# EMPLOYEE PROFILE

Attendance tab

Display

```
Date

Status

Leave Type

Working Hours

Check In

Check Out
```

---

# LEAVE CANCELLATION

If approved leave

is cancelled

Attendance must automatically refresh.

Remove Leave status.

Return to normal attendance workflow.

---

# LEAVE REVOCATION

If Admin revokes leave

Attendance recalculates automatically.

---

# LEAVE UPDATE

Pending Leave

may recalculate.

Approved Leave

must not.

Attendance always uses snapshot.

---

# ATTENDANCE SERVICE

Create reusable methods

```
getAttendanceStatus()

getAttendanceSource()

getLeaveStatus()

getLeaveBadge()

isAttendanceAllowed()

canCheckIn()

canCheckOut()
```

AttendanceController

must only call service.

---

# CALENDAR LEGEND

Display

```
Present

Late

Half Day

Leave

LWP

Holiday

Weekly Off

Absent
```

Responsive.

---

# REPORT EXPORT

Attendance reports

must include

```
Leave Type

Approval Status

LWP

Half Leave
```

Export implementation not required.

Only prepare data.

---

# AUTHORIZATION

Employee

Own attendance only.

Manager

Reporting employees.

HR

All.

Admin

All.

Return

403

when unauthorized.

---

# PERFORMANCE

Never query leave

inside Blade.

AttendanceService

should eager load

Approved Leave

Holiday

Company Settings

for requested month.

Avoid N+1 queries.

---

# EVENTS

Listen for

```
LeaveApproved

LeaveCancelled

LeaveRevoked
```

Refresh attendance cache.

Future payroll will consume same events.

---

# NOTIFICATIONS

Employee

```
Today's approved leave has started.
```

HR

```
Leave reflected in attendance.
```

Database notifications only.

---

# VALIDATION

Reject

Attendance marking

during approved leave.

Reject

duplicate attendance.

Reject

attendance on Holiday.

Reject

attendance on Weekly Off

if company policy blocks it.

---

# SMOKE TESTS

Verify

✓ Approved leave appears in attendance calendar

✓ Employee calendar updated

✓ HR calendar updated

✓ Attendance widget hides toggle

✓ Attendance widget displays leave message

✓ Check In blocked during leave

✓ Check Out blocked during leave

✓ Half Day Leave merges correctly

✓ LWP displayed correctly

✓ Holiday overrides leave

✓ Weekly Off displayed correctly

✓ Leave cancellation refreshes attendance

✓ Leave revocation refreshes attendance

✓ Reports count leave correctly

✓ Dashboard counts leave correctly

✓ Employee profile displays leave

✓ No duplicate attendance records

✓ No Blade calculations

✓ AttendanceService remains single source for attendance

---

# REQUIRED VERIFICATION

Run

```bash
docker compose exec app php artisan optimize:clear
```

```bash
docker compose exec app php artisan optimize
```

```bash
docker compose exec app php artisan view:cache
```

```bash
docker compose exec app php artisan route:list
```

```bash
docker compose exec app php artisan about
```

```bash
find app -name "*.php" -exec php -l {} \;
```

---

# PROGRESS UPDATE

Update

```
progress.md
```

Include

- Leave and Attendance integration completed
- Attendance widget updated
- Attendance calendar integrated
- Attendance reports updated
- Dashboard updated
- Attendance validation updated
- Leave events integrated
- Notifications prepared
- Verification completed

---

# SUCCESS CRITERIA

Task is complete when

- Approved leave automatically appears in attendance.
- Attendance toggle is disabled during approved leave.
- Attendance reports count leave correctly.
- Leave cancellation refreshes attendance.
- Leave revocation refreshes attendance.
- Half-day leave integrates correctly.
- Holiday and weekly off priorities are respected.
- LWP is handled correctly.
- Dashboard reflects leave.
- Existing architecture remains unchanged.
- No duplicate business logic exists.
- Verification commands pass successfully.
- Manual browser verification passes.
```