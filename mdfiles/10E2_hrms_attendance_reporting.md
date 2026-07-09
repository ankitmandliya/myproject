# HRMS Attendance Reporting Module (Laravel 10)

## Objective

Implement the Attendance Reporting module for HRMS.

This module provides attendance analytics, summaries, and reports for HR/Admin.

Attendance marking, attendance calendar, attendance history, and attendance widget have already been completed.

This task is ONLY for reporting.

---

# LARAVEL VERSION

Use Laravel 10 only.

Do NOT use Laravel 11 or Laravel 12 features.

---

# SCOPE

This task includes ONLY

- Attendance Reports
- Attendance Summary
- Employee Statistics
- Department Statistics
- Monthly Reports
- Filters
- Report UI

Do NOT implement

- Attendance Marking
- Calendar
- Check In
- Check Out
- Attendance Business Logic
- Services
- Models
- Database changes

Reuse the existing architecture.

---

# EXISTING ARCHITECTURE

Reuse existing

- AttendanceService
- AttendanceController
- DashboardService
- CompanySettingService
- UserService
- Existing Routes
- Existing Layout
- Existing HRMS Design

Do NOT duplicate business logic.

---

# MODULE LOCATION

Create views inside

resources/views/Adminpanel/HRMS/Attendance/Reports/

Create

```
index.blade.php

employee-report.blade.php

department-report.blade.php

monthly-report.blade.php

_partials/

summary-cards.blade.php

filters.blade.php

attendance-table.blade.php
```

---

# REPORT DASHBOARD

Display summary cards

- Total Employees
- Present Today
- Absent Today
- Late Today
- Half Day
- Leave Today
- Holidays This Month
- Weekly Offs This Month

Reuse Dashboard style cards.

No calculations inside Blade.

---

# FILTERS

Provide filters

- Employee
- Employee Code
- Department
- Designation
- Attendance Status
- From Date
- To Date
- Month
- Year

Retain selected values.

---

# EMPLOYEE REPORT

Display

- Employee Photo
- Employee Code
- Employee Name
- Department
- Designation
- Present Days
- Late Days
- Half Days
- Leave Days
- Holiday Days
- Weekly Off Days
- Absent Days
- Total Working Hours
- Average Check In
- Average Check Out

---

# DEPARTMENT REPORT

Display

- Department Name
- Total Employees
- Present
- Absent
- Late
- Half Day
- Leave
- Average Attendance %

---

# MONTHLY REPORT

Display month-wise summary

Columns

- Employee
- Present
- Late
- Half Day
- Leave
- Holiday
- Weekly Off
- Absent
- Working Hours

---

# STATUS BADGES

Reuse Bootstrap badges

Supported

- Present
- Late
- Half Day
- Leave
- Holiday
- Weekly Off
- Absent

---

# ATTENDANCE TABLE

Display

- Date
- Check In
- Check Out
- Working Hours
- Status

Reuse existing attendance partials wherever possible.

---

# EXPORT BUTTONS

Display buttons only

- Export Excel
- Export PDF
- Print

Do NOT implement export logic in this task.

---

# PAGINATION

Support

- 10
- 25
- 50
- 100

Reuse Laravel pagination.

---

# EMPTY STATE

Display

```
No Attendance Report Found
```

Reuse existing empty-state UI.

---

# BREADCRUMB

Dashboard

↓

HRMS

↓

Attendance

↓

Reports

Reuse existing breadcrumb component.

---

# FLASH MESSAGE

Reuse existing flash component.

---

# RESPONSIVE DESIGN

Desktop

Responsive tables

Tablet

Scrollable tables

Mobile

Bootstrap responsive cards/tables

Do NOT create separate mobile views.

---

# BLADE RESPONSIBILITY

Blade MUST NOT

- Query database
- Calculate attendance
- Calculate totals
- Call services

Blade ONLY renders data supplied by AttendanceController.

---

# PERFORMANCE

Reuse eager-loaded data.

Avoid N+1 queries.

Reuse existing AttendanceService methods.

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- Bootstrap Best Practices
- SOLID
- DRY
- PSR-12

Reuse Blade partials.

---

# ROUTES

Reuse existing Attendance routes where possible.

If a dedicated reports route is required, follow the existing HRMS route naming convention.

Example

```
hrms.attendance.reports
```

Do not create duplicate resource routes.

---

# BROWSER VERIFICATION

Verify

✓ Report dashboard opens

✓ Filters work

✓ Employee report

✓ Department report

✓ Monthly report

✓ Summary cards

✓ Attendance table

✓ Pagination

✓ Responsive layout

✓ Flash messages

✓ Breadcrumb

✓ No undefined variables

✓ No Blade errors

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
docker compose exec app php artisan view:cache
```

```bash
docker compose exec app php artisan route:list
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

progress.md

Include

- Attendance Reports completed
- Employee Report completed
- Department Report completed
- Monthly Report completed
- Summary cards integrated
- Filters integrated
- Pagination integrated
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Attendance Reports dashboard works.
- Employee report works.
- Department report works.
- Monthly report works.
- Summary cards display correctly.
- Filters work.
- Attendance table works.
- Pagination works.
- Existing Attendance module remains unchanged.
- Existing Dashboard remains unchanged.
- Existing architecture is reused.
- Blade contains no business logic.
- Browser verification passes.
- All verification commands pass successfully.