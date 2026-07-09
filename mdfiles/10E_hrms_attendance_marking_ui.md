# HRMS Attendance Marking UI Task (Laravel 10)

## Objective

Implement the complete Attendance Marking UI for the HRMS system.

This module enables employees to record their daily attendance using a Check In / Check Out widget integrated into the existing Admin Panel Header.

Attendance should be recorded through the existing Attendance module and must fully reuse the existing architecture.

This task is responsible ONLY for presentation and UI integration.

No attendance calculations or business rules should be implemented inside Blade files.

---

# LARAVEL VERSION

Use Laravel 10 only.

Do NOT use Laravel 11 or Laravel 12 features.

---

# EXISTING ARCHITECTURE (MANDATORY)

Reuse the existing

- AttendanceService
- AttendanceController
- CompanySettingService
- HolidayService
- Authentication
- Dashboard
- Employee Module
- Layout
- Sidebar
- Navbar
- Flash Messages

Do NOT duplicate any attendance logic.

---

# SCOPE

This phase ONLY includes

- Header Attendance Widget
- Blade Integration
- Bootstrap Components
- Controller Integration
- Existing Service Integration
- Confirmation Modal
- Loading State
- Flash Messages

Do NOT implement

- Attendance calculations
- Salary calculations
- Database queries
- Models
- Migrations
- New Services
- New Business Logic

---

# EXISTING UI REUSE

Reuse

- Existing Header
- Existing Navbar
- Existing Bootstrap Components
- Existing Flash Messages
- Existing Layout
- Existing Icons
- Existing Breadcrumb Components

Do NOT redesign the Admin Panel.

---

# ATTENDANCE ARCHITECTURE

Attendance must follow this flow.

Company Settings Table

↓

CompanySettingService

↓

Holiday Table

↓

HolidayService

↓

Attendance Table

↓

AttendanceService

↓

AttendanceController

↓

Header Attendance Widget

The Header Widget must never directly access

- Models
- Database
- Company Settings
- Holiday Table

Everything must come through AttendanceService.

---

# SINGLE SOURCE OF TRUTH

AttendanceService is the only service responsible for determining today's attendance state.

AttendanceController should communicate ONLY with AttendanceServiceInterface.

AttendanceService may internally consume

- CompanySettingService
- HolidayService

The Blade must receive a fully prepared view model.

---

# HEADER ATTENDANCE WIDGET

Integrate a new Attendance Widget inside the existing Admin Header.

Place the widget near

- Notifications
- User Profile

Do not disturb the existing layout.

---

# HEADER DISPLAY

Before Check In

Attendance

[ Check In ]

---

After Check In

Attendance

Checked In

09:02 AM

[ Check Out ]

---

After Check Out

Attendance Completed

Check In

09:02 AM

Check Out

06:15 PM

Completed

---

# ATTENDANCE STATES

The UI must support exactly three states.

State 1

Check In Available

Display

Green Bootstrap Button

Check In

---

State 2

Checked In

Display

Check In Time

Orange Bootstrap Button

Check Out

---

State 3

Attendance Completed

Display

Check In Time

Check Out Time

Green Success Badge

Attendance Completed

Disable attendance buttons.

---

# BUTTON BEHAVIOUR

Check In

Calls AttendanceController.

The Blade must never decide whether Check In is allowed.

AttendanceController provides

canCheckIn

---

Check Out

Calls AttendanceController.

Before submission display confirmation modal.

---

# CHECK OUT CONFIRMATION

Display Bootstrap Modal.

Title

Confirm Check Out

Message

Are you sure you want to check out?

Once checked out, you cannot check in again today.

Buttons

Yes, Check Out

Cancel

---

# LOADING STATE

After clicking Check In or Check Out

Disable button.

Display Bootstrap Spinner.

Prevent double clicking.

---

# FLASH MESSAGES

Reuse existing flash component.

Support

- Success
- Warning
- Error
- Validation Errors

Do not create another notification system.

---

