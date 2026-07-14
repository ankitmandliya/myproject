# 10G1_hrms_payroll_master_and_salary_structure.md

# Module

HRMS → Payroll → Payroll Master & Salary Structure

---

# Objective

Build the complete **Payroll Foundation Module**.

This module creates the salary structure architecture that will be consumed by Payroll Calculation (10G2).

This phase DOES NOT calculate salary.

This phase ONLY manages

- Salary Components
- Salary Templates
- Employee Salary Structure
- Salary Revision
- CTC
- Gross Salary
- Component Formulas
- Validation

---

# Laravel Version

Laravel 10 only.

---

# Existing Architecture

Reuse

- Employee Module
- Company Settings
- Department Module
- Designation Module
- Financial Year Module
- Existing Authentication
- Existing Sidebar
- Existing Layout
- Existing Flash Messages

Do NOT duplicate existing architecture.

---

# Scope

This phase includes

- Payroll Masters
- Salary Components
- Salary Structure Templates
- Employee Salary Assignment
- Salary Revision
- Payroll Dashboard
- Validation
- Reports (Basic)

No salary generation.

No payslip.

No payroll processing.

---

# Payroll Menu

Add Sidebar

```
HRMS

↓

Payroll

    Payroll Dashboard

    Salary Components

    Salary Structures

    Employee Salary

    Salary Revisions
```

Visible only

HR

Admin

Finance

Hidden for Employees.

---

# Database

Create

```
salary_components

salary_structures

salary_structure_items

employee_salary_structures

salary_revisions
```

Do NOT modify attendance tables.

Do NOT modify leave tables.

---

# Salary Components

Create Master

Fields

```
Component Name

Component Code

Type

(Earning / Deduction)

Calculation Type

Fixed

Percentage

Formula

Display Order

Taxable

PF Applicable

ESI Applicable

Professional Tax Applicable

Active
```

Examples

```
Basic

HRA

Conveyance

Medical

Special Allowance

Bonus

Performance Bonus

Leave Encashment

Overtime

Arrear

PF

ESI

Professional Tax

Income Tax

Loan Recovery

Advance Recovery

Other Deduction
```

CRUD required.

---

# Salary Structure

Create

```
Structure Name

Structure Code

Department

Designation

Description

Effective From

Status
```

Example

```
Software Engineer

Senior Developer

HR Executive

Sales Executive

Manager

Accountant
```

---

# Salary Structure Items

Each structure contains

```
Component

Calculation Type

Value

Formula

Display Order

```

Examples

```
Basic

40%

HRA

20%

Medical

1250

Conveyance

1600

Special Allowance

Balance

PF

12%

```

Support

```
Fixed

Percentage

Formula (future-ready)
```

No formula execution yet.

---

# Employee Salary Assignment

Each employee can have

```
Salary Structure

CTC

Gross Salary

Effective From

Status

Remarks
```

Only one active structure at a time.

Previous structures remain archived.

---

# Salary Revision

Create

```
Revision Date

Previous CTC

New CTC

Reason

Approved By

Remarks
```

History must remain immutable.

---

# Payroll Dashboard

Create

```
resources/views/Adminpanel/HRMS/Payroll/
```

Dashboard cards

```
Employees

Salary Structures

Active Structures

Salary Revisions

Pending Assignments

Current FY
```

---

# Salary Component List

Display

```
Code

Name

Type

Calculation

PF

ESI

PT

Status

Action
```

Actions

```
View

Edit

Delete
```

Delete only if unused.

---

# Salary Structure List

Display

```
Structure

Department

Designation

Components

Employees Assigned

Status

Action
```

---

# Employee Salary List

Display

```
Photo

Employee

Department

Designation

Structure

CTC

Gross

Effective Date

Status

Action
```

---

# Salary Revision History

Display

```
Employee

Old CTC

New CTC

Increment %

Revision Date

Approved By

Remarks
```

---

# Salary Component Form

Fields

```
Name

Code

Type

Calculation

Value

Taxable

PF

ESI

PT

Status
```

