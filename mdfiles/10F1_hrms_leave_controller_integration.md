# HRMS Leave Controller Integration (Laravel 10)

## Task

10F1_hrms_leave_controller_integration.md

---

# Objective

Complete the integration between the existing Leave Management backend and the newly created Blade UI.

The Leave UI has already been created.

This task connects

- Controllers
- Services
- Routes
- Blade Views

without changing the existing Leave business logic.

The goal is to make the Leave module fully functional and browser-testable.

---

# IMPORTANT RULES

Use Laravel 10 only.

Reuse existing

- LeaveService
- LeaveServiceInterface
- LeaveController
- Leave Form Requests
- Existing Layout
- Dashboard
- Sidebar
- Navbar
- Flash Messages
- Breadcrumb
- Authentication

Do NOT duplicate business logic.

---

# SCOPE

This task may modify ONLY

- LeaveController
- routes/web.php
- Existing Blade files
- progress.md

Do NOT modify

- Models
- Services
- Business Logic
- Database
- Migrations

unless required only to resolve integration mismatches.

---

# CONTROLLER RESPONSIBILITIES

LeaveController must become a thin controller.

Controller must communicate ONLY with

```
LeaveServiceInterface
```

No database queries.

No business logic.

---

# INDEX PAGE

Connect

```
index.blade.php
```

Provide

- Summary Cards
- Paginated Leave List
- Filters
- Search Results
- Employee Photos
- Status Badges

All data must come from LeaveService.

---

# SUMMARY CARDS

Populate

- Total Leave
- Pending
- Approved
- Rejected
- Remaining Leave Balance
- Used Leave

No calculations inside Blade.

---

# FILTERS

Connect filters

- Employee
- Employee Code
- Department
- Leave Type
- Status
- From Date
- To Date

Retain values after search.

Filters must be passed to LeaveService.

---

# APPLY LEAVE

Connect

```
create.blade.php
```

Provide

- authenticated employee
- employee details
- leave types
- company settings if already available

Multi-step form must continue working.

Validation errors must remain on the same step.

Old values must be preserved.

---

# STORE

Connect existing

Store Form Request

LeaveService

Redirect using existing flash messages.

---

# EDIT

Connect

```
edit.blade.php
```

Populate

- Leave Details
- Leave Type
- Dates
- Reason

Only Pending leave should be editable.

Business rule already exists in LeaveService.

---

# UPDATE

Reuse

Update Form Request

LeaveService

Redirect back with flash message.

---

# DELETE

Pending Leave only.

Reuse existing LeaveService.

Do NOT duplicate validation.

---

# SHOW PAGE

Connect

```
show.blade.php
```

Display

- Employee
- Leave Type
- From Date
- To Date
- Total Days
- Reason
- Timeline
- Status
- Approver
- Remarks

---

# TIMELINE

Populate using LeaveService.

Examples

Applied

↓

Pending

↓

Approved

or

Rejected

No calculations inside Blade.

---

# APPROVAL PAGE

Connect

```
approval.blade.php
```

HR/Admin only.

Display

Pending Leave Requests.

Buttons

Approve

Reject

View

Reuse LeaveService.

---

# APPROVE

Use existing

LeaveService

Redirect back

Flash Success

---

# REJECT

Use existing

LeaveService

Redirect back

Flash Success

---

# LEAVE CALENDAR

Connect

```
calendar.blade.php
```

Display

- Approved Leave
- Holidays
- Weekly Off

No fake/demo data.

Employee

Only own leave.

HR/Admin

All employees.

Reuse existing LeaveService.

---

# ROUTES

Inspect existing routes.

Keep existing routes unchanged.

Add ONLY missing named routes required by the new UI.

Examples

```
hrms.leave.index

hrms.leave.create

hrms.leave.store

hrms.leave.show

hrms.leave.edit

hrms.leave.update

hrms.leave.destroy

hrms.leave.approvals

hrms.leave.approve

hrms.leave.reject

hrms.leave.calendar
```

Reuse resource routes where possible.

Do NOT duplicate routes.

---

# AUTHORIZATION

Employee

Can view only

their own leave.

HR/Admin

Can

- View all
- Approve
- Reject

Unauthorized users

Return

403

Reuse existing middleware/policies if available.

---

# EMPLOYEE PHOTO

Reuse uploaded employee image.

Fallback avatar if missing.

---

# FLASH MESSAGE

Reuse existing flash component.

No new alert system.

---

# BREADCRUMB

Display

Dashboard

↓

HRMS

↓

Leave Management

↓

Current Page

Reuse existing breadcrumb.

---

# EMPTY STATE

If no leave exists

Display

```
No Leave Requests Found
```

Reuse existing Admin Panel styling.

---

# PAGINATION

Support

- 10
- 25
- 50
- 100

Reuse Laravel paginator.

---

# EXPORT

Display existing Export buttons only.

Do NOT implement Excel/PDF generation.

---

# RESPONSIVE DESIGN

Verify

Desktop

Tablet

Mobile

Reuse Bootstrap.

---

# BLADE RESPONSIBILITY

Blade must NEVER

- Query database
- Call services
- Call models
- Execute calculations

Blade only renders data.

---

# PERFORMANCE

Reuse eager-loaded relationships.

Avoid N+1 queries.

---

# CODE QUALITY

Follow

- Laravel Best Practices
- SOLID
- DRY
- PSR-12

Keep controller thin.

---

# BROWSER VERIFICATION

Verify manually

✓ Leave List

✓ Apply Leave

✓ Edit Pending Leave

✓ Delete Pending Leave

✓ View Leave

✓ Timeline

✓ Approval Page

✓ Approve Leave

✓ Reject Leave

✓ Calendar

✓ Employee-only view

✓ HR/Admin view

✓ Summary Cards

✓ Filters

✓ Pagination

✓ Status Badges

✓ Flash Messages

✓ Breadcrumb

✓ Sidebar Active Menu

✓ Responsive Layout

✓ No Undefined Variables

✓ No Blade Errors

✓ No Broken Routes

✓ No 404 Errors

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

- Leave Controller integrated
- LeaveService integration completed
- Summary cards connected
- Filters connected
- Pagination connected
- Approval workflow connected
- Leave calendar connected
- Employee authorization verified
- HR/Admin authorization verified
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Employee can apply leave.
- Employee can edit pending leave.
- Employee can delete pending leave.
- Employee can view leave details.
- HR/Admin can approve and reject leave.
- Leave calendar works with real data.
- Summary cards display correctly.
- Filters work.
- Pagination works.
- Status badges display correctly.
- Flash messages work.
- Breadcrumb works.
- Sidebar active state works.
- Existing LeaveService is fully reused.
- Controller contains no business logic.
- Browser testing passes.
- All verification commands pass successfully.