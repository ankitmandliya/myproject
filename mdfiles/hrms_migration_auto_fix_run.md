# HRMS Migration Auto Fix & Run (Laravel 10)

## Objective

This document defines an automated process for running Laravel migrations, detecting errors, fixing issues, and ensuring successful database setup without breaking existing functionality.

---

# Environment

- Framework: Laravel 10
- Database: MySQL
- Execution Mode: CLI / Docker
- Docker Service Name (if used): app

---

# IMPORTANT RULES (MANDATORY)

## 1. Do NOT Modify Existing System
- Do NOT modify existing tables
- Do NOT modify existing migrations
- Do NOT modify authentication system
- Do NOT modify existing CRUD logic

Only migration-related fixes are allowed.

---

## 2. Laravel Version Constraint
- Use Laravel 10 only
- Do NOT use Laravel 11/12 features

---

## 3. Security Rules
- Prevent SQL injection
- Use Eloquent or Schema Builder only
- Follow Laravel best practices
- Validate schema correctness before applying

---

# EXECUTION STEPS

## STEP 1: Run Migrations

If local environment:
```bash
php artisan migrate
```

If Docker environment:
```bash
docker compose exec app php artisan migrate
```

---

## STEP 2: Error Detection

If migration fails:

- Capture full error message
- Identify failing migration file
- Identify root cause (SQL, FK, datatype, default value, etc.)

---

## STEP 3: Allowed Fix Types

Only fix migration-related issues:

### Allowed Fixes:
- Invalid SQL defaults (e.g. CURRENT_DATE on DATE columns)
- Foreign key constraint order issues
- Missing indexes
- Incorrect enum definitions
- Datatype mismatches
- Timestamp issues
- Null constraint problems

### NOT Allowed:
- Changing business logic
- Modifying existing tables
- Editing controllers or models unrelated to migration

---

## STEP 4: Apply Fix

- Fix only the affected migration file
- Ensure Laravel 10 compatibility
- Ensure foreign key integrity
- Ensure rollback support is valid

---

## STEP 5: Retry Migration

Run again:

```bash
php artisan migrate
```

or

```bash
docker compose exec app php artisan migrate
```

Repeat until successful.

---

## STEP 6: Verification Checklist

Ensure:

- All tables are created successfully
- No SQL errors remain
- Foreign keys are correctly linked
- No existing tables were modified
- Migration rollback works correctly

---

## STEP 7: Progress Tracking

After successful execution, update:

```
progress.md
```

Include:

- Migration execution status
- Errors found (if any)
- Fixes applied
- Final success confirmation
- Any warnings or notes

---

# FINAL GOAL

- Clean migration execution
- No SQL errors
- Fully working database schema
- Production-ready Laravel 10 setup