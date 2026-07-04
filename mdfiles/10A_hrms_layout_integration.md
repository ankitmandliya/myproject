# HRMS Layout Integration Task (Laravel 10)

## Objective

This phase integrates the HRMS module into the existing Admin Panel layout.

The purpose of this task is to connect the existing HRMS backend with the application's common layout without changing the current application design.

This task is ONLY responsible for layout integration and navigation.

No CRUD implementation is part of this task.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only
- Do NOT use Laravel 11 or Laravel 12 features

---

## 2. Existing Application Rules

The existing Admin Panel is already working.

Do NOT modify or break:

- Authentication
- Login
- Registration
- Existing Dashboard
- Existing Sidebar Structure
- Existing Layout
- Existing Leave Type module
- Existing Holiday module

Only integrate new HRMS menu items.

---

## 3. Scope Restriction

This task ONLY includes

- Sidebar integration
- Top navigation integration
- Breadcrumb integration
- HRMS dashboard page
- Layout consistency
- Active menu highlighting

Do NOT implement

- CRUD
- Business Logic
- APIs
- Controllers
- Services
- Models
- Migrations
- Form Requests

---

# LAYOUT STRUCTURE

Use existing layout files.

Do NOT create new master layouts.

Use

```
resources/views/Adminpanel/layout/
```

Reuse existing

- mainlayout.blade.php
- sidebar.blade.php
- navbar.blade.php
- footer.blade.php

---

# HRMS MENU

Create one parent menu.

```
HRMS
```

Under HRMS create the following menu items.

```
Dashboard

Employees

Attendance

Leave Management
    ├── Leave Types (Existing)
    ├── Holidays (Existing)
    ├── Leave Apply

Payroll

Roles & Permissions

Company Settings
```

Do NOT duplicate

Leave Type

Holiday

They already exist.

Simply move/place them under HRMS → Leave Management.

---

# SIDEBAR RULES

Sidebar must

- Collapse correctly
- Expand correctly
- Highlight active menu
- Preserve existing styling

Do NOT change

- Existing CSS
- Existing JS
- Existing Admin Template

---

# ACTIVE MENU RULE

Every HRMS page must correctly highlight.

Example

```
Employees

↓

Employee List

↓

Employee Edit

↓

Employee View

↓

Employee Create
```

All must keep

Employees

highlighted.

Same applies for

- Attendance
- Leave
- Payroll
- Roles
- Company Settings

---

# BREADCRUMB

Every HRMS page must display

Example

```
Dashboard

↓

HRMS

↓

Employees

↓

Employee List
```

Another example

```
Dashboard

↓

HRMS

↓

Attendance
```

Breadcrumb should use existing layout components.

---

# HRMS DASHBOARD PAGE

Create

```
resources/views/Adminpanel/HRMS/dashboard.blade.php
```

The page is currently a placeholder.

Do NOT implement statistics.

Display

- Welcome message
- Quick navigation cards
- Module shortcuts

Cards

- Employees
- Attendance
- Leave Management
- Payroll
- Roles
- Company Settings

Each card should navigate to its module.

No charts.

No graphs.

No business data.

---

# QUICK ACTION CARDS

Create clickable cards.

Example

```
Employees

↓

Employee List
```

```
Attendance

↓

Attendance List
```

```
Leave

↓

Leave Apply
```

```
Payroll

↓

Salary List
```

```
Roles

↓

Role List
```

```
Company Settings

↓

Settings
```

---

# ROUTE USAGE

Use existing named routes.

Never hardcode URLs.

Example

Correct

```blade
route('hrms.users.index')
```

Incorrect

```blade
/employee/list
```

---

# ICON RULE

Use existing Font Awesome icons already used in the project.

Suggested icons

Dashboard

```
fas fa-home
```

Employees

```
fas fa-users
```

Attendance

```
fas fa-calendar-check
```

Leave

```
fas fa-calendar-minus
```

Payroll

```
fas fa-money-check-alt
```

Roles

```
fas fa-user-shield
```

Company Settings

```
fas fa-cogs
```

Do not introduce another icon library.

---

# VIEW STRUCTURE

Use

```
resources/views/Adminpanel/HRMS/
```

Example

```
HRMS/

dashboard.blade.php

Employees/

Attendance/

Leave/

Payroll/

Roles/

CompanySetting/
```

Do NOT duplicate existing

```
Leaves/

Holiday/

LeaveType/
```

Reuse them.

---

# RESPONSIVE DESIGN

Pages must work on

- Desktop
- Tablet
- Mobile

Reuse Bootstrap classes already available.

---

# FLASH MESSAGE SUPPORT

Ensure layout supports

```
success

error

warning

info
```

Reuse existing alert component if available.

Do not create duplicate alert system.

---

# VALIDATION ERROR DISPLAY

Layout should correctly display

```
$formErrors
```

using Laravel standard error handling.

---

# PAGE TITLE

Every HRMS page should define

```blade
@section('title')
```

or the project's existing title mechanism.

Example

```
Employees

Attendance

Leave Apply

Payroll
```

---

# CODE QUALITY

Follow

- Laravel Blade best practices
- DRY
- Reusable partials
- PSR standards

Avoid duplicate HTML.

Extract common pieces into partials if needed.

---

# VERIFICATION

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
docker compose exec app php artisan about
```

Verify

- Sidebar loads correctly
- All HRMS menus appear
- Active menu works
- Breadcrumb works
- Dashboard page loads
- Existing Holiday module works
- Existing Leave Type module works
- No broken links
- No Blade errors

---

# PROGRESS UPDATE

Update

```
progress.md
```

Include

- Files created
- Files modified
- Sidebar integration completed
- Breadcrumb integration completed
- Dashboard placeholder created
- Verification completed

---

# SUCCESS CRITERIA

Task is complete when

- HRMS menu is integrated into the existing sidebar.
- Leave Type and Holiday remain fully functional.
- Active menu highlighting works correctly.
- Breadcrumb navigation is available on HRMS pages.
- HRMS dashboard placeholder page is created.
- Quick navigation cards are available.
- Existing application layout remains unchanged.
- No duplicate layouts are created.
- Existing modules continue working without modification.
- All verification commands pass successfully.
- The application is ready for HRMS CRUD Blade pages.