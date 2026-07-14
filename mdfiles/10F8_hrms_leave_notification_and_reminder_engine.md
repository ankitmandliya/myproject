# 10F8_hrms_leave_notification_and_reminder_engine.md

## Module

HRMS → Leave Management → Notification & Reminder Engine

---

# Objective

Implement a centralized **Leave Notification & Reminder Engine** that keeps Employees, Reporting Managers, HR, and Admin informed throughout the leave lifecycle.

This phase integrates with the existing Leave Approval Workflow (10F6/10F6A), Attendance Integration (10F7), and Dashboard.

This task **must not implement business logic again**. It should only consume the existing events and workflow.

---

# Laravel Version

Laravel 10 only.

---

# Existing Architecture

Reuse existing:

- LeaveService
- LeaveApprovalService
- LeavePolicyService
- AttendanceService
- Existing Events
- Existing Controllers
- Existing Middleware
- Existing Layout
- Existing Navbar
- Existing Sidebar
- Existing Authentication

Do not duplicate logic.

---

# Scope

This task includes:

- Database Notifications
- Notification Center UI
- Reminder Engine
- Navbar Notification Bell
- Notification Preferences
- Scheduled Reminder Jobs
- Read/Unread Status
- Notification History

Do NOT implement:

- Email Sending
- SMS
- WhatsApp
- Push Notifications

Only prepare extension points.

---

# Notification Events

Reuse existing events:

```
LeaveApplied

LeaveApproved

LeaveRejected

LeaveCancelled

LeaveRevoked

LeaveUpdated
```

Do not create duplicate events.

---

# Database Notifications

Use Laravel Database Notifications.

Store

```
title

message

type

icon

priority

url

reference_id

reference_type

created_by

read_at
```

Use Laravel's notifications table.

Do not create another notification table.

---

# Notification Bell

Add notification bell in Header/Navbar.

Display

```
🔔

Unread Count
```

Example

```
🔔 5
```

Hide badge when count is zero.

---

# Notification Dropdown

Clicking the bell opens

```
Latest Notifications
```

Display

```
Icon

Title

Message

Time Ago

Read/Unread Indicator
```

Maximum

```
10
```

latest notifications.

Button

```
View All
```

---

# Notification Types

Support

```
Leave Applied

Leave Approved

Leave Rejected

Leave Cancelled

Leave Revoked

Leave Updated

Reminder

Information

Warning
```

Each type should have its own Bootstrap icon and color.

---

# Employee Notifications

Notify Employee when:

- Leave submitted successfully
- Leave approved
- Leave rejected
- Leave cancelled
- Leave revoked
- Leave starts today
- Leave ends tomorrow
- Leave balance becomes low

---

# Reporting Manager Notifications

Notify when:

- New leave request submitted
- Employee updates pending leave
- Employee cancels pending leave
- Approval overdue
- Leave starts today for reporting employee

---

# HR Notifications

Notify when:

- Manager approves leave
- Pending HR approval
- Employee cancels approved leave
- Leave revoked
- Leave policy conflict

---

# Admin Notifications

Notify when:

- Final approval required
- Leave revoked
- Leave cancelled
- Approval overdue
- System validation warning

---

# Leave Reminder Engine

Create scheduled reminders.

---

## Daily Pending Reminder

Every working day

```
09:30 AM IST
```

Notify

Manager

HR

Admin

```
You have pending leave requests awaiting action.
```

---

## Leave Starts Today

At

```
08:00 AM IST
```

Notify employee

```
Your approved leave starts today.
```

---

## Leave Ends Tomorrow

At

```
06:00 PM IST
```

Notify employee

```
Your leave ends tomorrow.
Please report to work on the next working day.
```

---

## Low Leave Balance

Notify employee when

```
Remaining CL <= 2

Remaining SL <= 2

Remaining EL <= 3
```

Frequency

Once per month.

---

# Notification Center

Create

```
resources/views/Adminpanel/Notifications/index.blade.php
```

Display

```
Icon

Title

Message

User

Created

Status

Action
```

Filters

```
Type

Read

Unread

Date Range
```

Pagination

```
10

25

50

100
```

---

# Read Notification

Support

```
Mark Read
```

Updates

```
read_at
```

---

# Mark All Read

Button

```
Mark All as Read
```

Confirmation

```
Mark all notifications as read?
```

---

# Notification Detail

Display

```
Title

Full Message

Date

Time

Reference

Action Link
```

---

# Navigation

Sidebar

```
Notifications
```

or

Top Navbar

```
View All Notifications
```

Reuse existing layout.

---

# Notification Icons

Use Bootstrap Icons.

Example

```
Leave Applied

bi-send

Leave Approved

bi-check-circle

Leave Rejected

bi-x-circle

Reminder

bi-alarm

Warning

bi-exclamation-triangle

Information

bi-info-circle
```

---

# Priority

Support

```
Low

Medium

High

Critical
```

Badge color

```
Low

Gray

Medium

Blue

High

Orange

Critical

Red
```

---

# Notification Actions

Click notification

↓

Redirect to

```
Leave Details

Approval Screen

Attendance Calendar

Dashboard
```

depending on notification type.

---

# Notification Preferences

Add section in

```
Company Settings
```

Options

```
Enable Notifications

Enable Leave Reminders

Enable Approval Reminders

Enable Low Balance Alerts
```

Reuse existing settings table.

---

# Scheduled Jobs

Create scheduled jobs only.

Do not send emails.

Jobs

```
PendingApprovalReminderJob

LeaveStartReminderJob

LeaveEndReminderJob

LowBalanceReminderJob
```

Register in Laravel Scheduler.

Use Asia/Kolkata timezone.

---

# Dashboard Widget

Employee Dashboard

```
Recent Notifications
```

Latest

```
5
```

notifications.

---

# Authorization

Employee

Own notifications.

Manager

Own + reporting workflow.

HR

HR notifications.

Admin

All.

Unauthorized access

```
403
```

---

# Performance

Eager load notification references.

Never query notifications inside Blade.

Use pagination.

Avoid N+1.

---

# Blade Rules

Blade must only

- Display notifications
- Display badges
- Display icons
- Render pagination

No calculations.

---

# Browser Verification

Verify manually

✓ Notification bell

✓ Unread count

✓ Dropdown

✓ View All

✓ Mark Read

✓ Mark All Read

✓ Employee notifications

✓ Manager notifications

✓ HR notifications

✓ Admin notifications

✓ Reminder notifications

✓ Redirect links

✓ Empty state

✓ Responsive UI

✓ No undefined variables

---

# Empty State

Display

```
No Notifications Found
```

Reuse existing empty-state illustration if available.

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

- Notification engine completed
- Notification bell added
- Notification center completed
- Reminder scheduler completed
- Notification preferences added
- Dashboard widget completed
- Authorization completed
- Browser verification completed
- Verification commands passed

---

# Success Criteria

Task is complete when:

- Employee receives notifications throughout the leave lifecycle.
- Manager receives new leave request notifications.
- HR/Admin receive approval reminders.
- Notification bell displays unread count.
- Notification dropdown works.
- Notification center lists all notifications.
- Read/Unread state works.
- Mark All Read works.
- Scheduled reminder jobs are registered.
- Notification preferences are configurable.
- Existing architecture remains unchanged.
- No duplicate business logic exists.
- Existing services/events are reused.
- Manual browser verification passes.
- All Laravel verification commands pass successfully.
```