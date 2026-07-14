# 10F6A_hrms_leave_approval_frontend.md

## Module

HRMS → Leave Management

---

# Objective

Complete the **Frontend/UI** for the Leave Approval Workflow already implemented in **10F6**.

The approval business logic, services, controller methods, routes, notifications, audit logs, and approval engine already exist.

This task ONLY connects the frontend with the existing backend.

Do NOT duplicate business logic.

---

# Laravel Version

Use Laravel 10 only.

Do NOT use Laravel 11/12 features.

---

# Existing Architecture

Reuse existing

- LeaveApprovalService
- LeaveService
- LeavePolicyService
- LeaveApplyController
- Existing Models
- Existing Routes
- Existing Middleware
- Existing Bootstrap Layout
- Existing Flash Messages
- Existing Breadcrumb
- Existing Sidebar
- Existing Authentication

Do NOT create duplicate controllers or services.

---

# Scope

This task only includes

- Blade Views
- Blade Partials
- Existing Controller Integration
- Existing Route Integration
- Bootstrap UI
- AJAX (only if already used in project)

No new business logic.

No policy calculations.

No balance calculations.

No approval rules.

---

# Sidebar

Add new menu

```
HRMS

↓

Leave

↓

Leave Approvals
```

Visible only for

- Reporting Manager
- HR
- Admin

Hidden for employees.

Active menu highlighting required.

---

# Routes

Use ONLY existing routes.

Example

```
hrms.leave.approvals

hrms.leave.approve

hrms.leave.reject

hrms.leave.cancel

hrms.leave.revoke

hrms.leave.show
```

Do NOT create duplicate routes.

---

# Leave Approval Dashboard

Create

```
resources/views/Adminpanel/HRMS/Leaves/approvals.blade.php
```

Display

Summary Cards

```
Pending

Manager Pending

HR Pending

Approved

Rejected

Cancelled

Revoked
```

Reuse Dashboard cards.

No calculations in Blade.

---

# Filters

Provide

```
Employee

Employee Code

Department

Designation

Leave Type

Status

Approval Level

Financial Year

From Date

To Date
```

Retain filter values.

Reuse existing filter design.

---

# Approval Table

Display

```
Employee Photo

Employee Code

Employee Name

Department

Leave Type

From

To

Requested Days

Payable Days

Current Approval Stage

Applied Date

Status

Action
```

Bootstrap responsive table.

---

# Status Badges

Use Bootstrap badges.

Supported

```
Pending

Manager Approved

HR Approved

Approved

Rejected

Cancelled

Revoked
```

Each badge should have unique color.

---

# Action Buttons

Pending

```
View

Approve

Reject
```

Approved

```
View
```

Rejected

```
View
```

Cancelled

```
View
```

Revoked

```
View
```

Do not show Approve button once approved.

---

# View Leave Details

Open

```
show.blade.php
```

Display

Employee Information

Employee Photo

Department

Designation

Joining Date

Leave Type

Reason

Attachment

Calculation Snapshot

```
Requested Days

Holiday Days

Weekly Off

Sandwich Days

Payable Days
```

Remaining Balance

Timeline

Approval History

---

# Approval Timeline

Display

```
Applied

↓

Manager Approved

↓

HR Approved

↓

Approved
```

Include

```
Date

Time

User

Role

Remarks
```

---

# Approval History Table

Display

```
User

Role

Action

Date

Time

Remarks

IP Address
```

Read only.

---

# Approval Modal

Create reusable Bootstrap modal.

Purpose

Approve leave.

Fields

```
Remarks (Optional)
```

Buttons

```
Approve

Cancel
```

---

# Reject Modal

Bootstrap modal.

Fields

```
Remarks

(required)

minimum 10 characters
```

Buttons

```
Reject

Cancel
```

---

# Cancel Leave

Employee

Pending only.

Display

```
Cancel Leave
```

Confirmation

```
Are you sure?

This request will be cancelled.
```

---

# Revoke Leave

Admin only.

Display

```
Revoke Approval
```

Confirmation

```
This will restore leave balance.

Continue?
```

---

# Approval Buttons

Use loading spinner.

Disable duplicate click.

Prevent double submission.

Reuse existing JS helper.

---

# Employee Leave List

Employee should see

```
Pending

Approved

Rejected

Cancelled

Revoked
```

Status badge.

Timeline button.

Cancel button

(Pending only)

Edit button

(Pending only)

---

# Manager Dashboard

Display

```
Pending Approvals

Today's Requests

Upcoming Leave

Recently Approved
```

Reuse dashboard cards.

---

# HR Dashboard

Display

```
Pending Manager Approval

Pending HR Approval

Pending Admin Approval

Rejected

Approved Today
```

---

# Admin Dashboard

Display

```
Pending Final Approval

Today's Approved

Revoked

Cancelled

```

---

# Bulk Actions

Allow

```
Approve Selected

Reject Selected
```

Only

HR

Admin

Confirmation required.

---

# Search

Support

```
Employee Name

Employee Code

Leave Type

Department
```

Instant filter not required.

Server-side filtering.

---

# Employee Photo

Reuse employee upload path.

Missing image

↓

Default avatar.

---

# Empty State

Display

```
No Leave Requests Found
```

Reuse existing illustration if available.

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

# Flash Messages

Reuse existing flash component.

Display

```
Approved Successfully

Rejected Successfully

Cancelled Successfully

Revoked Successfully

Validation Errors
```

Do not introduce another alert library.

---

# Breadcrumb

Display

```
Dashboard

↓

HRMS

↓

Leave

↓

Approvals
```

Reuse existing breadcrumb.

---

# Responsive UI

Desktop

Responsive table.

Tablet

Scrollable.

Mobile

Bootstrap responsive cards.

No separate Blade.

---

# Blade Rules

Blade MUST NOT

- Calculate leave
- Calculate balances
- Calculate approval flow
- Query database

Blade ONLY displays data.

---

# Performance

Reuse eager loaded relations.

Never query inside Blade.

Avoid N+1.

---

# Authorization

Employee

Own requests only.

Reporting Manager

Own reporting employees.

HR

All.

Admin

All.

Return

403

when unauthorized.

---

# Browser Verification

Verify manually

✓ Sidebar menu

✓ Approval dashboard

✓ Filters

✓ Search

✓ Pagination

✓ View details

✓ Timeline

✓ Approve

✓ Reject

✓ Cancel

✓ Revoke

✓ Employee status

✓ Flash messages

✓ Breadcrumb

✓ Employee photo

✓ Empty state

✓ Mobile layout

✓ No undefined variables

✓ No broken images

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

- Leave Approval frontend completed
- Sidebar integration completed
- Approval dashboard completed
- Approval modals completed
- Timeline completed
- Bulk actions completed
- Employee leave status completed
- Responsive UI completed
- Browser verification completed
- Verification commands passed

---

# Success Criteria

Task is complete when

- HR/Admin can see the **Leave Approvals** menu.
- Pending leave requests are listed.
- Approve/Reject buttons are visible according to role.
- Employee can cancel only pending leave.
- Admin can revoke approved leave.
- Timeline is visible.
- Approval history is visible.
- Bulk approval works.
- Search and filters work.
- Pagination works.
- Flash messages work.
- Breadcrumb works.
- Existing architecture remains unchanged.
- No business logic is added to Blade.
- Existing services/controllers are reused.
- Manual browser verification passes.
- All verification commands pass successfully.
```