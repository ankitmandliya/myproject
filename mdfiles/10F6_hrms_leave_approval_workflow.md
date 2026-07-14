# 10F6_hrms_leave_approval_workflow.md

## Module

HRMS → Leave Management

---

# Objective

Implement a complete enterprise Leave Approval Workflow.

The workflow must support:

- Reporting Manager approval
- HR approval
- Admin approval
- Auto approval (optional)
- Timeline
- Remarks
- Notifications
- Audit Logs
- Balance Management
- Security
- Role-based authorization

The workflow must work seamlessly with the Leave Policy Engine (10F3), Prorata Allocation (10F4), Snapshot Engine (10F4B), Sandwich Engine (10F5), and Live Calculation Engine (10F5A).

---

# Approval Flow

The system should support configurable approval levels.

Example:

```
Employee

↓

Reporting Manager

↓

HR

↓

Admin

↓

Approved
```

If company requires only HR approval:

```
Employee

↓

HR

↓

Approved
```

If Auto Approval enabled:

```
Employee

↓

Approved
```

Approval levels should come from Company Settings.

Never hardcode.

---

# Leave Status

Supported statuses

```
Pending

Manager Approved

HR Approved

Approved

Rejected

Cancelled

Revoked
```

Use constants or enum.

Never use magic strings.

---

# Database Changes

Update leave_apply table.

Add:

```
approval_level

approved_by

approved_at

manager_id

manager_status

manager_remarks

manager_action_at

hr_id

hr_status

hr_remarks

hr_action_at

admin_id

admin_status

admin_remarks

admin_action_at

rejected_by

rejected_at

cancelled_by

cancelled_at

revoked_by

revoked_at
```

All nullable.

---

# Approval Timeline

Each leave should display timeline.

Example

```
Applied

07 Jul 09:05 AM

↓

Manager Approved

07 Jul 10:15 AM

↓

HR Approved

07 Jul 11:40 AM

↓

Final Approved

07 Jul 12:00 PM
```

Rejected

```
Applied

↓

Manager Approved

↓

Rejected by HR
```

Cancelled

```
Applied

↓

Cancelled by Employee
```

---

# Reporting Manager

Employee should have

```
reporting_manager_id
```

Only reporting manager may approve first level.

Other managers cannot.

---

# HR Approval

HR users

can approve

after manager approval.

---

# Admin Approval

Admin approves final stage

if configured.

---

# Auto Approval

Company Setting

```
leave_auto_approval

0

1
```

If enabled

Leave immediately

```
Approved
```

No manual approval required.

---

# Approval Dashboard

HR/Admin

should have

```
Pending

Approved

Rejected

Cancelled

Revoked
```

tabs.

Filters

Employee

Department

Leave Type

Status

Date Range

Financial Year

---

# Approval Screen

Display

Employee Photo

Employee Name

Employee Code

Department

Designation

Leave Type

Reason

Attachment

Remaining Balance

Requested Days

Holiday Days

Weekly Off

Sandwich Days

Final Payable Days

Calculation Snapshot

Timeline

Previous Leave History

---

# Manager Actions

Buttons

Approve

Reject

View Details

---

# HR Actions

Buttons

Approve

Reject

Return Back

View History

---

# Admin Actions

Buttons

Approve

Reject

Revoke

---

# Remarks

Approval

remarks optional.

Reject

remarks mandatory.

Minimum

10 characters.

---

# Balance Consumption

Never deduct balance

when employee applies.

Balance should deduct ONLY after

Final Approval.

---

# Reject

Rejected leave

should never deduct balance.

---

# Cancel

Employee may cancel

ONLY

Pending

Manager Approved

HR Approved

Not after final approval.

---

# Revoke

Admin may revoke

approved leave

ONLY IF

Payroll not processed.

On revoke

restore balance.

---

# Overlapping Leave

Before approval

check

approved leave overlap.

Reject approval

if overlap exists.

---

# Attendance Validation

If attendance already exists

for approved leave dates

show warning.

Admin decides.

---

# Resigned Employee

Do not approve

leave

for

Inactive

Resigned

Terminated

employees.

---

# Notification

Notify

Reporting Manager

when employee applies.

Notify Employee

after

Approve

Reject

Cancel

Revoke

Future implementation may send

Email

SMS

WhatsApp

Current implementation

Database notification only.

---

# Audit Log

Log every action.

Example

```
Employee Applied

↓

Manager Approved

↓

HR Approved

↓

Admin Approved
```

Include

User

Date

Time

IP

Remarks

Never delete logs.

---

# Leave History

Employee profile

should display

Applied

Pending

Approved

Rejected

Cancelled

Revoked

Timeline.

---

# Dashboard Cards

Employee Dashboard

Pending

Approved

Rejected

Remaining Leave

Current Month Leave

Current Financial Year Leave

---

# HR Dashboard

Pending Approval

Today's Requests

Rejected

Approved Today

Cancelled

Department Wise Pending

---

# Security

Employee

may view only own leave.

Manager

may approve

own reporting employees.

HR

may approve

all employees.

Admin

full access.

Return

403

for unauthorized access.

---

# Snapshot

Approval should always use

stored snapshot.

Never recalculate

approved leave.

Snapshot remains immutable.

---

# Calendar

Approved leave

should automatically appear

in

Employee Calendar

HR Calendar

Attendance Calendar

---

# Payroll Lock

If payroll processed

for month

disable

Approve

Reject

Cancel

Revoke

---

# Validation

Reject

duplicate approval.

Reject

duplicate rejection.

Reject

cancel after approval.

Reject

revoke twice.

Reject

approval without permission.

Reject

missing remarks on rejection.

Reject

future invalid dates.

---

# UI

Approval page

should have

Sticky Summary Card

Approval Timeline

Employee Card

Leave Summary

Calculation Summary

Attachment Preview

Approval Buttons

Remarks Box

Responsive layout.

---

# Service Layer

Business logic

must remain inside

```
LeaveApprovalService
```

Controller should only

authorize

call service

return response.

---

# Events

Fire events

```
LeaveApplied

LeaveManagerApproved

LeaveHRApproved

LeaveApproved

LeaveRejected

LeaveCancelled

LeaveRevoked
```

Future modules

Email

Notifications

Payroll

Attendance

will listen.

---

# Smoke Tests

Verify:

- Employee applies leave.
- Reporting Manager approves.
- HR approves.
- Admin approves.
- Auto approval works.
- Reject requires remarks.
- Reject restores balance.
- Final approval deducts balance.
- Cancel pending leave works.
- Cancel approved leave blocked.
- Revoke restores balance.
- Duplicate approval blocked.
- Unauthorized approval returns 403.
- Timeline created correctly.
- Notifications stored.
- Audit log created.
- Snapshot unchanged after approval.
- Calendar updated after approval.
- Payroll-locked leave cannot be modified.
- Overlapping leave blocked.

---

# Required Verification

Run:

```
php artisan migrate

php artisan optimize:clear

php artisan optimize

php artisan view:cache

php artisan route:list

php artisan about

find app -name "*.php" -exec php -l {} \;
```

---

# Progress.md

Update progress.md with:

- Multi-level approval workflow implemented
- Reporting Manager, HR and Admin approval chain
- Approval timeline
- Role-based authorization
- Notification hooks
- Audit logs
- Snapshot-based approval
- Balance deduction on final approval
- Balance restoration on revoke/reject
- Overlap validation
- Payroll lock validation
- Calendar integration
- Events added
- Verification completed