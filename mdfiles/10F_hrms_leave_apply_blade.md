# HRMS Leave Management Blade Integration (Laravel 10)

## Task

10F_hrms_leave_apply_blade.md

---

# Objective

Implement the complete Leave Management UI for the HRMS system.

The Leave module must integrate with the already completed

- Leave Controller
- Leave Service
- Leave Business Logic
- Employee Module
- Attendance Module
- Dashboard
- Authentication

This phase is responsible ONLY for presentation.

No business logic should be added.

Reuse the existing architecture.

---

# LARAVEL VERSION

Use Laravel 10 only.

Do NOT use Laravel 11 or Laravel 12 features.

---

# EXISTING ARCHITECTURE

Reuse existing

- LeaveController
- LeaveService
- LeaveServiceInterface
- Leave Form Requests
- Existing Routes
- Dashboard
- Layout
- Sidebar
- Navbar
- Breadcrumb
- Flash Messages

Do NOT duplicate logic.

---

# SCOPE

This phase ONLY includes

- Blade Views
- Blade Partials
- Bootstrap UI
- Existing Controller Integration
- Existing Service Integration

Do NOT implement

- Business Logic
- Models
- Services
- Controllers
- Routes
- APIs
- Database Queries

---

# DIRECTORY STRUCTURE

Create

resources/views/Adminpanel/HRMS/Leave/

```
index.blade.php

create.blade.php

edit.blade.php

show.blade.php

approval.blade.php

calendar.blade.php

_partials/

    filters.blade.php

    summary-cards.blade.php

    leave-table.blade.php

    form.blade.php

    timeline.blade.php
```

---

# LEAVE DASHBOARD

Display summary cards

- Total Leave Balance
- Used Leave
- Remaining Leave
- Pending Requests
- Approved Leaves
- Rejected Leaves

Use Dashboard-style Bootstrap cards.

No calculations inside Blade.

---

# APPLY LEAVE

Create a professional multi-step form.

Step 1

Employee Information

- Employee
- Employee Code
- Department
- Designation

(Read-only)

---

Step 2

Leave Details

- Leave Type
- From Date
- To Date
- Total Days (read-only from controller)
- Half Day (if supported)
- Emergency Leave (if supported)

---

Step 3

Reason

Large textarea

Optional attachment upload placeholder (UI only)

---

Step 4

Review

Display all entered information before submission.

---

# REQUIRED FIELDS

Display

```
*
```

for required fields.

---

# LEAVE LIST

Columns

- Employee Photo
- Employee Code
- Employee Name
- Leave Type
- From Date
- To Date
- Total Days
- Applied Date
- Status
- Action

---

# STATUS BADGES

Bootstrap badges

Pending

Approved

Rejected

Distinct colors.

---

# SEARCH & FILTERS

Provide

- Employee
- Employee Code
- Department
- Leave Type
- Status
- From Date
- To Date

Retain selected filters.

---

# HR APPROVAL PAGE

Create

approval.blade.php

Display

Pending Leave Requests

Actions

Approve

Reject

View

No business logic inside Blade.

---

# LEAVE DETAILS

Create

show.blade.php

Display

Employee

Leave Type

Reason

Duration

Status

Applied Date

Approved By

Approved Date

Remarks

Timeline

---

# TIMELINE

Reusable partial

Example

Applied

↓

Pending

↓

Approved

or

Rejected

Reuse Bootstrap Timeline/Card style if available.

---

# LEAVE CALENDAR

Create

calendar.blade.php

Bootstrap table only.

No JS calendar plugins.

Display

- Leave
- Holiday
- Weekly Off

Employee leave should appear only for authorized users.

---

# EMPLOYEE VIEW

Employee sees

Only

their own leave requests.

---

# HR / ADMIN VIEW

HR/Admin sees

All leave requests.

Approval buttons visible only for authorized users.

---

# EMPLOYEE PHOTO

Reuse Employee upload path.

Fallback avatar if image missing.

---

# EMPTY STATES

Display

No Leave Requests Found

Reuse existing Admin Panel style.

---

# PAGINATION

Support

10

25

50

100

Reuse Laravel pagination.

---

# EXPORT

Display buttons only

Export Excel

Print

Do NOT implement export logic.

---

# FLASH MESSAGE

Reuse existing flash component.

Display

- Success
- Error
- Warning
- Validation Errors

---

# BREADCRUMB

Dashboard

↓

HRMS

↓

Leave Management

↓

Current Page

Reuse existing breadcrumb component.

---

# RESPONSIVE DESIGN

Desktop

Tablet

Mobile

Reuse Bootstrap.

---

# BLADE RESPONSIBILITY

Blade must NEVER

- Query database
- Call models
- Call services
- Execute calculations

Blade only renders UI.

---

# EXISTING FUNCTIONALITY PROTECTION

Do NOT modify

- Attendance Module
- Employee Module
- Dashboard
- Authentication
- Sidebar
- Navbar
- Holiday Module
- Leave Type Module

Only extend HRMS.

---

# ROUTES

Use ONLY existing named routes.

Do NOT create new routes.

If a required route is missing,

document it in progress.md instead of inventing one.

---

# PERFORMANCE

Reuse eager-loaded data.

Avoid N+1 rendering.

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- Bootstrap Best Practices
- SOLID
- DRY
- PSR-12

---

# BROWSER VERIFICATION

Verify

✓ Apply Leave

✓ Edit Leave

✓ View Leave

✓ Leave List

✓ Leave Approval

✓ Leave Calendar

✓ Summary Cards

✓ Filters

✓ Pagination

✓ Status Badges

✓ Employee Photo

✓ Flash Messages

✓ Breadcrumb

✓ Responsive Layout

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

progress.md

Include

- Leave List completed
- Apply Leave completed
- Leave Details completed
- Leave Approval completed
- Leave Calendar completed
- Summary Cards integrated
- Filters integrated
- Pagination integrated
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Employee can apply leave.
- Employee can view their leave history.
- HR/Admin can approve or reject leave.
- Leave dashboard summary works.
- Leave calendar displays correctly.
- Status badges render correctly.
- Employee photos display correctly.
- Filters and pagination work.
- Flash messages work.
- Breadcrumb works.
- Responsive layout works.
- Existing Admin Panel design is preserved.
- No business logic exists in Blade.
- Existing services/controllers are reused.
- Manual browser verification passes.
- All verification commands pass successfully.