# RESPONSIVE DESIGN

Desktop

Full Attendance Widget.

Tablet

Compact Widget.

Mobile

Dropdown Widget.

Reuse existing Bootstrap responsiveness.

---

# AUTHENTICATION

Attendance Widget should only be visible for authenticated employees.

Guests

Do not display Attendance Widget.

---

# ROLE VISIBILITY

Employee

Can only mark his own attendance.

HR

May mark his own attendance (if HR is also an employee).

Admin

May mark his own attendance.

No user can mark attendance on behalf of another employee from the Header Widget.

# COMPANY SETTINGS INTEGRATION (MANDATORY)

Attendance Marking must NEVER use hardcoded values.

All attendance rules must be dynamically loaded from the existing Company Settings module.

Do NOT duplicate any configuration.

The Company Settings module is the single source of truth for attendance configuration.

---

# COMPANY SETTINGS

Reuse the existing Company Setting table.

The following values must be fetched through the existing CompanySettingService.

- Office Start Time
- Office End Time
- Late Threshold (Minutes)
- Half Day Threshold
- Weekly Off Days
- Salary Date
- Company Time Zone (if available)

Do NOT hardcode any of these values.

---

# HOLIDAY INTEGRATION

Reuse the existing Holiday module.

Company holidays must come from the existing Holiday table through HolidayService.

Do NOT hardcode holidays.

Do NOT create another holiday table.

---

# ATTENDANCE PRIORITY

AttendanceService must determine today's attendance state using the following priority.

Priority 1

Company Holiday

↓

Priority 2

Weekly Off

↓

Priority 3

Attendance Record

↓

Priority 4

Attendance Not Marked

The Blade must simply display the returned state.

---

# COMPANY HOLIDAY

If today is a Company Holiday

AttendanceService should return

Holiday

Holiday Name

Holiday Date

AttendanceController passes these values to Blade.

The Blade displays

Today's Holiday

Independence Day

Attendance is disabled today.

Disable

Check In

Check Out

---

# WEEKLY OFF

Weekly Off must come from Company Settings.

Examples

Sunday

Saturday, Sunday

Friday

AttendanceService determines whether today is a weekly off.

Blade displays

Today is Weekly Off.

Attendance is not required.

Disable attendance buttons.

---

# OFFICE TIMING

The Attendance Widget should display office timing.

Example

Office Hours

09:00 AM - 06:00 PM

These values come from Company Settings.

---

# LATE POLICY

Display

Late After

09:15 AM

The Blade only displays.

AttendanceService calculates.

---

# HALF DAY POLICY

Display

Half Day After

01:00 PM

AttendanceService calculates.

Blade only renders.

---

# CURRENT STATUS

The Header Widget should display today's attendance status.

Examples

Present

Late

Half Day

Holiday

Weekly Off

Attendance Completed

Attendance Pending

The Blade must never determine status.

---

# REQUIRED VIEW VARIABLES

AttendanceController should pass a complete Attendance View Model.

Example

attendanceStatus

statusBadge

canCheckIn

canCheckOut

attendanceCompleted

checkInTime

checkOutTime

officeStartTime

officeEndTime

lateThreshold

halfDayThreshold

weeklyOff

isWeeklyOff

isCompanyHoliday

holidayName

holidayDate

officeOpen

todayDate

currentTime

employeeName

employeeCode

workingHours

---

# CONTROLLER RESPONSIBILITY

AttendanceController should ONLY consume

AttendanceServiceInterface

AttendanceController must never

Query models

Query Company Settings

Query Holiday table

Calculate attendance

Calculate Late

Calculate Half Day

Everything comes from AttendanceService.

---

# ATTENDANCE SERVICE RESPONSIBILITY

AttendanceService is responsible for aggregating all attendance information.

AttendanceService may internally use

CompanySettingService

HolidayService

Attendance Repository/Model

AttendanceService returns one complete Attendance Widget response.

---

# ATTENDANCE VIEW MODEL

