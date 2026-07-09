# HRMS Attendance UI Refinement (Final) (Laravel 10)

## Objective

Refine the existing Attendance module to make it production-ready.

This task improves the UI/UX and integrates the remaining attendance features without changing the overall architecture.

The Attendance module already supports:

- Attendance Business Logic
- Attendance Controller
- Attendance Service
- Attendance Calendar
- Attendance History
- Attendance Header Widget

This task ONLY refines the existing implementation.

---

# LARAVEL VERSION

Use Laravel 10 only.

Do NOT use Laravel 11/12 features.

---

# SCOPE

This task includes ONLY

- Attendance Calendar refinement
- Header Attendance Toggle refinement
- Holiday integration refinement
- Weekly Off integration refinement
- Previous / Next Month navigation
- Employee attendance calendar improvements
- Responsive UI improvements
- Blade refinements

Do NOT create

- Models
- Migrations
- Services
- Controllers (unless minimal changes are required)
- Database tables

Reuse the existing architecture.

---

# EXISTING ARCHITECTURE (MANDATORY)

Reuse existing

- AttendanceService
- AttendanceController
- CompanySettingService
- HolidayService
- DashboardService
- Authentication
- Layout
- Sidebar
- Navbar
- Flash Messages

Do NOT duplicate business logic.

---

# EXISTING UI REUSE

Reuse

- KaiAdmin Theme
- Bootstrap
- Existing Header
- Existing Sidebar
- Existing Flash Messages
- Existing Breadcrumb
- Existing Cards

Do NOT redesign the Admin Panel.

---

# EXISTING FUNCTIONALITY PROTECTION

Do NOT break

- Dashboard
- Employee Module
- Holiday Module
- Leave Type Module
- Attendance History
- Attendance Summary
- Attendance Reports
- Company Settings
- Authentication

Only extend Attendance.

---

# CALENDAR REFINEMENT

Replace the existing static date grid with a real monthly calendar.

The calendar must be generated using Carbon.

Use

- startOfMonth()
- endOfMonth()
- startOfWeek(Carbon::MONDAY)
- endOfWeek(Carbon::SUNDAY)

Do NOT hardcode weekdays.

The first day of every month must appear under the correct weekday.

Example

July 2026

```
Mon Tue Wed Thu Fri Sat Sun

        1   2   3   4   5
6   7   8   9  10  11  12
13 14  15 16 17 18 19
20 21  22 23 24 25 26
27 28  29 30 31
```

---

# MONTH NAVIGATION

Add Previous and Next month navigation.

Display

```
← Previous      July 2026      Next →
```

Allow navigation using query parameters.

Example

```
/hrms/attendance/calendar?month=2026-07
```

or existing routing style.

Support

- Previous month
- Current month
- Next month
- Any month/year

Retain selected month after refresh.

---

# TODAY HIGHLIGHT

Highlight today's date.

Example

```
TODAY

9

Present
```

Use Bootstrap border/background only.

Do not introduce custom CSS frameworks.

---

# ATTENDANCE DISPLAY

Each calendar day should display

- Status Badge
- Check In Time
- Check Out Time
- Working Hours (if available)

Example

```
9

Present

09:02

18:05
```

If attendance is unavailable

Display

```
No Attendance
```

---

# STATUS BADGES

Supported

- Present
- Late
- Half Day
- Leave
- Holiday
- Weekly Off
- Absent

Reuse Bootstrap badges.

Do not hardcode colors outside Bootstrap.

---

# HOLIDAY INTEGRATION

Reuse the existing Holiday module.

All holidays must come dynamically from HolidayService.

Do NOT hardcode holidays.

---

# MULTI-DAY HOLIDAY SUPPORT

Holiday ranges must be supported.

Example

Holiday

Hariyali Amavasya

From

2026-07-10

To

2026-07-14

Calendar must automatically mark

10

11

12

13

14

as Holiday.

Do not mark only from_date.

---

# SINGLE DAY HOLIDAY

Support

```
from_date == to_date
```

Display Holiday for that day only.

---

# HOLIDAY PRIORITY

Priority

Holiday

↓

Weekly Off

↓

Attendance

If both occur

Display Holiday.

---

# HOLIDAY DISPLAY

Display

Holiday Badge

Holiday Name

Example

```
10

Holiday

Hariyali Amavasya
```

---

# WEEKLY OFF

