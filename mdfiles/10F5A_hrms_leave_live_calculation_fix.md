# 10F5A_hrms_leave_live_calculation_fix.md

## Module
HRMS → Leave Management

## Objective

Fix the leave calculation engine so that every leave request is calculated dynamically using the central `LeavePolicyService` instead of relying on user-entered values or Blade calculations.

This task ensures that Requested Days, Holidays, Weekly Offs, Sandwich Leave, Half Day, Payable Days, Remaining Balance, and Balance After Approval are always correct before the employee submits the leave request.

---

# Current Problems

Examples observed:

- 27 Jul 2026 → 29 Jul 2026 shows Total Days = 1 instead of 3.
- Requested Days are incorrect.
- Final Payable Days are incorrect.
- Remaining Balance is not refreshed.
- Leave calculations are not updating immediately after changing dates.
- UI still trusts previous values instead of recalculating.

---

# Source of Truth

ALL calculations MUST come from:

```
LeavePolicyService
```

No calculations should exist inside:

- Blade
- JavaScript
- Controller
- Request class

The UI should simply display the values returned by the service.

---

# Live Calculation Endpoint

Create a calculation endpoint.

Example:

```
POST /hrms/leave/calculate
```

or

```
GET /hrms/leave/calculate
```

Accept:

```
employee_id
leave_type_id

from_date

to_date

half_day

half_day_session

emergency_leave
```

Return JSON:

```json
{
    "requested_days":3,
    "holiday_days":0,
    "weekly_off_days":0,
    "sandwich_days":0,
    "payable_leave_days":3,
    "remaining_balance":9,
    "balance_after_approval":6,
    "warning":null
}
```

---

# Trigger Live Calculation

Whenever ANY of these fields change:

- Leave Type
- From Date
- To Date
- Half Day
- Half Day Session
- Emergency Leave

Immediately call the calculation endpoint.

No page refresh.

---

# Requested Days Calculation

Always calculate using Carbon.

Example:

```
From:
27 Jul

To:
29 Jul
```

Result

```
Requested Days = 3
```

Inclusive counting.

Never:

```
diffInDays()
```

without adding one.

Correct:

```
diffInDays()+1
```

---

# Holiday Calculation

Read ONLY from

```
holidays
```

table.

Support

- single day holiday
- multi-day holiday

Example

```
10 Jul

11 Jul Holiday

12 Jul Holiday

13 Jul
```

Result

```
Holiday Days = 2
```

---

# Weekly Off Calculation

Read dynamically from

```
company_setting
```

Never hardcode Saturday or Sunday.

Support

```
Sunday

Saturday,Sunday

Friday

etc.
```

Example

```
Fri

Sat

Sun

Mon
```

Weekly Off Days

```
2
```

---

# Sandwich Leave

Read

```
company_setting.sandwich_leave_enabled
```

If disabled

```
Weekly Off

Holiday

NOT payable
```

If enabled

```
Holiday between leave

Weekly Off between leave

Become payable.
```

---

# Half Day

When employee selects

```
Half Day
```

Then

```
Requested Days = 0.5

Payable Days = 0.5
```

Never 1.

Half Day session:

```
First Half

Second Half
```

must also be returned.

---

# Emergency Leave

When enabled

Display

```
Emergency Leave

YES
```

No special calculation unless future business rules require it.

---

# Final Payable Days

Formula

```
Requested

-

Excluded Holidays

-

Excluded Weekly Off

+

Sandwich Days

=

Final Payable Days
```

depending on company settings.

Never editable.

---

# Remaining Balance

Return current balance.

Example

```
Allocated

12

Used

3

Remaining

9
```

---

# Balance After Approval

Display live.

Example

```
Remaining

9

Payable

3

Balance After Approval

6
```

---

# Insufficient Balance

If payable days exceed balance

Display

```
Insufficient Leave Balance
```

Disable

```
Submit Leave
```

button.

---

# Leave Without Pay

If leave type

```
Leave Without Pay
```

Then

Balance validation skipped.

Payable Days still calculated.

Remaining Balance unchanged.

---

# Calendar Preview

Highlight selected dates.

Legend

```
Requested

Holiday

Weekly Off

Sandwich

Half Day
```

Live update while changing dates.

---

# Review Step

Never use cached values.

Always reload latest calculation.

Show

```
Requested Days

Holiday Days

Weekly Off Days

Sandwich Days

Final Payable Days

Remaining Balance

Balance After Approval
```

---

# Validation

Reject

```
To Date < From Date
```

Reject

```
Future invalid dates
```

Reject

```
Missing leave type
```

Reject

```
Inactive employee
```

Reject

```
No leave balance
```

Reject

```
Leave exceeds allowed limit
```

---

# Controller

Controller should never calculate.

Only

```
LeavePolicyService

↓

Return JSON

↓

Store Snapshot
```

---

# Blade

Remove every calculation like

```
$totalDays

$dateDiff

remaining

payable
```

Blade only renders returned values.

---

# JavaScript

JavaScript should only

```
Collect fields

↓

Call endpoint

↓

Render response
```

No calculation logic.

---

# Performance

Debounce

```
300ms
```

when dates change.

Prevent duplicate AJAX requests.

---

# Security

Only authenticated users.

Employees calculate only their own leave.

HR/Admin may calculate for any employee.

---

# Smoke Tests

Verify:

- 27 Jul → 29 Jul returns Requested Days = 3.
- Same-day leave returns 1 day.
- Half Day returns 0.5.
- Weekly Off excluded correctly.
- Holiday excluded correctly.
- Sandwich ON includes sandwich days.
- Sandwich OFF excludes sandwich days.
- Remaining Balance updates correctly.
- Balance After Approval updates correctly.
- Leave Without Pay ignores balance validation.
- Submit disabled on insufficient balance.
- Review page matches live calculation exactly.
- Snapshot stores identical values.
- No calculation performed in Blade.
- No calculation performed in JavaScript.
- Controller delegates all calculations to LeavePolicyService.

---

# Required Verification

Run:

```
php artisan optimize:clear

php artisan optimize

php artisan view:cache

php artisan route:list

php artisan about

php artisan migrate

find app -name "*.php" -exec php -l {} \;
```

---

# Expected Result

The Leave Apply module becomes fully dynamic and policy-driven.

Every value displayed to the user is calculated from a single source (`LeavePolicyService`), ensuring consistency between the UI, stored snapshot, approval process, reports, and payroll.