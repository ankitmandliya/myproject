# HRMS Attendance Blade Integration Task (Laravel 10)

## Objective

This phase implements the complete Attendance Management UI for the HRMS system.

The Attendance module must integrate with the already completed

- Attendance Controller
- Attendance Service
- Attendance Business Logic
- Dashboard
- Layout
- Authentication

This phase is responsible for **presentation only**.

No business logic should be added.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

Reuse the existing

- Models
- Services
- Controllers
- Routes
- Form Requests
- Dashboard
- Layout
- Sidebar
- Navbar

Do NOT duplicate logic.

---

## 3. Scope Restriction

This phase ONLY includes

- Blade Views
- Blade Partials
- Bootstrap UI
- Existing Controller Integration
- Existing Service Integration

Do NOT implement

- Business Logic
- Database Queries
- Models
- Services
- Controllers
- Routes
- APIs
- Migrations

---

# EXISTING UI REUSE (MANDATORY)

Reuse existing

- Admin Layout
- Sidebar
- Navbar
- Breadcrumb
- Flash Messages
- Bootstrap Cards
- Bootstrap Tables
- Bootstrap Forms
- Existing Icons

Do NOT redesign the Admin Panel.

---

# EXISTING FUNCTIONALITY PROTECTION

Do NOT modify

- Employee Module
- Holiday Module
- Leave Type Module
- Dashboard
- Authentication
- Existing Layout
- Existing Routes
- Existing Services

Only extend the HRMS module.

---

# MODULE STRUCTURE

Create views inside

```
resources/views/Adminpanel/HRMS/Attendance/
```

Create

```
index.blade.php

show.blade.php

attendance-history.blade.php

attendance-calendar.blade.php

_partials/

    filters.blade.php

    summary-cards.blade.php

    attendance-table.blade.php

    attendance-modal.blade.php
```

---

# ATTENDANCE LIST PAGE

Display

- Employee Photo
- Employee Code
- Employee Name
- Department
- Designation
- Attendance Date
- Check In
- Check Out
- Working Hours
- Attendance Status
- Action

---

# STATUS BADGES

Use Bootstrap badges.

Supported Status

- Present
- Absent
- Late
- Half Day
- Leave
- Holiday

Each status should have a unique badge color.

---

# SUMMARY CARDS

Display

- Total Employees
- Present Today
- Absent Today
- Late Today
- Half Day
- Leave Today

Use Dashboard style cards.

No calculations inside Blade.

---

# SEARCH & FILTERS

Provide filters

- Employee Name
- Employee Code
- Department
- Designation
- Status
- From Date
- To Date

Retain filter values after search.

---

# CHECK-IN / CHECK-OUT

Display action buttons only when allowed.

Example

```
Check In

Check Out
```

Button visibility must depend on values already provided by Controller.

Blade must NOT determine business rules.

---

# DAILY ATTENDANCE VIEW

Create

```
show.blade.php
```

Display

- Employee Information
- Attendance Date
- Check In
- Check Out
- Working Hours
- Late Minutes
- Half Day
- Status
- Notes (if available)

---

# MONTHLY ATTENDANCE HISTORY

Create

```
attendance-history.blade.php
```

Display

Monthly attendance table.

Columns

- Date
- Check In
- Check Out
- Working Hours
- Status

Include pagination.

---

# ATTENDANCE CALENDAR

Create

```
attendance-calendar.blade.php
```

Display monthly calendar.

Mark

- Present
- Absent
- Leave
- Holiday
- Weekend

Do NOT use third-party calendar plugins.

Use Bootstrap table/grid only.

---

# ATTENDANCE MODAL

Create reusable modal

```
attendance-modal.blade.php
```

Purpose

Display attendance details without leaving the list page.

Reuse Bootstrap Modal.

---

# EMPLOYEE PHOTO

Display employee photo.

Reuse Employee upload path.

If image missing

Display default avatar.

---

# EMPTY STATE

If no attendance exists

Display

```
No Attendance Records Found
```

Show friendly illustration/icon if existing Admin Panel already provides one.

---

# PAGINATION

Support

- 10
- 25
- 50
- 100

Reuse Laravel pagination.

---

# EXPORT BUTTON

Display

```
Export Excel
```

Button only.

Do NOT implement Excel generation in this phase.

---

# BREADCRUMB

Display

Dashboard

↓

HRMS

↓

Attendance

↓

Current Page

Reuse existing breadcrumb component.

---

# FLASH MESSAGE

Reuse existing flash component.

Display

- Success
- Error
- Warning
- Validation Errors

Do NOT introduce another alert system.

---

# RESPONSIVE DESIGN

Desktop

Responsive table.

Tablet

Scrollable table.

Mobile

Bootstrap responsive cards/table.

No separate mobile Blade.

---

# BLADE RESPONSIBILITY

Blade MUST NOT

- Query database
- Call Services
- Call Models
- Execute calculations
- Apply attendance rules

Blade ONLY

- Display variables
- Render UI
- Render forms
- Render badges
- Render tables
- Render pagination

---

# PERFORMANCE

Reuse eager-loaded data.

Do NOT trigger additional queries inside Blade.

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- Bootstrap Best Practices
- DRY
- Shared Partials
- PSR-12

Avoid duplicated HTML.

---

# ROUTES

Use ONLY existing named routes.

Example

```
hrms.attendance.index

hrms.attendance.show

hrms.attendance.store

hrms.attendance.update
```

Do NOT create additional routes.

---

# OPTIONAL FUTURE FEATURES (DO NOT IMPLEMENT)

- Attendance Import
- Attendance Excel Export Logic
- Attendance PDF
- Attendance Geo Location
- Biometric Integration
- QR Attendance
- Face Recognition

---

# BROWSER VERIFICATION

Verify manually

✓ Attendance List

✓ Search

✓ Filters

✓ Pagination

✓ Summary Cards

✓ Attendance Detail

✓ Attendance History

✓ Calendar View

✓ Employee Images

✓ Status Badges

✓ Responsive Layout

✓ Flash Messages

✓ Breadcrumb

✓ Sidebar Active Menu

✓ No Broken Images

✓ No Undefined Variables

✓ No Blade Errors

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

```
progress.md
```

Include

- Files created
- Files modified
- Attendance List completed
- Attendance Details completed
- Attendance History completed
- Attendance Calendar completed
- Shared partials created
- Summary cards integrated
- Filters integrated
- Pagination integrated
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Attendance List is fully functional.
- Employee photos display correctly.
- Summary cards display correctly.
- Search and filters work.
- Daily attendance page works.
- Monthly attendance history works.
- Attendance calendar works.
- Bootstrap modal works.
- Status badges display correctly.
- Pagination works.
- Flash messages work.
- Breadcrumb works.
- Responsive layout works.
- Existing Admin Panel design is preserved.
- No business logic exists in Blade.
- Existing architecture remains unchanged.
- Existing services/controllers are reused.
- Manual browser verification passes.
- All verification commands pass successfully.