Weekly Off must come dynamically from Company Settings.

Never hardcode

Saturday

Sunday

If HR changes Weekly Off

Attendance Calendar should automatically update.

---

# EMPLOYEE CALENDAR

Employee should see

My Attendance Calendar

instead of Attendance List.

Sidebar

Employee

Attendance

↓

My Attendance

---

# HR CALENDAR

HR/Admin should continue to access

Attendance List

with employee filters.

No existing HR functionality should break.

---

# HEADER ATTENDANCE TOGGLE

Replace existing buttons with a Toggle Switch.

States

OFF

↓

ON

↓

Completed

Display inside existing Navbar.

Do not disturb current layout.

---

# CHECK IN

Toggle OFF

↓

User turns ON

Display confirmation

```
Mark today's attendance?

Yes

Cancel
```

---

# CHECK OUT

Toggle ON

↓

User turns OFF

Display confirmation

```
Are you sure you want to check out?

You cannot check in again today.

Yes

Cancel
```

---

# COMPLETED STATE

After Check Out

Display

Attendance Completed

Disable Toggle.

Prevent additional attendance.

---

# DUPLICATE PROTECTION

Prevent

Multiple Check In

Multiple Check Out

Multiple Attendance

The UI must display existing flash messages.

---

# LOADING STATE

During attendance requests

Disable Toggle.

Display spinner.

Prevent duplicate clicks.

---

# ATTENDANCE LEGEND

Display legend below calendar.

Present

Late

Half Day

Leave

Absent

Holiday

Weekly Off

Reuse Bootstrap badges.

---

# EMPTY STATE

If no attendance exists

Display

```
No Attendance Found
```

If month has holidays

Holiday should still appear.

---

# EMPLOYEE PROFILE

From Employee List

Attendance button should open

Employee Calendar

instead of Attendance List.

Employee Calendar must display only that employee's attendance.

---

# RESPONSIVE DESIGN

Desktop

Full Calendar

Tablet

Responsive Calendar

Mobile

Scrollable Calendar

Reuse Bootstrap responsiveness.

---

# BLADE RESPONSIBILITY

Blade MUST NOT

- Query Models
- Query Database
- Calculate attendance
- Determine Holiday
- Determine Weekly Off
- Calculate Working Hours
- Calculate Late
- Calculate Half Day

Blade ONLY displays values supplied by AttendanceController.

---

# PERFORMANCE

Reuse eager-loaded data.

Avoid N+1 queries.

Avoid duplicate AttendanceService calls.

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- Bootstrap Best Practices
- SOLID
- DRY
- PSR-12

Reuse Blade partials.

---

# BROWSER VERIFICATION

Verify

✓ Calendar weekday alignment

✓ Previous month navigation

✓ Next month navigation

✓ Month/year selection

✓ Today highlight

✓ Holiday display

✓ Multi-day holidays

✓ Single-day holidays

✓ Weekly Off display

✓ Attendance display

✓ Check In toggle

✓ Check Out toggle

✓ Attendance Completed state

✓ Employee calendar

✓ HR attendance list

✓ Existing Dashboard unaffected

✓ Existing Employee module unaffected

✓ Existing Holiday module unaffected

✓ Existing Company Settings respected

✓ Existing Flash Messages work

✓ Responsive layout

✓ No broken links

✓ No Blade errors

✓ No undefined variables

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

# PROGRESS UPDATE

Update

```
progress.md
```

Include

- Calendar refined
- Real Carbon calendar implemented
- Previous/Next month navigation added
- Today highlight added
- Holiday integration refined
- Multi-day holiday support completed
- Weekly Off integration completed
- Header attendance toggle completed
- Attendance completed state refined
- Employee calendar integrated
- Responsive calendar improved
- Attendance legend added
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Calendar displays correct weekdays.
- Previous and Next month navigation works.
- Today is highlighted.
- Employee attendance displays correctly.
- Holiday ranges display correctly.
- Single-day holidays display correctly.
- Weekly Off comes from Company Settings.
- Holiday names display correctly.
- Employee opens My Attendance Calendar by default.
- HR continues to use Attendance List.
- Header toggle supports Check In and Check Out.
- Duplicate attendance is prevented.
- Attendance Completed state works.
- Existing Admin Panel design is preserved.
- No business logic exists in Blade.
- Existing services/controllers are reused.
- Browser verification passes.
- All verification commands pass successfully.
```