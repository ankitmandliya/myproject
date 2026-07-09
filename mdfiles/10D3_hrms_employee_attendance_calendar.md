# HRMS Employee Attendance Calendar Integration (Laravel 10)

## Objective

Convert the Attendance Calendar into an employee-specific calendar.

The Attendance module must support role-based visibility.

Employee

- Can view only their own attendance.

HR/Admin

- Can view attendance of any employee.

No business logic should be duplicated.

Reuse the existing AttendanceService.

---

# IMPORTANT RULES

## Laravel Version

- Laravel 10 only.

---

# ARCHITECTURE

Reuse existing

- AttendanceService
- AttendanceController
- UserService
- UserController
- Employee Module
- Authentication
- Dashboard
- Layout

Do NOT duplicate attendance logic.

---

# ROLE BASED ACCESS

## Employee

When logged in as Employee

Attendance menu

```
HRMS

↓

Attendance
```

must open

```
My Attendance
```

The employee must only see

his/her own attendance.

Employee ID must come from

```
auth()->user()
```

Do NOT allow employee ID in URL.

---

## HR / Admin

HR/Admin can open

```
Employees

↓

View

↓

Attendance
```

or

```
Employees

↓

Attendance History
```

for any employee.

---

# EMPLOYEE LIST

On Employee List

Add new Action

```
View Attendance
```

Button/Icon

Example

```
View

Edit

Attendance
```

Use existing Bootstrap buttons.

---

# EMPLOYEE PROFILE

On Employee Show page

Add

```
Attendance Summary
```

Display

- Present
- Absent
- Leave
- Late
- Half Day

Provide buttons

```
History

Calendar
```

---

# CONTROLLER

AttendanceController must support

```
myAttendance()

employeeAttendance($employeeId)

calendar($employeeId)

history($employeeId)
```

Reuse AttendanceService.

Do NOT duplicate queries.

---

# ROUTES

Employee

```
hrms.my-attendance
```

HR/Admin

```
hrms.attendance.employee

hrms.attendance.history

hrms.attendance.calendar
```

Use route model binding where possible.

---

# CALENDAR

Calendar must display

selected employee only.

Never merge attendance of multiple employees.

---

# HISTORY

History page must display

only selected employee.

Include

- Date
- Check In
- Check Out
- Working Hours
- Status

---

# SUMMARY

Above calendar display

Employee

- Photo
- Name
- Employee Code
- Department
- Designation

Summary Cards

- Present
- Absent
- Leave
- Late
- Half Day

Reuse Dashboard cards.

---

# ACCESS CONTROL

Employee

Cannot open

```
/attendance/calendar/2
```

for another employee.

Return

```
403 Forbidden
```

HR/Admin

Can access every employee.

---

# UI

Reuse

- Existing Attendance Calendar
- Existing History Page
- Existing Summary Cards
- Existing Breadcrumb
- Existing Flash Messages

Do NOT redesign UI.

---

# EMPTY STATE

If employee has no attendance

Display

```
No Attendance Found
```

Calendar should still render.

---

# PERFORMANCE

Reuse eager loading.

No N+1 queries.

No duplicate service calls.

---

# SECURITY

Never trust employee ID from request.

Authorization must happen before loading attendance.

---

# BROWSER VERIFICATION

Employee Login

✓ Attendance menu opens My Attendance

✓ Calendar shows only own attendance

✓ History shows only own attendance

✓ Cannot access another employee

HR Login

✓ Employee List shows View Attendance button

✓ Attendance button opens employee calendar

✓ History works

✓ Summary works

✓ Breadcrumb works

✓ Responsive UI works

✓ No Blade errors

✓ No SQL errors

---

# VERIFICATION COMMANDS

Run

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
docker compose exec app php artisan view:cache
```

```bash
docker compose exec app php artisan about
```

Run PHP lint

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

- Employee attendance integration completed
- Role-based attendance implemented
- Employee attendance calendar completed
- Employee attendance history completed
- View Attendance action added
- Authorization implemented
- Browser verification completed

---

# SUCCESS CRITERIA

Task is complete when

- Employees can view only their own attendance.
- HR/Admin can view attendance of any employee.
- Employee List contains View Attendance action.
- Employee Profile contains Attendance section.
- Calendar is employee-specific.
- History is employee-specific.
- Existing AttendanceService is fully reused.
- No duplicate business logic exists.
- Authorization is enforced.
- Existing UI remains unchanged.
- Browser verification passes.