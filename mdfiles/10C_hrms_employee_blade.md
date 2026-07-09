# HRMS Employee Blade Integration Task (Laravel 10)

## Objective

This phase implements the complete Employee Management UI for the HRMS module.

The Employee module is responsible for managing employees by integrating the existing

- User
- UserDetail
- UserRole

backend layers into a single unified UI.

The UI must consume the existing Controllers and Services only.

No business logic should be added in this phase.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

The following are already completed.

- Models
- Seeders
- Services
- Controllers
- Form Requests
- Routes
- Business Logic
- Dashboard Integration

Reuse the existing implementation.

Do NOT duplicate any logic.

---

## 3. Scope Restriction

This phase ONLY includes

- Blade Views
- Blade Partials
- Form Layout
- Bootstrap UI
- Existing Controller Integration
- Existing Route Integration

Do NOT implement

- Business Logic
- Database Queries
- Models
- Services
- Controllers
- Routes
- Validation Rules
- Migrations
- APIs

---

# EXISTING UI REUSE (MANDATORY)

Reuse the existing Admin Panel UI.

Reuse existing

- Layout
- Navbar
- Sidebar
- Footer
- Cards
- Tables
- Bootstrap classes
- Font Awesome icons
- Alerts
- Flash Messages
- Breadcrumb

Do NOT

- Create new layouts
- Create custom CSS
- Create custom JavaScript libraries
- Introduce Tailwind
- Introduce Bootstrap upgrades

Maintain the same Admin Panel design.

---

# EXISTING FUNCTIONALITY PROTECTION

Do NOT modify or break

- Authentication
- Dashboard
- Holiday Module
- Leave Type Module
- Existing Controllers
- Existing Services
- Existing Routes
- Existing Layout
- Existing Blade Components

Only extend the application.

---

# MODULE STRUCTURE

Create views inside

```
resources/views/Adminpanel/HRMS/Employees/
```

Create

```
index.blade.php

create.blade.php

edit.blade.php

show.blade.php

_partials/
    form.blade.php
```

---

# EMPLOYEE DATA MODEL

Employee UI represents

```
users
        +

user_details
        +

user_role
```

Users should never feel they are editing multiple tables.

The UI should present a single Employee Management interface.

---

# EMPLOYEE LIST PAGE

Display

- Profile Photo
- Employee Code
- Full Name
- Email
- Department
- Designation
- Role
- Status
- Joining Date
- Actions

Do NOT display

- Aadhaar
- PAN
- Salary
- Address

on listing page.

---

# SEARCH & FILTERS

Provide filters

- Employee Name
- Employee Code
- Department
- Designation
- Role
- Status

Retain filter values after search.

---

# PAGINATION

Support

- 10
- 25
- 50
- 100

Reuse Laravel pagination.

---

# TOP ACTION BUTTONS

Display

- Add Employee
- Export Excel

Do NOT implement Import in this phase.

---

# ROW ACTIONS

Each row should contain

- View
- Edit
- Activate / Deactivate

Do NOT provide permanent Delete.

Employees should remain in the system.

---

# EMPTY STATE

If no employee exists

Display

```
No Employees Found
```

with

Add Employee button.

---

# CREATE EMPLOYEE PAGE

Divide the form into sections.

---

## SECTION 1

### Account Information

Fields

- Employee Code
- First Name
- Last Name
- Email
- Password
- Confirm Password

---

## SECTION 2

### Personal Information

Fields

- Gender
- Date of Birth
- Mobile Number (if available)
- Address
- Profile Photo

---

## SECTION 3

### Employment Information

Fields

- Joining Date
- Department
- Designation
- Basic Salary
- Role
- Status

---

## SECTION 4

### Government Details

Fields

- Aadhaar
- PAN

---

# EDIT EMPLOYEE PAGE

Reuse the Create Employee form.

Do NOT display

- Password
- Confirm Password

unless a dedicated Reset Password feature exists.

---

# VIEW EMPLOYEE PAGE

Display employee profile.

Sections

---

## Basic Information

- Profile Photo
- Employee Code
- Name
- Email
- Role
- Status

---

## Personal Information

- Gender
- Date of Birth
- Address
- Mobile

---

## Employment Information

- Department
- Designation
- Joining Date
- Basic Salary

---

## Government Details

- Aadhaar
- PAN

---

# SUMMARY CARDS

Display read-only summary cards

- Attendance Summary
- Leave Summary
- Salary Summary

Use existing Services through Controller.

No calculations inside Blade.

---

# PROFILE PHOTO

If profile photo is unavailable

Display

- Default Avatar

---

# STATUS BADGE

Display

Active

Inactive

using existing Bootstrap badge styles.

---

# FORM PARTIAL

Move all repeated form fields into

```
_partials/form.blade.php
```

Reuse this partial for

- Create
- Edit

Do NOT duplicate HTML.

---

# VALIDATION DISPLAY

Display validation errors using existing

- Flash component
- Error component

Reuse existing layout partials.

---

# FLASH MESSAGE

Display

- Success
- Error
- Warning
- Validation Errors

using existing flash partial.

---

# BREADCRUMB

Display

Dashboard

↓

HRMS

↓

Employees

↓

Current Page

Reuse existing breadcrumb partial.

---

# RESPONSIVE DESIGN

Desktop

Responsive table.

Tablet

Scrollable table.

Mobile

Bootstrap responsive cards/table.

Do NOT create separate mobile pages.

---

# BLADE RESPONSIBILITY

Blade MUST NOT

- Query database
- Instantiate Models
- Call Services
- Execute calculations
- Apply business rules

Blade ONLY

- Display variables
- Render forms
- Render tables
- Render badges
- Render pagination
- Display validation
- Display flash messages

---

# PERFORMANCE

Reuse eager-loaded data from Controller.

Do NOT trigger additional queries.

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- PSR-12
- DRY
- Component Reuse
- Thin Controller
- Fat Service

---

# ROUTES

Use ONLY existing named routes.

Example

```
hrms.users.index

hrms.users.create

hrms.users.store

hrms.users.show

hrms.users.edit

hrms.users.update
```

Do NOT create new routes.

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

Verify

- Employee List loads
- Create page loads
- Edit page loads
- View page loads
- Breadcrumb works
- Sidebar active state works
- Flash messages work
- Validation messages display
- Search works
- Pagination works
- Empty state works
- Responsive layout works
- No undefined variables
- No Blade errors
- Existing application remains unaffected

---

# PROGRESS UPDATE

Update

```
progress.md
```

Include

- Files created
- Files modified
- Employee List completed
- Create Employee page completed
- Edit Employee page completed
- View Employee page completed
- Shared form partial created
- Search integrated
- Pagination integrated
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Employee module UI is fully functional.
- Existing Admin Panel design is preserved.
- Existing Controllers and Services are reused.
- No business logic exists inside Blade.
- Shared form partial is used.
- Employee List displays correctly.
- Create Employee page works.
- Edit Employee page works.
- View Employee page works.
- Search and filters work.
- Pagination works.
- Flash messages work.
- Validation messages work.
- Empty state is handled gracefully.
- Existing modules continue working.
- Application remains browser-testable.
- All verification commands pass successfully.