# 10F9_hrms_leave_reports_and_analytics.md

# Module

HRMS → Leave Management → Reports & Analytics

---

# Objective

Implement a complete **Leave Reports & Analytics** module for HR/Admin using the existing Leave, Attendance, Employee, Company Settings, Holiday, and Approval systems.

This module is **reporting only**.

It must **reuse** the existing Leave Management architecture.

No leave calculation logic should be recreated.

---

# Laravel Version

Laravel 10 only.

Do NOT use Laravel 11/12 features.

---

# Existing Architecture

Reuse existing

- LeaveService
- LeavePolicyService
- LeaveApprovalService
- AttendanceService
- Employee Service
- Company Settings
- Holiday Module
- Existing Controllers
- Existing Models
- Existing Dashboard
- Existing Layout
- Existing Bootstrap Components
- Existing Authentication
- Existing Sidebar
- Existing Flash Messages

Do NOT duplicate business logic.

---

# Scope

This phase ONLY includes

- Reports
- Analytics
- Charts
- Summary Cards
- Filters
- Export Buttons (UI only)
- Print Layout
- Dashboard Widgets

Do NOT modify

- Leave calculations
- Attendance calculations
- Leave approval engine
- Notification engine
- Balance engine

---

# Folder Structure

Create

```
resources/views/Adminpanel/HRMS/LeaveReports/

index.blade.php

employee-report.blade.php

department-report.blade.php

leave-type-report.blade.php

balance-report.blade.php

liability-report.blade.php

monthly-report.blade.php

financial-year-report.blade.php

approval-report.blade.php

lwp-report.blade.php

sandwich-report.blade.php

_partials/

summary-cards.blade.php

filters.blade.php

charts.blade.php

tables.blade.php
```

---

# Sidebar

Add

```
HRMS

↓

Leave Reports
```

Visible only to

- HR
- Admin

Not visible to Employees.

Reuse sidebar style.

---

# Dashboard

Create

```
Leave Reports Dashboard
```

Display summary cards

```
Total Employees

Total Leave Requests

Approved

Pending

Rejected

Cancelled

Revoked

Total Leave Days

LWP Days

Remaining Leave Balance

Current Financial Year
```

No calculations in Blade.

---

# Charts

Use Chart.js.

Display

## Monthly Leave Trend

```
Jan

Feb

Mar

...

Dec
```

---

## Leave Type Distribution

Pie Chart

```
CL

SL

EL

LWP

ML

PL
```

---

## Department Wise Leave

Bar Chart

```
HR

Sales

Accounts

IT

Support

Admin
```

---

## Monthly Approval Trend

Approved

Rejected

Cancelled

Pending

---

# Reports

---

# Employee Leave Report

Columns

```
Photo

Employee Code

Employee Name

Department

Designation

Allocated

Consumed

Remaining

Pending

LWP

Sandwich

```

Action

```
View Details
```

---

# Employee Details

Display

```
Profile

Leave Balance

Leave History

Approval History

Attendance Summary

Holiday Summary

Sandwich Leave Count

LWP Count

```

---

# Department Report

Display

```
Department

Employees

Applied

Approved

Rejected

Pending

LWP

Sandwich

Average Leave

```

---

# Leave Type Report

Display

```
Leave Type

Allocated

Consumed

Remaining

Pending

Rejected

Average Usage

```

---

# Monthly Report

Display

```
Month

Requests

Approved

Rejected

Cancelled

LWP

Leave Days

```

---

# Financial Year Report

Display

```
Financial Year

Allocated

Consumed

Remaining

Carry Forward

LWP

Employees Covered

```

---

# Leave Balance Report

Display

```
Employee

CL

SL

EL

LWP

Consumed

Remaining

Carry Forward

```

Highlight

Low balance

---

# Leave Liability Report

Display

```
Employee

Earned Leave Remaining

Estimated Liability

Carry Forward

```

Reuse current EL balance.

Do NOT calculate salary.

Placeholder for Payroll integration.

---

# LWP Report

Display

```
Employee

Department

LWP Days

Reason

Approved By

```

---

# Sandwich Leave Report

