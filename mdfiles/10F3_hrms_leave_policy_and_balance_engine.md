# HRMS Leave Policy & Leave Balance Engine (Laravel 10)

## Task

10F3_hrms_leave_policy_and_balance_engine.md

---

# Objective

Implement the complete Leave Policy Engine and Employee Leave Balance Engine.

This phase creates the foundation for the remaining Leave Management system.

This is a business logic phase.

It will be used later by

- Leave Apply
- Attendance
- Dashboard
- Payroll
- Reports

---

# IMPORTANT

Laravel 10 only.

Reuse existing

- LeaveService
- LeaveController
- LeaveType
- Holiday
- Company Setting
- Employee
- Existing Authentication

Do NOT redesign the project architecture.

---

# ARCHITECTURE

Create a dedicated

```
LeavePolicyService
```

Create

```
app/Contracts/LeavePolicyServiceInterface.php

app/Services/LeavePolicyService.php
```

Purpose

All leave calculations must remain inside

LeavePolicyService.

LeaveService should only

- Apply Leave
- Update Leave
- Cancel Leave
- Approve Leave
- Reject Leave

LeaveService must consume

LeavePolicyServiceInterface

instead of implementing policy calculations itself.

---

# DATABASE

Create

```
employee_leave_balances
```

Columns

```
id

employee_id

leave_type_id

financial_year

allocated

used

remaining

carry_forward

created_at

updated_at
```

Relationships

Employee

↓

EmployeeLeaveBalance

↓

LeaveType

---

# FINANCIAL YEAR

Entire Leave module must use

```
1 April

↓

31 March
```

Never calculate leave using calendar year.

Create reusable methods

```
currentFinancialYear()

financialYearStart()

financialYearEnd()
```

inside

LeavePolicyService.

---

# LEAVE POLICY

Every Leave Type should support

- Annual Allocation
- Monthly Allocation
- Carry Forward
- Paid / Unpaid
- Sandwich Applicable
- Half Day Allowed
- Requires Approval

Reuse existing Leave Type table where possible.

Do NOT hardcode values.

---

# LEAVE BALANCE CREATION

When a new Financial Year starts

Create Leave Balance records

for every active employee.

For each Leave Type

Create

Allocated

Used

Remaining

Carry Forward

No duplicate balance rows.

---

# MID-YEAR JOINING

Employee joining after

1 April

must receive prorated leave.

Example

Joining Date

```
10 October
```

Eligible months

October

November

December

January

February

March

Allocate only those months.

Do NOT allocate April–September.

---

# MONTHLY ALLOCATION

Use monthly allocation.

Example

Casual Leave

12/year

↓

1/month

Sick Leave

12/year

↓

1/month

Earn Leave

18/year

↓

1.5/month

Round according to company policy.

---

# CARRY FORWARD

Carry Forward

Casual Leave

×

Sick Leave

×

Earn Leave

✓

Example

31 March

Employee

EL Remaining

8

1 April

New Allocation

18

Carry Forward

8

Total

26

Only Earn Leave carries forward.

---

# LEAVE BALANCE METHODS

Implement

```
allocateLeave()

calculateRemaining()

consumeLeave()

restoreLeave()

carryForward()

getBalance()

getFinancialYear()

getEmployeeBalances()
```

These methods must be reusable.

---

# LEAVE APPLICATION VALIDATION

Before Leave Apply

System must verify

Remaining Leave.

Display

Allocated

Used

Remaining

If Remaining

< Requested Leave

Reject application.

---

# LEAVE BALANCE UPDATE

When Leave is Approved

Update

Used

Remaining

When Leave is Cancelled

Restore balance.

When Leave is Rejected

Do NOT consume balance.

---

# EMPLOYEE DASHBOARD

Display

Casual Leave

Allocated

Used

Remaining

Sick Leave

Allocated

Used

Remaining

Earn Leave

Allocated

Used

Remaining

Dashboard must consume

LeavePolicyService

only.

---

# LEAVE APPLY PAGE

Display

Leave Balance card

Example

```
Casual Leave

Allocated : 12

Used : 5

Remaining : 7
```

Update dynamically when

Leave Type changes.

---

# API RESPONSE

Return

```
Leave Type

Allocated

Used

Remaining

Carry Forward

Financial Year
```

for every leave type.

---

# COMPANY SETTINGS

Continue using Company Settings only for

- Office Time
- Office End
- Weekly Off
- Late Minutes
- Half Day
- Attendance Settings

Leave allocation must NOT be stored there.

---

# PERFORMANCE

Avoid N+1 queries.

Use eager loading.

Do not recalculate balances repeatedly.

Cache Financial Year helper if appropriate.

---

# SECURITY

Employees

can view only

their own Leave Balance.

HR/Admin

can view everyone.

---

# VERIFICATION

Test

✓ New Financial Year allocation

✓ Mid-year joining allocation

✓ Carry Forward

✓ Remaining Leave calculation

✓ Used Leave calculation

✓ Cancel restores balance

✓ Reject does not consume balance

✓ Dashboard balance

✓ Leave Apply balance

✓ Employee authorization

✓ HR authorization

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
docker compose exec app php artisan migrate
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

PHP lint

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

- LeavePolicyService created
- Leave Balance Engine completed
- Financial Year engine completed
- Monthly allocation completed
- Mid-year joining allocation completed
- Carry Forward completed
- Employee Leave Balance table created
- Dashboard integration completed
- Leave Apply balance integration completed
- Verification completed

---

# SUCCESS CRITERIA

Task is complete when

- Financial Year is fully implemented.
- Employee Leave Balance table exists.
- Leave balances are automatically generated.
- Mid-year joining is prorated correctly.
- Only Earn Leave carries forward.
- Leave balances display on Dashboard.
- Leave balances display on Apply Leave page.
- Remaining leave is validated before applying.
- Leave approval updates balances correctly.
- Cancelled leave restores balance.
- Employees can only view their own balances.
- HR/Admin can view all balances.
- Existing architecture remains unchanged.
- LeavePolicyService owns all leave business rules.
- All verification commands pass successfully.