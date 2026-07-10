# HRMS Global Timezone & Attendance Modal Fix (Laravel 10)

## Task

10E6_hrms_global_timezone_and_modal_fix.md

---

# Objective

This task standardizes the entire HRMS application to use **Indian Standard Time (Asia/Kolkata)** and fixes the remaining Attendance header modal/UI issues.

This is a system-wide configuration task.

Every current and future HRMS module must use the same timezone.

No module should use UTC or server-local timezone directly.

---

# IMPORTANT

Reuse existing

- Laravel Configuration
- Company Settings
- AttendanceService
- LeaveService
- SalaryService
- DashboardService
- HolidayService
- Controllers
- Middleware
- Existing Layout

Do NOT redesign any UI.

---

# GLOBAL TIMEZONE (MANDATORY)

The entire application must use

```
Asia/Kolkata
```

Do NOT use

```
UTC
```

Do NOT use

```
server timezone
```

Do NOT manually convert timezone inside services.

Laravel application timezone must become the single source of truth.

---

# APPLICATION CONFIGURATION

Update

```
.env
```

Use

```
APP_TIMEZONE=Asia/Kolkata
```

Update

```
config/app.php
```

```
'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
```

Never hardcode timezone elsewhere.

---

# CARBON USAGE

Every service must use

```
now()

today()

Carbon::now()

Carbon::today()
```

Remove any usage of

```
date()

gmdate()

time()

Carbon::now('UTC')
```

Attendance calculations must always use Laravel timezone.

---

# MODULES AFFECTED

Review and standardize all existing HRMS modules.

Attendance

- Check In
- Check Out
- Auto Checkout
- Calendar
- Reports
- History

Leave

- Apply Date
- Leave Duration
- Leave Reports

Salary

- Payroll Month
- Salary Generation
- Salary Reports

Dashboard

- Today's Statistics
- Monthly Charts
- Recent Activities

Holiday

- Date Comparison

Company Settings

- Office Time
- Late Threshold
- Half-Day Threshold

Employee

- Joining Date Display

Every module must use the same timezone.

---

# FUTURE DEVELOPMENT RULE

Every future HRMS feature must use

```
now()
```

or

```
Carbon::now()
```

Never use

```
date()
```

Never use

```
gmdate()
```

Never use

```
strtotime()
```

unless wrapped by Carbon.

This becomes a permanent project rule.

---

# ATTENDANCE HEADER WIDGET

Header widget must always display

Current Indian Time

Example

```
09 Jul 2026

01:45 PM IST
```

Time should update using

Asia/Kolkata timezone.

---

# ATTENDANCE BUSINESS RULES

Current time

↓

Compare against

Company Office Time

↓

Calculate

- Present
- Late
- Half Day
- Working Hours

using IST only.

---

# CALENDAR

Current day

Previous month

Next month

Holiday

Weekly Off

Attendance

must all use

```
Asia/Kolkata
```

No UTC comparison.

---

# REPORTS

Attendance

Leave

Salary

Dashboard

must display dates and time in IST.

---

# MODAL BUG FIX

Current issue

Attendance confirmation popup leaves

- faded background
- disabled page
- hidden buttons
- stuck backdrop

after submit.

---

# REQUIRED FIX

Use Bootstrap modal correctly.

When modal closes

Remove

```
modal-open
```

Remove

```
.modal-backdrop
```

Restore

```
body overflow
```

Restore scrolling.

Never manually manipulate

opacity

display

visibility

using custom CSS.

Use Bootstrap lifecycle

```
shown.bs.modal

hidden.bs.modal
```

to clean up modal state.

---

# BUTTON STATE

When employee clicks

Check In

or

Check Out

Disable button only during request.

After response

Immediately

- hide modal
- remove backdrop
- enable page
- update header widget
- update calendar
- update attendance list

No page refresh required.

---

# AJAX / FORM SUBMIT

Prevent duplicate submission.

Show loading spinner.

After success

- close modal
- remove overlay
- enable buttons
- refresh widget state

---

# BROWSER VERIFICATION

Verify manually

✓ Application timezone is Asia/Kolkata

✓ Current Indian time displayed correctly

✓ Check In uses IST

✓ Check Out uses IST

✓ Attendance calculations use IST

✓ Leave dates use IST

✓ Salary reports use IST

✓ Dashboard uses IST

✓ Calendar uses IST

✓ Holiday comparison uses IST

✓ Popup closes correctly

✓ Background is clickable again

✓ No stuck backdrop

✓ No disabled screen

✓ Buttons re-enable correctly

✓ No duplicate requests

---

# VERIFICATION COMMANDS

Run

```bash
docker compose exec app php artisan optimize:clear
```

```bash
docker compose exec app php artisan optimize
```

```bash
docker compose exec app php artisan config:clear
```

```bash
docker compose exec app php artisan config:cache
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

---

# progress.md

Update

- Global Asia/Kolkata timezone configured
- Laravel timezone standardized
- Carbon usage standardized
- Attendance uses IST
- Leave uses IST
- Salary uses IST
- Dashboard uses IST
- Holiday comparisons use IST
- Future development timezone rule documented
- Attendance modal backdrop fixed
- Header widget updated
- Bootstrap modal lifecycle corrected
- Browser verification completed

---

# SUCCESS CRITERIA

Task is complete when

- Entire HRMS uses Asia/Kolkata timezone.
- No UTC/server-time based attendance calculations remain.
- All date/time displays are consistent across modules.
- Future HRMS modules inherit the same timezone configuration.
- Attendance popup no longer freezes the screen.
- Modal backdrop is removed correctly.
- Buttons enable/disable correctly.
- Header widget updates immediately.
- Calendar, reports, attendance list, and dashboard remain synchronized.
- All verification commands pass.
- Manual browser verification passes.
```