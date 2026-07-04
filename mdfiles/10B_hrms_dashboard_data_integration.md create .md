# HRMS Dashboard Data Integration Task (Laravel 10)

## Objective

This phase integrates the HRMS Dashboard with the completed backend Business Layer.

The dashboard layout has already been created during **10A_hrms_layout_integration.md**.

This task is responsible for displaying live HRMS data by consuming the existing DashboardService and related services.

No new business logic should be implemented in this phase.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

The following layers are already completed.

- Models
- Seeders
- Services
- Controllers
- Form Requests
- Routes
- DashboardService
- DashboardServiceInterface
- Dashboard Business Logic
- Layout Integration

Reuse them.

Do NOT duplicate any implementation.

---

## 3. Scope Restriction

This phase ONLY includes

- DashboardController integration
- Dashboard Blade integration
- DashboardService consumption
- Dashboard widgets
- Summary cards
- Dashboard charts data preparation
- Recent activities
- Upcoming holidays

Do NOT implement

- Business logic
- CRUD
- APIs
- Models
- Migrations
- Seeders
- Events
- Notifications
- Queues
- Jobs

---

# EXISTING DASHBOARD DETECTION (MANDATORY)

Before implementing the HRMS Dashboard, inspect the existing Admin Panel Dashboard.

If an existing DashboardController, dashboard Blade, layout, widgets, cards, charts, alerts, or reusable Blade partials already exist, they MUST be reused.

Do NOT redesign or recreate the existing dashboard.

Only extend the existing dashboard by integrating HRMS widgets.

Preserve the existing

- Layout
- Header
- Footer
- Sidebar
- Navbar
- Cards
- Grid system
- Responsive behavior
- CSS classes
- JavaScript
- Blade structure

The HRMS Dashboard must appear as a natural extension of the existing Admin Panel.

---

# EXISTING UI REUSE (MANDATORY)

Reuse all existing

- CSS
- JavaScript
- Bootstrap classes
- Font Awesome icons
- Cards
- Tables
- Widgets
- Alerts
- Responsive utilities

Do NOT

- Introduce new CSS
- Introduce new JS libraries
- Introduce new chart libraries
- Introduce new layouts

Maintain the existing Admin Panel design.

---

# EXISTING FUNCTIONALITY PROTECTION

Do NOT modify or break

- Existing Dashboard
- Authentication
- Sidebar
- Navbar
- Leave Type Module
- Holiday Module
- Existing Routes
- Existing Controllers
- Existing Services
- Existing Layout
- Existing Blade Components

Only extend the existing implementation.

Never replace working functionality.

---

# ROUTE MANAGEMENT (MANDATORY)

Before modifying `routes/web.php`, inspect the existing routes.

Keep all existing application routes unchanged.

For HRMS routes

- Keep enabled only completed modules.
- Temporarily comment routes for incomplete modules.
- Never delete routes.
- Never comment existing Holiday routes.
- Never comment existing Leave Type routes.
- Uncomment routes when corresponding modules are completed.

The application must remain browser-testable after every phase.

---

# DASHBOARD DATA FLOW

Dashboard must strictly follow

```
Blade

↓

DashboardController

↓

DashboardServiceInterface

↓

DashboardService

↓

Other Services

↓

Models
```

DashboardController must NEVER access Models directly.

---

# DASHBOARD SERVICE RULE (MANDATORY)

DashboardService is the ONLY service responsible for aggregating dashboard information.

DashboardController must NEVER directly consume

- UserService
- AttendanceService
- LeaveService
- SalaryService
- CompanySettingService

DashboardController communicates ONLY with

```
DashboardServiceInterface
```

All calculations remain inside DashboardService.

---

# CONTROLLER RESPONSIBILITY (MANDATORY)

DashboardController must remain a Thin Controller.

DashboardController MUST

- Receive HTTP request
- Call DashboardServiceInterface
- Pass data to Blade
- Return View response

DashboardController MUST NOT

- Query Models
- Execute database queries
- Calculate dashboard statistics
- Aggregate data
- Apply business rules
- Access CompanySetting directly

All business logic belongs inside DashboardService.

---

# BLADE RESPONSIBILITY (MANDATORY)

Dashboard Blade is responsible ONLY for presentation.

