# 10E8_hrms_employee_reporting_hierarchy.md

# Module

HRMS → Employee Management → Reporting Hierarchy

---

# Objective

Implement a complete **Reporting Hierarchy Management** module that allows HR/Admin to assign Reporting Managers to employees.

This hierarchy will be reused by:

- Leave Approval Workflow
- Attendance Approval (future)
- Payroll Approval (future)
- Appraisal Module (future)
- Claims & Reimbursement (future)
- Organization Chart (future)

This module becomes the single source of truth for employee reporting relationships.

---

# Laravel Version

Laravel 10 only.

---

# Existing Architecture

Reuse existing

- Employee Module
- User Module
- Department Module
- Designation Module
- Leave Approval Workflow
- Existing Authentication
- Existing Authorization
- Existing Sidebar
- Existing Flash Messages
- Existing Audit Log

Do NOT duplicate existing services.

---

# Database

If not already available, add

```
reporting_manager_id
```

to the employee master table (or users table, depending on project architecture).

Foreign Key

```
Employee
↓

Reporting Manager

(Self Relationship)
```

Example

```
Ajay Kumar

↓

Rahul Sharma
```

where

```
Ajay.reporting_manager_id = Rahul.id
```

---

# Employee Form

Update

Employee Create

Employee Edit

Add field

```
Reporting Manager
```

Type

Dropdown

Required

No

(Default: None)

---

# Dropdown Rules

Show only

- Active Employees
- Managers
- HR
- Admin

Do NOT show

- Inactive Employees

Do NOT show

Current Employee

Example

```
Select Reporting Manager

▼ Rahul Sharma

▼ Amit Verma

▼ Priya Gupta
```

---

# Validation

Prevent

Employee reporting to themselves

```
Ajay

↓

Ajay
```

Invalid

---

Prevent Circular Reporting

Example

```
A

↓

B

↓

C

↓

A
```

Must be rejected.

Display

```
Circular reporting hierarchy is not allowed.
```

---

# Employee Details Page

Display

```
Reporting Manager

Rahul Sharma

Employee Code

EMP0004

Department

IT
```

Clickable link to manager profile if available.

---

# Employee List

Add optional column

```
Reporting Manager
```

Example

| Employee | Reporting Manager |
|-----------|-------------------|
| Ajay | Rahul Sharma |
| Neha | Priya Gupta |

Support sorting.

---

# Search

Allow search by

- Employee
- Employee Code
- Reporting Manager
- Department
- Designation

---

# Filters

Support

```
Department

Designation

Reporting Manager

Status
```

---

# Profile Card

Show

```
Reporting Manager

Rahul Sharma
```

in Employee Profile.

---

# Leave Integration

Reuse

```
reporting_manager_id
```

for

Leave Approval Workflow.

Do NOT ask HR to manually assign approver.

Workflow

```
Employee

↓

Reporting Manager

↓

HR

↓

Admin
```

Manager stage should automatically skip if Company Settings disable manager approval.

---

# Attendance Integration

Future ready only.

No attendance approval logic required.

Just ensure

Attendance module can later reuse

```
reporting_manager_id
```

---

# Payroll Integration

Future ready only.

Payroll approval should be able to reuse the same hierarchy.

---

# Dashboard

Add widget

```
Employees Without Reporting Manager
```

Example

```
8 Employees
```

Clickable list.

Visible

HR/Admin only.

---

# Reports

Create

Reporting Hierarchy Report

Display

```
Employee

Department

Designation

Reporting Manager

Status
```

Buttons

```
Excel

PDF

Print
```

UI only.

---

# Organization Preview

Simple hierarchy preview.

Example

```
CEO

↓

HR Manager

↓

IT Manager

↓

Developer

↓

Junior Developer
```

Tree implementation is NOT required.

Only parent relationship.

---

# Authorization

Employee

View own Reporting Manager only.

Cannot edit.

Manager

View own team.

Cannot edit.

HR

Full access.

Admin

Full access.

Unauthorized

↓

403

---

# Audit Log

Track

```
Reporting Manager Assigned

Reporting Manager Changed

Reporting Manager Removed
```

Store

```
Employee

Old Manager

New Manager

Changed By

Changed At

IP Address
```

Reuse existing audit log.

---

# Notifications

Notify Employee

```
Your Reporting Manager has been updated.

Reporting Manager

Rahul Sharma
```

Notify Manager

```
A new employee now reports to you.

Employee

Ajay Kumar
```

Reuse Notification Engine.

---

# Performance

Use eager loading.

Avoid N+1 queries.

No database queries inside Blade.

---

# Blade Rules

Blade must NOT

- Resolve reporting hierarchy
- Query manager
- Calculate permissions

Blade only displays prepared data.

---

# Responsive UI

Desktop

Bootstrap tables.

Tablet

Scrollable tables.

Mobile

Responsive cards.

---

# Breadcrumb

Display

```
Dashboard

↓

HRMS

↓

Employees

↓

Reporting Hierarchy
```

---

# Flash Messages

Reuse existing flash component.

Messages

```
Reporting Manager Assigned Successfully.

Reporting Manager Updated Successfully.

Reporting Manager Removed Successfully.

Circular hierarchy detected.

Employee cannot report to themselves.
```

---

# Browser Verification

Verify

✓ Create Employee

✓ Edit Employee

✓ Assign Reporting Manager

✓ Remove Reporting Manager

✓ Employee Profile

✓ Employee List

✓ Dashboard Widget

✓ Search

✓ Filters

✓ Notification

✓ Audit Log

✓ Leave Approval Integration

✓ Responsive Layout

✓ No Undefined Variables

---

# Regression Verification

Ensure existing modules continue working

✓ Employee CRUD

✓ Leave Approval Workflow

✓ Attendance Module

✓ Notifications

✓ Reports

✓ Dashboard

✓ Authentication

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

- Reporting hierarchy implemented
- Reporting Manager assignment completed
- Employee profile updated
- Employee list updated
- Dashboard widget completed
- Leave workflow integrated
- Notifications completed
- Audit log completed
- Browser verification completed
- Laravel verification commands passed

---

# Success Criteria

Task is complete when

- HR/Admin can assign a Reporting Manager.
- Employees cannot assign themselves.
- Circular reporting is prevented.
- Reporting Manager is visible in Employee Profile.
- Employee List displays Reporting Manager.
- Leave Approval automatically uses the assigned Reporting Manager.
- Notifications are sent after assignment.
- Audit logs are created.
- Dashboard shows employees without managers.
- No business logic exists in Blade.
- Existing architecture is reused.
- Manual browser verification passes.
- All Laravel verification commands pass successfully.

---

# Future Usage

This hierarchy will be reused by:

- Leave Approval ✅
- Attendance Approval (future)
- Payroll Approval (future)
- Performance Appraisal (future)
- Claims & Reimbursement (future)
- Organization Chart (future)