AttendanceService should expose a method similar to

getTodayAttendanceWidget()

Example response

status

statusBadge

canCheckIn

canCheckOut

attendanceCompleted

checkInTime

checkOutTime

officeStartTime

officeEndTime

lateThreshold

halfDayThreshold

holiday

holidayName

weeklyOff

workingHours

This object is passed directly to the Blade.

---

# DYNAMIC CONFIGURATION

Whenever HR changes

Office Start Time

Office End Time

Late Minutes

Half Day Time

Weekly Off

Holiday

No Attendance code should require modification.

The Attendance Widget should automatically reflect the latest Company Settings.

---

# MULTIPLE CHECK-IN

If the user has already checked in today

AttendanceService returns

canCheckIn = false

message = "You have already checked in today."

The Blade simply displays the flash message.

---

# MULTIPLE CHECK-OUT

If the user has already checked out

AttendanceService returns

attendanceCompleted = true

The Header Widget displays

Attendance Completed

Buttons remain disabled.

---

# CHECK-OUT WITHOUT CHECK-IN

If the user attempts to check out without checking in

AttendanceService returns

You have not checked in today.

Display existing flash message.

---

# EMPLOYEE ACCESS

Authenticated Employee

Can only access

Today's Attendance

Cannot pass another employee ID.

Attendance always belongs to

auth()->user()

---

# HR / ADMIN ACCESS

The Header Attendance Widget always marks the logged-in user's own attendance.

HR/Admin attendance management for other employees continues to be handled through the existing Employee → Attendance pages.

The Header Widget must never allow selecting another employee.

---

# BLADE RESPONSIBILITY

Blade MUST NOT

Call Models

Call Services

Read Company Settings

Read Holiday Table

Calculate Late

Calculate Half Day

Calculate Working Hours

Determine Weekly Off

Determine Holiday

Determine Attendance Status

Blade ONLY renders the values received from AttendanceController.

---

# PERFORMANCE

Attendance Widget should require only one AttendanceService call.

Avoid multiple service calls.

Avoid duplicate queries.

Reuse eager-loaded data whenever possible.

# FUTURE READY ARCHITECTURE

The Attendance Marking module must be designed so future attendance methods can be integrated without redesigning the UI.

Future features include

- GPS Attendance
- QR Code Attendance
- Face Recognition
- Biometric Machine Integration
- IP Address Validation
- Geo Fence Validation
- Work From Home Attendance
- Multiple Shift Support

These features are NOT part of this task.

Design the UI so they can be added later.

---

# ACCESSIBILITY

Follow Bootstrap accessibility guidelines.

Buttons must contain

- Icons
- Text Labels
- Proper Button States

Confirmation modal must be keyboard accessible.

---

# SECURITY

The Attendance Widget must never trust request parameters.

Attendance always belongs to

Authenticated User

The authenticated user must come from

auth()->user()

Never allow employee ID from request for attendance marking.

---

# ROUTES

Reuse only existing Attendance routes.

Example

hrms.attendance.check-in

hrms.attendance.check-out

or equivalent existing named routes.

Do NOT create duplicate routes.

If new routes are required, follow the existing HRMS naming convention.

---

# JAVASCRIPT

Use only

Bootstrap

Existing Project JavaScript

Vanilla JavaScript

Do NOT introduce

- jQuery plugins
- Vue
- React
- Alpine
- Livewire
- External attendance libraries

---

# STYLING

Reuse existing

Bootstrap

KaiAdmin theme

Existing CSS

Existing Components

Do NOT create new CSS files unless absolutely required.

If small styling adjustments are necessary, use existing Bootstrap utility classes.

---

# EMPTY STATES

If attendance has not yet been marked

Display

Attendance Not Marked

Check In button available

If Company Holiday

Display

Today is Company Holiday

Attendance Disabled

If Weekly Off

Display

Today is Weekly Off

Attendance Disabled

If Attendance Completed

Display

Attendance Completed

Check In Time