Display

```
Employee

Leave Type

Holiday Days

Weekly Off

Sandwich Days

Payable Days

```

---

# Approval Performance Report

Display

```
Approver

Role

Approved

Rejected

Pending

Average Approval Time

```

---

# Cancellation Report

Display

```
Employee

Leave Type

Cancelled On

Cancelled By

Reason

```

---

# Rejection Report

Display

```
Employee

Leave Type

Rejected By

Rejected On

Reason

```

---

# Filters

Support

```
Financial Year

Month

Employee

Employee Code

Department

Designation

Leave Type

Status

Approval Stage

Approver

From Date

To Date
```

Retain filter values.

Reuse existing filter UI.

---

# Search

Support

```
Employee Name

Employee Code

Department

Leave Type
```

Server-side only.

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

# Export

Provide buttons only

```
Export Excel

Export PDF

Print
```

Do NOT implement export logic.

---

# Print Layout

Create

```
Printable report view
```

Bootstrap compatible.

---

# Empty State

Display

```
No Report Data Found
```

Reuse existing empty-state illustration.

---

# Employee Photos

Reuse employee upload path.

Missing image

↓

Default avatar.

---

# Drill Down

Allow navigation

Dashboard

↓

Employee Report

↓

Employee Details

Reuse existing routes.

---

# Dashboard Widget

HR Dashboard

Display

```
Top 5 Employees

Most Leave Taken

Lowest Leave Balance

Pending Approvals

Upcoming Leave

```

---

# Authorization

Employee

No access.

Manager

Department/reporting reports only.

HR

All reports.

Admin

All reports.

Unauthorized

```
403
```

---

# Performance

Reuse eager loading.

No queries inside Blade.

Avoid N+1.

Server-side pagination.

---

# Blade Rules

Blade MUST NOT

- Calculate balances
- Calculate reports
- Query database
- Execute business logic

Blade ONLY

- Render charts
- Render tables
- Render filters
- Render pagination

---

# Breadcrumb

Display

```
Dashboard

↓

HRMS

↓

Leave Reports

↓

Current Report
```

Reuse existing breadcrumb.

---

# Flash Messages

Reuse existing flash component.

---

# Responsive Design

Desktop

Full tables.

Tablet

Scrollable.

Mobile

Responsive Bootstrap cards.

No separate mobile Blade.

---

# Browser Verification

Verify manually

✓ Leave Reports dashboard

✓ Employee Report

✓ Department Report

✓ Leave Type Report

✓ Balance Report

✓ Liability Report

✓ Monthly Report

✓ Financial Year Report

✓ LWP Report

✓ Sandwich Report

✓ Approval Report

✓ Charts

✓ Filters

✓ Search

✓ Pagination

✓ Breadcrumb

✓ Flash Messages

✓ Employee Photos

✓ Empty States

✓ Mobile Layout

✓ No Undefined Variables

✓ No Broken Images

---

# Required Verification

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

- Leave Reports dashboard completed
- Employee report completed
- Department report completed
- Leave Type report completed
- Balance report completed
- Liability report completed
- Monthly report completed
- Financial Year report completed
- LWP report completed
- Sandwich report completed
- Approval report completed
- Charts integrated
- Filters integrated
- Pagination integrated
- Dashboard widget completed
- Browser verification completed
- Verification commands passed

---

# Success Criteria

Task is complete when

- HR/Admin can access Leave Reports.
- Dashboard displays correct summary cards.
- All reports render using existing services.
- Charts display report data.
- Filters work correctly.
- Pagination works.
- Employee drill-down works.
- Export buttons are available.
- Print layout is available.
- No business logic exists in Blade.
- Existing services/controllers are reused.
- Existing architecture remains unchanged.
- Browser verification passes.
- All Laravel verification commands pass successfully.

---

# Future Payroll Integration

This module must expose reusable reporting data for the upcoming Payroll module without duplicating any calculations. Payroll will consume:

- Approved Leave
- LWP Days
- Earned Leave Balance
- Attendance Summary
- Leave Liability
- Financial Year Leave Data

No Payroll logic should be implemented in this phase.