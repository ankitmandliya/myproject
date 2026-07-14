# HRMS Leave Sandwich & Duration Engine (Laravel 10)

## Task

10F5_hrms_leave_sandwich_and_duration_engine.md

---

# Objective

Implement the complete Leave Duration Engine.

This phase calculates the actual leave days that will be deducted from an employee's balance.

The engine must support

- Leave Duration
- Sandwich Leave
- Weekly Off Detection
- Holiday Detection
- Half Day Leave
- Company Leave Policies
- Leave Snapshot Integration

No Blade redesign.

No Attendance implementation.

No Payroll implementation.

---

# IMPORTANT

Laravel 10 only.

Reuse existing

- LeavePolicyService
- LeaveService
- LeaveApply
- EmployeeLeaveBalance
- Holiday
- CompanySetting
- Leave Snapshot (10F4B)

Never duplicate business logic.

---

# LEAVE ENGINE

All calculations must exist only inside

```
LeavePolicyService
```

Create reusable methods.

Example

calculateLeaveDuration()

calculateRequestedDays()

calculateHolidayDays()

calculateWeeklyOffDays()

calculateSandwichDays()

calculatePayableLeaveDays()

prepareCalculationSnapshot()

---

# SINGLE SOURCE OF TRUTH

All calculations must return ONE array.

Example

[
    'requested_days' => 6,
    'holiday_days' => 1,
    'weekly_off_days' => 2,
    'sandwich_days' => 2,
    'payable_leave_days' => 6,
    'holiday_dates' => [],
    'weekly_off_dates' => [],
    'sandwich_dates' => [],
]

Never calculate the same thing twice.

Every module must reuse this result.

---

# REQUESTED DAYS

Calculate inclusive days.

Example

1 Aug

↓

5 Aug

=

5 Days

Never exclude start/end dates.

---

# HOLIDAY DETECTION

Read only

holiday table

Use existing Holiday model.

Support

Single-day holiday

Multi-day holiday

Recurring holiday if already supported.

---

# WEEKLY OFF DETECTION

Read only

company_setting

Weekly Off configuration.

Support

Sunday

Saturday+Sunday

Custom future configurations.

---

# SANDWICH POLICY

Read only

company_setting

sandwich_leave_enabled

If disabled

No sandwich calculation.

If enabled

calculate sandwich leave.

---

# HOLIDAY POLICY

Read

holiday_between_leave_count

If enabled

holiday between leave counts.

Otherwise ignore.

---

# WEEKLY OFF POLICY

Read

weekly_off_between_leave_count

If enabled

weekly off counts.

Otherwise ignore.

---

# SANDWICH EXAMPLE

Friday

Leave

Saturday

Weekly Off

Sunday

Weekly Off

Monday

Leave

If enabled

Payable

4

If disabled

Payable

2

---

# HOLIDAY EXAMPLE

14 Aug

Leave

15 Aug

Holiday

16 Aug

Leave

If enabled

Payable

3

If disabled

Payable

2

---

# HALF DAY

Read

allow_half_day_leave

If disabled

Reject Half Day request.

Support

First Half

Second Half

Deduction

0.5

---

# SAME DAY HALF DAY

Example

10 Aug

↓

10 Aug

Half Day

Deduction

0.5

---

# MULTI DAY HALF DAY

Not allowed.

Reject validation.

---

# LEAVE BALANCE VALIDATION

Use

payable_leave_days

Reject

If

Remaining Balance

<

Payable Leave

---

# LEAVE SNAPSHOT

Populate

requested_days

holiday_days

weekly_off_days

sandwich_days

payable_leave_days

leave_calculation_json

using calculation result.

---

# IMMUTABILITY

Approved Leave

must never recalculate.

Always use stored snapshot.

---

# LEAVE APPLY PAGE

Without redesigning Blade

display

Requested Days

Holiday Days

Weekly Off Days

Sandwich Days

Final Payable Days

using existing UI.

---

# UPDATE LEAVE

Pending Leave

must recalculate.

Approved Leave

must not recalculate.

---

# PERFORMANCE

Single holiday query.

Single company setting query.

No repeated Carbon loops.

Reuse arrays.

---

# REUSABLE METHODS

Attendance

Payroll

Reports

must later reuse

calculatePayableLeaveDays()

Do not duplicate logic.

---

# AUTHORIZATION

Employee

Own Leave only.

HR/Admin

All employees.

Reuse existing middleware.

---

# VALIDATION

Reject

From Date > To Date

Reject

Half Day with multiple dates

Reject

Negative duration

Reject

Balance exceeded

Reject

Inactive employee

Reject

Invalid Leave Type

---

# VERIFICATION

Verify

✓ Requested days calculation

✓ Holiday detection

✓ Weekly off detection

✓ Sandwich enabled

✓ Sandwich disabled

✓ Holiday counting enabled

✓ Holiday counting disabled

✓ Weekly off counting enabled

✓ Weekly off counting disabled

✓ Half Day

✓ Balance validation

✓ Snapshot saved

✓ Approved leave immutable

✓ Pending leave recalculates

---

# VERIFICATION COMMANDS

Run

docker compose exec app php artisan optimize:clear

docker compose exec app php artisan optimize

docker compose exec app php artisan view:cache

docker compose exec app php artisan route:list

docker compose exec app php artisan about

find app -name "*.php" -exec php -l {} \;

---

# PROGRESS UPDATE

Update

progress.md

Include

- Leave Duration Engine completed
- Holiday Engine completed
- Weekly Off Engine completed
- Sandwich Leave Engine completed
- Half Day support completed
- Snapshot integration completed
- Balance validation completed
- Verification completed

---

# SUCCESS CRITERIA

Task is complete when

✓ Leave duration is correct.

✓ Holidays are detected.

✓ Weekly offs are detected.

✓ Sandwich Leave works.

✓ Half Day works.

✓ Leave balances use payable_leave_days.

✓ Snapshot stores calculations.

✓ Approved leave never recalculates.

✓ Pending leave recalculates.

✓ Company settings drive all policies.

✓ Existing architecture remains unchanged.

✓ Verification commands pass successfully.