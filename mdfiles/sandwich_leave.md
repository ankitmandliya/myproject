# HRMS Leave Policy Configuration Migration & Seeder (Laravel 10)

## Task

10F4A_hrms_leave_policy_configuration_migration.md

---

# Objective

Prepare the database for advanced Leave Policy management before implementing the Sandwich Leave Engine.

This phase ONLY creates

- database migration(s)
- seeder(s)
- model updates

No business logic.

No Blade changes.

No controller changes.

No service changes.

This task prepares the database for

- Sandwich Leave
- Holiday Between Leave
- Weekly Off Between Leave
- Half Day Leave
- Future Leave Policies

---

# IMPORTANT

Laravel 10 only.

Reuse existing

- CompanySetting Model
- LeaveType Model
- Existing Seeders

Do NOT redesign the existing architecture.

---

# SCOPE

Create / Update only

- database/migrations
- database/seeders
- CompanySetting model (fillable/casts only if required)
- progress.md

Do NOT modify

- LeaveService
- LeavePolicyService
- Controllers
- Blade
- Routes

---

# COMPANY SETTINGS

Add the following columns to

```
company_settings
```

| Column | Type | Default | Description |
|---------|------|---------|-------------|
| sandwich_leave_enabled | boolean | false | Enable Sandwich Leave Policy |
| holiday_between_leave_count | boolean | true | Count holidays between leave dates |
| weekly_off_between_leave_count | boolean | true | Count weekly offs between leave dates |
| allow_half_day_leave | boolean | true | Allow Half Day Leave |
| leave_apply_before_days | integer | 0 | Minimum days before leave application |
| leave_cancel_before_days | integer | 0 | Minimum days before leave cancellation |

---

# COLUMN COMMENTS

Use migration comments wherever supported.

Example

```
sandwich_leave_enabled

Enable sandwich leave calculation
```

---

# DEFAULT VALUES

Default

```
sandwich_leave_enabled = false

holiday_between_leave_count = true

weekly_off_between_leave_count = true

allow_half_day_leave = true

leave_apply_before_days = 0

leave_cancel_before_days = 0
```

---

# MODEL UPDATE

Update

```
CompanySetting.php
```

Add

fillable

casts

Example

```
boolean

integer
```

No additional methods.

---

# SEEDER

Update existing

CompanySettingSeeder

OR

Create

```
LeavePolicySettingSeeder
```

Populate

```
sandwich_leave_enabled = false

holiday_between_leave_count = true

weekly_off_between_leave_count = true

allow_half_day_leave = true

leave_apply_before_days = 0

leave_cancel_before_days = 0
```

Do NOT create duplicate Company Setting rows.

Update existing row.

---

# BACKWARD COMPATIBILITY

Migration must work even if

company_settings

already contains data.

Use

```
Schema::hasColumn()
```

or equivalent safeguards if your project convention supports idempotent migrations.

Never delete existing data.

---

# FUTURE SUPPORT

These settings will later be consumed by

- LeavePolicyService
- AttendanceService
- PayrollService

Do NOT implement usage now.

Only prepare the database.

---

# VERIFICATION

Verify

✓ Migration runs successfully

✓ Existing company settings remain intact

✓ New columns exist

✓ Seeder updates existing record

✓ Seeder creates no duplicate row

✓ Model fillable updated

✓ Model casts updated

---

# VERIFICATION COMMANDS

Run

```bash
docker compose exec app php artisan migrate
```

```bash
docker compose exec app php artisan db:seed
```

```bash
docker compose exec app php artisan optimize:clear
```

```bash
docker compose exec app php artisan optimize
```

```bash
docker compose exec app php artisan about
```

```bash
docker compose exec app php artisan route:list
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

- Leave Policy configuration migration created
- Company Settings updated
- Seeder updated
- Model updated
- Migration verified
- Seeder verified
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Company Settings contains all Leave Policy configuration fields.
- Existing data remains unchanged.
- Seeder updates the existing Company Setting row.
- No duplicate company settings are created.
- Model fillable and casts are updated.
- Migration is fully backward compatible.
- Verification commands pass successfully.

---

# NOTE FOR NEXT TASK

This migration is the foundation for

```
10F5_hrms_leave_sandwich_and_duration_engine.md
```

The Sandwich Leave Engine must read these values ONLY from

```
CompanySetting
```

Never hardcode Leave Policy values.