# HRMS Leave Prorata Allocation & Carry Forward Engine (Laravel 10)

## Task

10F4_hrms_leave_prorata_and_carry_forward.md

---

# Objective

Implement the automatic Leave Allocation Engine for every Financial Year.

This phase extends the Leave Policy Engine completed in **10F3**.

The system must automatically

- Allocate leave for every employee
- Handle employees joining mid-year
- Carry forward Earn Leave only
- Reset Casual and Sick Leave
- Create balances for the new financial year

This is a **Business Logic** task.

No Blade redesign.

---

# IMPORTANT

Laravel 10 only.

Reuse existing

- LeavePolicyService
- LeaveService
- LeaveType
- Employee
- EmployeeLeaveBalance
- Company Settings
- Authentication

Do NOT duplicate business logic.

---

# FINANCIAL YEAR

Entire HRMS Leave module must always use

```
1 April
↓

31 March
```

Never January–December.

Reuse Financial Year helpers created in 10F3.

---

# ALLOCATION ENGINE

Create reusable methods inside

```
LeavePolicyService
```

Example

```
allocateFinancialYear()

allocateEmployee()

allocateLeaveType()

allocateProrataLeave()

carryForwardEarnLeave()

resetFinancialYear()

generateFinancialYearBalances()
```

These methods must be reusable.

---

# MONTHLY ALLOCATION

Leave allocation must be monthly.

Example

| Leave Type | Annual | Monthly |
|------------|--------|---------|
| Casual Leave | 12 | 1 |
| Sick Leave | 12 | 1 |
| Earn Leave | 18 | 1.5 |

Do NOT hardcode.

Read annual allocation from

Leave Type.

---

# MID-YEAR JOINING

Employee joining after

```
1 April
```

must receive prorated leave.

Example

Joining Date

```
10 October 2026
```

Eligible Months

October

November

December

January

February

March

Only these months are allocated.

Do NOT allocate

April–September.

---

# EXAMPLES

Employee joins

```
1 April
```

Allocation

```
CL = 12

SL = 12

EL = 18
```

---

Employee joins

```
10 October
```

Allocation

```
CL = 6

SL = 6

EL = 9
```

(rounding according to company policy)

---

Employee joins

```
20 February
```

Allocation

```
CL = 2

SL = 2

EL = 3
```

---

# CARRY FORWARD POLICY

Carry Forward Rules

| Leave Type | Carry Forward |
|------------|---------------|
| Casual Leave | No |
| Sick Leave | No |
| Earn Leave | Yes |

Never carry forward

- Casual Leave
- Sick Leave
- LWP

Only Earn Leave.

---

# YEAR END PROCESS

On

```
31 March
```

Process

1.

Calculate

Remaining Earn Leave

2.

Create New Financial Year

3.

Carry Forward

Remaining Earn Leave

4.

Allocate

Current Year Earn Leave

5.

Reset

Casual Leave

6.

Reset

Sick Leave

---

# EXAMPLE

Financial Year

2026-27

Employee

```
CL Remaining = 4

SL Remaining = 3

EL Remaining = 8
```

New Year

2027-28

Result

```
CL = 12

SL = 12

EL = 18 + 8

=26
```

---

# DUPLICATE PROTECTION

Running allocation multiple times

must NOT create duplicate balance rows.

Use

Employee

Leave Type

Financial Year

as unique combination.

---

# RESIGNED EMPLOYEES

Skip

Inactive

Deleted

Resigned employees.

Allocate only

Active employees.

---

# NEW EMPLOYEE

When HR creates a new employee

System should automatically generate

Leave Balance

for the active Financial Year.

No manual HR action required.

---

# EDIT JOINING DATE

If Joining Date changes

Example

October

↓

June

Recalculate

Leave Allocation

for current Financial Year.

Do NOT duplicate balances.

---

# DASHBOARD

Dashboard Leave Balance

must automatically reflect

new allocations.

No manual refresh logic.

---

# APPLY LEAVE

Leave Apply page must immediately show

Allocated

Used

Remaining

after allocation.

---

# COMPANY SETTINGS

Do NOT store leave allocation here.

Company Settings remain responsible only for

- Office Time
- Working Hours
- Weekly Off
- Late Minutes
- Half Day

Leave allocation belongs to Leave Policy.

---

# SCHEDULER SUPPORT

Create reusable methods that can later be called from

Laravel Scheduler

for

```
1 April
```

yearly allocation.

Do NOT implement Scheduler itself.

Only prepare reusable service methods.

---

# PERFORMANCE

Use eager loading.

Batch insert/update where possible.

Avoid duplicate queries.

Avoid recalculating unchanged balances.

---

# AUTHORIZATION

Employees

can view only

their own leave balances.

HR/Admin

can generate allocations.

---

# VERIFICATION

Test

✓ Employee joins April

✓ Employee joins October

✓ Employee joins February

✓ Earn Leave carried forward

✓ Casual Leave reset

✓ Sick Leave reset

✓ Duplicate allocation prevented

✓ Joining Date update recalculates balance

✓ Dashboard reflects allocation

✓ Leave Apply reflects allocation

✓ Active employees only

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

- Prorata allocation engine completed
- Mid-year joining allocation completed
- Carry Forward implemented
- Earn Leave carry forward verified
- Casual Leave reset verified
- Sick Leave reset verified
- Duplicate allocation prevention completed
- Dashboard integration verified
- Leave Apply integration verified
- Verification completed

---

# SUCCESS CRITERIA

Task is complete when

- Financial Year allocation works.
- Mid-year joining is prorated automatically.
- Earn Leave carries forward.
- Casual Leave resets every Financial Year.
- Sick Leave resets every Financial Year.
- Duplicate balance creation is prevented.
- Active employees only receive balances.
- Joining date changes recalculate balances.
- Dashboard reflects balances.
- Leave Apply shows correct balances.
- Existing LeavePolicyService owns all calculations.
- Existing architecture remains unchanged.
- Verification commands pass successfully.