Validation

Required

---

# Salary Structure Wizard

Steps

```
Basic Information

↓

Components

↓

Review

↓

Save
```

Reuse existing wizard style from Leave module.

---

# Employee Salary Wizard

Steps

```
Employee

↓

Structure

↓

CTC

↓

Review

↓

Assign
```

---

# Validation

Prevent

Duplicate component codes

Duplicate structure codes

Multiple active salary structures

Negative salary values

Zero CTC

Inactive employee assignment

Past invalid effective dates

---

# Search

Support

```
Employee

Department

Designation

Structure

Component
```

Server-side.

---

# Filters

Support

```
Department

Designation

Status

Structure

Effective Date
```

---

# Pagination

Support

```
10

25

50

100
```

Reuse Laravel pagination.

---

# Employee Profile

Add

```
Salary Information
```

Section

Display

```
Current Structure

Current CTC

Gross

Effective Date

Revision History
```

---

# Dashboard Widgets

Display

```
Recent Salary Revisions

Recently Assigned Structures

Upcoming Revisions
```

---

# Authorization

Employee

No access.

HR

Full access.

Finance

Full access.

Admin

Full access.

Unauthorized

↓

403

---

# Reports

Basic reports only

```
Salary Structure Report

Employee Salary Report

Revision Report
```

Export buttons

```
Excel

PDF

Print
```

UI only.

---

# Audit Log

Track

```
Created

Updated

Assigned

Revised

Deleted
```

Store

```
User

Time

IP

Action
```

Reuse existing audit architecture.

---

# Notifications

Notify employee

```
Salary Structure Assigned
```

Notify HR

```
Revision Pending
```

Reuse Notification Engine.

---

# Performance

Eager load relationships.

Avoid N+1.

No DB queries inside Blade.

---

# Blade Rules

Blade must NOT

Calculate salary

Calculate CTC

Execute formulas

Perform database queries

Blade only renders data.

---

# Responsive UI

Desktop

Responsive Bootstrap tables.

Tablet

Scrollable.

Mobile

Bootstrap cards.

---

# Breadcrumb

Display

```
Dashboard

↓

HRMS

↓

Payroll

↓

Current Page
```

---

# Flash Messages

Reuse existing flash component.

---

# Browser Verification

Verify manually

✓ Payroll Dashboard

✓ Salary Components CRUD

✓ Salary Structure CRUD

✓ Salary Assignment

✓ Salary Revision

✓ Employee Salary

✓ Dashboard Widgets

✓ Search

✓ Filters

✓ Pagination

✓ Employee Profile

✓ Responsive Layout

✓ Flash Messages

✓ Breadcrumb

✓ No Undefined Variables

---

# Required Verification

Run

```bash
docker compose exec app php artisan migrate
```

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

# Progress Update

Update

```
progress.md
```

Include

- Payroll dashboard completed
- Salary Components completed
- Salary Structures completed
- Salary Component mapping completed
- Employee Salary Assignment completed
- Salary Revision completed
- Dashboard widgets completed
- Reports completed
- Notifications completed
- Audit logs completed
- Browser verification completed
- Verification commands passed

---

# Success Criteria

Task is complete when

- HR/Admin/Finance can manage Salary Components.
- Salary Structures can be created and reused.
- Employees can be assigned exactly one active Salary Structure.
- Salary Revision history is maintained.
- Dashboard displays payroll summary.
- Employee profile displays salary information.
- Basic reports are available.
- Notifications integrate with the existing notification engine.
- Audit logs are recorded.
- No payroll calculations are performed yet.
- No business logic exists in Blade.
- Existing architecture is reused.
- Manual browser verification passes.
- All Laravel verification commands pass successfully.

---

# Next Module

After successful completion of **10G1**, proceed to:

**10G2_hrms_payroll_calculation_engine.md**

This phase will consume:

- Attendance
- Approved Leave
- LWP
- Holidays
- Salary Structure
- Company Settings
- Financial Year

to calculate monthly payroll.