Dashboard Blade MUST NOT

- Query database
- Call services
- Instantiate models
- Perform calculations
- Execute business rules

Dashboard Blade MAY ONLY

- Display variables
- Loop through collections
- Render widgets
- Render tables
- Display empty states
- Display flash messages
- Display validation errors

---

# DASHBOARD WIDGETS

Display summary cards.

---

## Employee Summary

Show

- Total Employees
- Active Employees
- Inactive Employees

---

## Attendance Summary

Show

- Present Today
- Absent Today
- Late Today
- Half Day Today

---

## Leave Summary

Show

- Pending Leave
- Approved Leave
- Rejected Leave

Do NOT include Cancelled.

Current schema does not support it.

---

## Payroll Summary

Show

- Salary Generated
- Pending Salary
- Total Payroll

---

## Company Information

Display

- Office Start Time
- Office End Time
- Weekly Off
- Salary Date

Retrieve using CompanySettingService through DashboardService.

---

# RECENT ACTIVITIES

Display latest

- Leave Applications
- Attendance Records
- Salary Generation
- Newly Added Employees

Maximum

```
10 records
```

Use DashboardService only.

---

# UPCOMING HOLIDAYS

Display

- Holiday Name
- Holiday Date

Maximum

```
5 records
```

Sorted by nearest upcoming date.

---

# DASHBOARD CHART DATA

Prepare arrays only.

No JavaScript chart implementation.

Example

Attendance

```php
[
    'Present' => 50,
    'Absent' => 4,
    'Leave' => 3
]
```

Monthly Leave

```php
[
    'Jan' => 12,
    'Feb' => 9,
    'Mar' => 7
]
```

Payroll

```php
[
    'Jan' => 250000,
    'Feb' => 280000
]
```

Pass arrays to Blade.

---

# QUICK ACTION CARDS

Dashboard must contain navigation cards.

- Employees
- Attendance
- Leave Apply
- Payroll
- Roles
- Company Settings

Reuse existing cards created during 10A.

Do NOT duplicate.

---

# OPTIONAL DATA HANDLING

Every widget must gracefully handle missing data.

If no data exists

Display

```
No Data Available
```

Do NOT

- Throw exceptions
- Show undefined variables
- Break dashboard rendering

The dashboard must continue rendering.

---

# PERFORMANCE RULES

Dashboard should

- Use eager loading
- Avoid N+1 queries
- Reuse DashboardService aggregation
- Avoid duplicate queries
- Avoid repeated database hits

Dashboard Blade must never access database.

---

# RESPONSIVE DESIGN

Dashboard must support

- Desktop
- Tablet
- Mobile

Reuse Bootstrap classes already available.

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- PSR-12
- DRY
- Thin Controller
- Fat Service
- Clean Architecture

---

# BROWSER VERIFICATION

Verify

- Dashboard opens successfully
- Existing Dashboard styling remains unchanged
- Sidebar works
- Navbar works
- Breadcrumb works
- Flash messages work
- Quick Action cards work
- Every completed HRMS module opens correctly
- No broken links
- No View Not Found errors
- No Undefined Variable errors
- No 404 errors
- Responsive layout remains unchanged

Application must remain browser-testable.

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

- Dashboard loads
- Summary cards display
- Recent activities display
- Upcoming holidays display
- Empty state works
- No Blade errors
- No undefined variables
- Controller contains no business logic
- DashboardService performs aggregation

---

# PROGRESS UPDATE

Update

```
progress.md
```

Include

- Files modified
- DashboardController integrated
- DashboardService connected
- Dashboard widgets completed
- Summary cards completed
- Recent activities completed
- Upcoming holidays completed
- Charts data prepared
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Dashboard displays live HRMS data.
- Existing Dashboard is reused.
- Existing UI remains unchanged.
- DashboardController uses DashboardServiceInterface only.
- DashboardService performs all aggregation.
- Dashboard Blade contains presentation logic only.
- Summary cards display correctly.
- Recent activities display correctly.
- Upcoming holidays display correctly.
- Empty state works gracefully.
- No duplicate business logic exists.
- Existing modules continue working.
- Application remains browser-testable.
- All verification commands pass successfully.
- Dashboard is fully integrated with the HRMS backend.