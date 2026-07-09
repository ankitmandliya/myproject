# HRMS Attendance Calendar Fallback Task (Laravel 10)

## Objective

Fix the Attendance Calendar page so it always renders during development and testing.

If no attendance records exist for the selected month, the calendar should display temporary demo data.

This fallback is ONLY for development/testing.

Real attendance data must always take priority.

---

# IMPORTANT RULES

## Laravel Version

- Laravel 10 only

---

# SCOPE

Only modify

- AttendanceController
- Attendance Blade (calendar only)
- progress.md

Do NOT modify

- AttendanceService business logic
- Database
- Models
- Migrations
- Attendance calculations

---

# FALLBACK RULE

AttendanceService remains the source of truth.

Controller should first request

- Monthly Attendance
- Holidays
- Weekly Offs

If attendance data exists

Use it.

If attendance data is empty

Generate temporary demo calendar data.

---

# DEMO DATA RULE

Generate demo records ONLY in memory.

Never insert demo records into the database.

Never create seeders.

Never create migrations.

---

# SAMPLE DEMO DATA

Current Month

Example

Week 1

Mon → Present

Tue → Present

Wed → Late

Thu → Present

Fri → Half Day

Sat → Present

Sun → Holiday

Week 2

Mix

- Present
- Leave
- Absent
- Holiday

Week 3

Mix

- Present
- Late
- Half Day

Week 4

Mix

- Present
- Present
- Leave

The calendar should look realistic.

---

# STATUS COLORS

Present

Bootstrap Success

Late

Bootstrap Warning

Absent

Bootstrap Danger

Leave

Bootstrap Primary

Holiday

Bootstrap Secondary

Weekend

Bootstrap Dark

Reuse existing badge colors.

---

# CONTROLLER RULE

Controller should expose

```
$calendarData

$isDemoCalendar
```

Blade must never generate demo data.

---

# BLADE

If

```
$isDemoCalendar == true
```

Display small Bootstrap badge

```
Demo Calendar Data
```

above the calendar.

Hide automatically when real attendance exists.

---

# FUTURE READY

When attendance records are available

The demo calendar must disappear automatically.

No code changes should be required.

---

# PERFORMANCE

Generate demo data only once.

Do not create unnecessary loops.

---

# CODE QUALITY

No business logic inside Blade.

No database writes.

No fake models.

No hardcoded HTML duplication.

---

# BROWSER VERIFICATION

Verify

✓ Calendar renders

✓ Month grid renders

✓ Mixed attendance statuses display

✓ Demo badge visible

✓ No database records created

✓ Existing attendance overrides demo

✓ Responsive layout

✓ Existing design preserved

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

- Attendance calendar fallback added
- Demo calendar generation implemented
- No database writes
- Existing attendance takes priority
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Calendar always renders.
- Demo data is shown only when attendance is empty.
- No demo records are stored in the database.
- Real attendance automatically replaces demo data.
- Existing architecture remains unchanged.
- No business logic is added to Blade.
- Browser verification passes.