Check Out Time

Working Hours (if provided)

---

# ERROR HANDLING

Reuse existing Flash Message component.

Support

Success

Warning

Error

Validation Errors

Do NOT introduce another notification framework.

---

# RESPONSIVENESS

Desktop

Full Attendance Widget

Tablet

Compact Attendance Widget

Mobile

Responsive Header Dropdown

Do NOT create a separate mobile Blade.

Reuse existing Bootstrap responsive utilities.

---

# NO BUSINESS LOGIC IN BLADE

Blade MUST NEVER

- Query Models
- Query Database
- Read Company Settings
- Read Holiday Table
- Determine Office Hours
- Determine Holiday
- Determine Weekly Off
- Calculate Late
- Calculate Half Day
- Calculate Working Hours
- Calculate Attendance Status
- Decide Button Visibility

Blade ONLY renders values supplied by AttendanceController.

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- Bootstrap Best Practices
- SOLID Principles
- DRY Principle
- PSR-12
- Existing HRMS Architecture

Avoid duplicated HTML by using reusable Blade partials whenever appropriate.

---

# PERFORMANCE

Attendance Widget should use a single prepared response from AttendanceService.

Avoid

- Multiple Service Calls
- Duplicate Queries
- N+1 Queries
- Model Queries inside Blade

Reuse eager-loaded relationships.

---

# BROWSER VERIFICATION

Verify manually

✓ Attendance Widget appears in Header

✓ Check In button appears before attendance

✓ Check Out button appears after Check In

✓ Confirmation modal appears before Check Out

✓ Attendance Completed state displays correctly

✓ Weekly Off disables attendance

✓ Company Holiday disables attendance

✓ Office timings display correctly

✓ Late threshold displays correctly

✓ Half Day threshold displays correctly

✓ Flash messages display correctly

✓ Loading spinner prevents duplicate clicks

✓ Existing Header layout remains unchanged

✓ Existing Sidebar remains unchanged

✓ Existing Dashboard remains unchanged

✓ Existing Employee module remains unchanged

✓ Existing Attendance History still works

✓ Existing Attendance Calendar still works

✓ Responsive layout works

✓ No broken links

✓ No undefined variables

✓ No JavaScript errors

✓ No Blade errors

---

# VERIFICATION COMMANDS

Run

```bash
docker compose exec app php artisan optimize:clear
```

Run

```bash
docker compose exec app php artisan optimize
```

Run

```bash
docker compose exec app php artisan view:cache
```

Run

```bash
docker compose exec app php artisan route:list
```

Run

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

progress.md

Include

- Attendance Header Widget completed
- Check In UI completed
- Check Out UI completed
- Attendance confirmation modal completed
- Company Settings integration completed
- Holiday integration completed
- Weekly Off integration completed
- Dynamic office timings integrated
- Dynamic late threshold integrated
- Dynamic half day threshold integrated
- Flash messages integrated
- Responsive header completed
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

This task is complete when

✓ Attendance Header Widget is visible.

✓ Employees can Check In.

✓ Employees can Check Out.

✓ Check Out confirmation modal works.

✓ Attendance state changes correctly between

- Check In
- Check Out
- Attendance Completed

✓ Office timings are displayed dynamically.

✓ Late threshold is displayed dynamically.

✓ Half Day threshold is displayed dynamically.

✓ Weekly Off is obtained dynamically from Company Settings.

✓ Company Holidays are obtained dynamically from the Holiday module.

✓ Header never contains hardcoded attendance rules.

✓ AttendanceService remains the single source of truth.

✓ AttendanceController only communicates with AttendanceServiceInterface.

✓ Blade contains no business logic.

✓ Existing Company Setting module is fully reused.

✓ Existing Holiday module is fully reused.

✓ Existing Admin Panel design is preserved.

✓ Existing Attendance History and Calendar remain functional.

✓ Existing architecture remains unchanged.

✓ Manual browser verification passes.

✓ All verification commands pass successfully.