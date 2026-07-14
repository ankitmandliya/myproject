# 10F6C_hrms_leave_approval_workflow_bugfix.md

# Module

HRMS → Leave Management → Approval Workflow Bug Fix & End-to-End Verification

---

# Objective

Fix all issues in the Leave Approval workflow and verify the complete Leave module end-to-end.

Current issue:

- "Approve Selected" button is not performing any action.
- "Reject Selected" button is not performing any action.

This task must ensure the complete approval workflow is fully functional from frontend to database.

Do NOT introduce new features.

Only fix broken workflow and verify the existing implementation.

---

# Scope

Verify and fix

- Approval Dashboard
- Bulk Approval
- Bulk Reject
- Single Approval
- Single Reject
- Checkbox Selection
- AJAX/Form submission
- Route binding
- CSRF
- Controller
- Service
- Database update
- Notifications
- Attendance integration
- Leave balance update
- Timeline
- Audit log

---

# Frontend

Verify

resources/views/Adminpanel/HRMS/Leaves/approvals.blade.php

Check

- Bulk buttons
- Checkbox selection
- Select All checkbox
- Button enable/disable
- Form action
- AJAX endpoint
- Confirmation modal
- Loading spinner
- Duplicate-click prevention
- Success flash message
- Error flash message

Buttons must remain disabled until at least one row is selected.

---

# Bulk Approval

Verify

Approve Selected

Flow

Select

↓

Approve

↓

Confirmation

↓

Controller

↓

LeaveApprovalService

↓

Success

↓

Refresh Grid

---

# Bulk Reject

Verify

Reject Selected

↓

Confirmation

↓

Remarks required

↓

Reject

↓

Refresh Grid

---

# Single Approval

Verify

Approve button

↓

Confirmation

↓

Approval

↓

Notification

↓

Timeline update

↓

Attendance refresh

↓

Leave balance deduction

---

# Single Reject

Verify

Reject

↓

Remarks required

↓

Rejected

↓

Notification

↓

Timeline update

---

# Validation

Prevent

Approving

Already Approved leave

Already Rejected leave

Cancelled leave

Revoked leave

Duplicate approval

Duplicate rejection

---

# Leave Balance

After approval verify

Employee Leave Balance

↓

Consumed

↓

Remaining

↓

Snapshot unchanged

---

# Attendance Integration

After approval verify

Attendance Calendar

↓

Leave badge

Attendance Widget

↓

Cannot Check In

History

↓

Approved Leave

Reports

↓

Leave shown

---

# Notification Verification

Employee receives

Leave Approved

Leave Rejected

Manager receives

Action completed

Notification bell updates immediately.

---

# Timeline

Verify

Applied

↓

Manager

↓

HR

↓

Admin

↓

Completed

Reject path

↓

Rejected

Cancel path

↓

Cancelled

---

# Audit Log

Verify

Approved By

Approved At

Rejected By

Rejected At

IP Address

Remarks

---

# Route Verification

Verify

All approval routes exist.

No 404.

No 405.

No CSRF mismatch.

---

# Controller Verification

Verify

LeaveApplyController

ApprovalController

All methods exist.

No missing helper.

No missing service injection.

---

# Service Verification

Verify

LeaveApprovalService

Approve

Reject

Bulk Approve

Bulk Reject

Cancel

Revoke

Rollback

No duplicate processing.

---

# Database Verification

Verify

leave_apply

employee_leave_balances

notifications

attendance

audit log

timeline JSON

All updates occur correctly.

---

# Browser Verification

Manually verify

✓ Leave Apply

✓ Leave List

✓ Pending Leave

✓ Approve

✓ Reject

✓ Bulk Approve

✓ Bulk Reject

✓ Attendance Calendar

✓ Attendance Widget

✓ Notifications

✓ Timeline

✓ Audit Log

✓ Leave Balance

✓ Dashboard

✓ Reports

✓ Sidebar

✓ Pagination

✓ Search

✓ Filters

✓ Mobile View

---

# Entire Leave Module Verification

Re-test every implemented Leave feature.

Verify

✓ 10F1 Controller Integration

✓ 10F2 UI Refinement

✓ 10F3 Leave Policy Engine

✓ 10F4 Prorata Allocation

✓ 10F4B Calculation Snapshot

✓ 10F5 Sandwich Leave Engine

✓ 10F5A Live Calculation

✓ 10F6 Approval Workflow

✓ 10F6A Approval Frontend

✓ 10F6B Controller Presentation Fix

✓ 10F7 Attendance Integration

✓ 10F8 Notifications

✓ 10F9 Reports

✓ 10F10 Financial Year Closing

Ensure nothing is broken after the fixes.

---

# Laravel Verification

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

progress.md

Include

- Bulk approval fixed
- Bulk rejection fixed
- Approval workflow verified
- Attendance integration verified
- Leave balance verification completed
- Notification verification completed
- Timeline verification completed
- Entire Leave module regression tested
- Browser verification completed
- Laravel verification commands passed

---

# Success Criteria

Task is complete when

- Approve Selected works.
- Reject Selected works.
- Single approve/reject works.
- Checkboxes correctly enable bulk actions.
- Leave balances update correctly.
- Attendance reflects approved leave.
- Notifications are generated.
- Timeline updates correctly.
- Audit logs are created.
- No JavaScript errors occur.
- No controller or route errors occur.
- Complete Leave module regression testing passes.
- All Laravel verification commands pass successfully.