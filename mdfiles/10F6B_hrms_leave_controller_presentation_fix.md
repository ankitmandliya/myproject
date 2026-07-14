# 10F6B_hrms_leave_controller_presentation_fix.md

# Module

HRMS → Leave Management → Controller Presentation Fix

---

# Objective

Resolve the runtime error:

```
Method App\Http\Controllers\HRMS\LeaveApplyController::prepareLeavePresentation does not exist.
```

This task restores the missing presentation layer introduced during the Leave Approval Frontend implementation.

No business logic should be modified.

Only restore the controller presentation helper and any missing view-model preparation.

---

# Error

Current runtime error

```
Method

App\Http\Controllers\HRMS\LeaveApplyController::prepareLeavePresentation()

does not exist.
```

---

# Root Cause

During previous Leave module refactoring,

controller actions call

```
prepareLeavePresentation()
```

but the helper method is missing or was accidentally removed.

Several views now expect prepared presentation fields instead of raw database values.

---

# Scope

This task ONLY fixes

- Missing controller helper
- Presentation mapping
- View data preparation
- Null-safe formatting
- Existing Blade compatibility

Do NOT modify

- Leave calculations
- Leave balances
- Leave approval workflow
- Leave notifications
- Attendance integration
- Leave reports

---

# Controller

Update

```
app/Http/Controllers/HRMS/LeaveApplyController.php
```

Restore

```
protected function prepareLeavePresentation($leave)
```

or equivalent helper.

---

# Responsibility

The helper should ONLY prepare UI data.

No database updates.

No business calculations.

No approval logic.

---

# Prepare Fields

Return display-ready fields.

Examples

```
Employee Photo

Employee Name

Employee Code

Department

Designation

Leave Type

Leave Badge

Status Badge

Status Color

Duration Text

Applied Date

Approved Date

Rejected Date

Cancelled Date

Approved By

Pending With

Current Stage

Reason

Remarks

Timeline

Balance Summary

Sandwich Summary

LWP Summary

```

---

# Date Formatting

Reuse existing application formatting.

Do NOT use raw Carbon formatting inside Blade.

Controller should prepare

```
Applied On

12 Jul 2026

instead of

2026-07-12
```

---

# Badge Preparation

Prepare

```
Pending

↓

warning

Approved

↓

success

Rejected

↓

danger

Cancelled

↓

secondary

Revoked

↓

dark
```

Blade should not decide colors.

---

# Timeline

Prepare complete timeline array

```
Applied

Manager Approval

HR Approval

Admin Approval

Completed
```

depending on workflow.

---

# Null Safety

Handle safely

```
Employee Photo

Department

Designation

Manager

Approver

Remarks

Timeline

Leave Type

```

No

```
Trying to get property of non-object
```

No

```
Undefined array key
```

No

```
Attempt to read property on null
```

---

# Collection Support

Support

Single Leave

↓

Show Page

and

Collection

↓

Index

Approval

History

Reports

without duplicate code.

---

# Blade Cleanup

Remove any

```
Carbon::parse()

optional()

status color logic

badge logic

timeline calculations
```

from Blade.

Blade should display only prepared values.

---

# Performance

Use eager-loaded relationships.

No database queries inside helper.

---

# Authorization

Do not modify existing authorization.

---

# Browser Verification

Verify

✓ Leave List

✓ Leave Details

✓ Leave Approval

✓ Leave History

✓ Employee Leave

✓ Manager Leave

✓ HR Leave

✓ Admin Leave

✓ Timeline

✓ Badges

✓ Employee Photo

✓ Empty State

✓ No Undefined Variables

✓ No Runtime Errors

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

- Restored prepareLeavePresentation helper
- Controller presentation mapping completed
- Blade presentation cleanup completed
- Runtime error resolved
- Browser verification completed
- Verification commands passed

---

# Success Criteria

Task is complete when

- `prepareLeavePresentation()` exists.
- All controller actions reuse the helper.
- Leave List renders successfully.
- Leave Details render successfully.
- Approval pages render successfully.
- Timeline displays correctly.
- Blade contains no presentation calculations.
- No runtime exception occurs.
- No duplicate presentation logic exists.
- All Laravel verification commands pass.