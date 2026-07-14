# HRMS Leave Calculation Snapshot & Audit Trail (Laravel 10)

## Task

10F4B_hrms_leave_calculation_snapshot.md

---

# Objective

Prepare the Leave module for an enterprise-grade audit trail.

This phase introduces immutable leave calculation snapshots so that once a leave request is submitted and approved, its calculation can always be reproduced exactly as it was on that date.

This prevents historical leave records from changing when

- Company Leave Policy changes
- Weekly Off configuration changes
- Holidays are added or removed
- Sandwich Leave policy changes
- Financial Year changes

This phase is **Database Preparation + Integration only**.

Do NOT implement Sandwich Leave calculation yet.

That will be implemented in **10F5**.

---

# IMPORTANT

Laravel 10 only.

Reuse existing

- LeaveApply Model
- LeaveService
- LeavePolicyService
- CompanySetting
- Holiday
- EmployeeLeaveBalance

Do NOT redesign the existing architecture.

---

# SCOPE

May modify

- database/migrations
- LeaveApply model
- LeaveService (save snapshot only)
- LeavePolicyService (prepare snapshot payload only)
- progress.md

Do NOT modify

- Blade UI
- Attendance
- Payroll
- Dashboard
- Controllers (unless required for integration)

---

# DATABASE

Create a migration for the existing

```
leave_applies
```

table.

Add the following columns.

| Column | Type | Default | Purpose |
|---------|------|---------|---------|
| requested_days | decimal(5,2) | 0 | Total days between From and To |
| holiday_days | decimal(5,2) | 0 | Holidays counted |
| weekly_off_days | decimal(5,2) | 0 | Weekly offs counted |
| sandwich_days | decimal(5,2) | 0 | Extra sandwich days |
| payable_leave_days | decimal(5,2) | 0 | Final deducted leave |
| leave_calculation_json | json (nullable) | null | Complete calculation snapshot |

---

# COLUMN PURPOSE

requested_days

Number of calendar leave days requested.

---

holiday_days

Number of holidays included.

---

weekly_off_days

Number of weekly offs included.

---

sandwich_days

Additional days counted because of Sandwich Policy.

---

payable_leave_days

Actual leave deducted from balance.

This value will always be used by

- Leave Balance
- Attendance
- Payroll

Never recalculate historical records.

---

leave_calculation_json

Store the complete calculation snapshot.

---

# SNAPSHOT STRUCTURE

The JSON payload should be future-proof.

Example

```json
{
    "financial_year": "2026-27",
    "requested_days": 6,
    "holiday_days": 1,
    "weekly_off_days": 2,
    "sandwich_days": 2,
    "payable_leave_days": 6,

    "sandwich_enabled": true,
    "count_holidays_in_leave": true,
    "count_weekly_offs_in_leave": true,

    "office_weekly_offs": [
        "Sunday"
    ],

    "holiday_dates": [
        "2026-08-15"
    ],

    "weekly_off_dates": [
        "2026-08-16"
    ],

    "leave_type": "Casual Leave",

    "generated_at": "2026-07-11T10:15:00+05:30"
}
```

---

# IMPORTANT

The snapshot represents

the policy that existed

on the day the leave was applied.

Never overwrite

existing snapshots.

---

# LEAVES CREATED BEFORE THIS MIGRATION

Existing records

must remain valid.

Populate new columns

with

```
0

or

NULL
```

Do NOT attempt historical recalculation.

---

# LEAVE SERVICE

When a Leave Request is

Created

or

Updated

LeaveService should

request a calculation payload from

LeavePolicyService.

Save

- requested_days
- holiday_days
- weekly_off_days
- sandwich_days
- payable_leave_days
- leave_calculation_json

Do NOT calculate Sandwich Leave yet.

Only prepare the storage structure.

---

# LEAVE POLICY SERVICE

Add a reusable method

```
prepareCalculationSnapshot()
```

This method should return

all required fields

using current company settings.

During this phase

placeholder values

may be used

because Sandwich Leave logic

will be implemented in

10F5.

---

# IMMUTABILITY

Once a Leave Request is

Approved

its snapshot

must never change.

Even if

- Company Settings change
- Holiday list changes
- Weekly Off changes
- Sandwich policy changes

Historical records must remain unchanged.

---

# FUTURE MODULES

These columns will later be consumed by

- AttendanceService
- PayrollService
- Reports
- Dashboard

Do NOT implement those integrations now.

---

# MODEL UPDATE

Update

LeaveApply Model

fillable

casts

```
leave_calculation_json

=> array
```

No other model changes.

---

# PERFORMANCE

Avoid unnecessary JSON decoding.

Use native Laravel casts.

Do not recalculate historical records.

---

# VERIFICATION

Verify

✓ Migration runs successfully

✓ Existing leave records preserved

✓ Existing data unchanged

✓ New columns created

✓ Snapshot stored on new Leave Apply

✓ JSON cast works

✓ Existing approved leave remains unchanged

✓ No recalculation of old records

---

# VERIFICATION COMMANDS

Run

```bash
docker compose exec app php artisan migrate
```

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

- Leave snapshot migration created
- LeaveApply model updated
- Snapshot JSON integration completed
- Immutable audit trail prepared
- Verification completed

---

# SUCCESS CRITERIA

Task is complete when

- Leave table contains calculation snapshot columns.
- Existing leave records remain unchanged.
- New leave requests store calculation metadata.
- LeaveApply model supports JSON casting.
- Snapshot is immutable after approval.
- Historical leave calculations are never recalculated.
- Existing architecture remains unchanged.
- Verification commands pass successfully.

---

# NOTE FOR NEXT TASK

The next phase

```
10F5_hrms_leave_sandwich_and_duration_engine.md
```

must consume

ONLY

- CompanySetting Leave Policy
- Holiday table
- Weekly Off configuration
- Leave Calculation Snapshot

It must never hardcode company policies.