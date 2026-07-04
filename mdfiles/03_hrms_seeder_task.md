# HRMS Seeder Task (Laravel 10)

## Objective

This document defines the Seeder layer for the HRMS system.

It is responsible for initializing ONLY master/configuration data required for system bootstrap.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version Constraint
- Use Laravel 10 only
- Do NOT use Laravel 11 or 12 features

---

## 2. Seeder Safety Rules
- NEVER modify existing tables structure
- NEVER delete existing data
- Seeders MUST be idempotent (safe to re-run)
- Use updateOrCreate() wherever applicable

---

## 3. Seeder Scope (VERY IMPORTANT)

Only seed MASTER DATA:

✔ RoleMaster  
✔ RolePermission  
✔ CompanySetting  

DO NOT seed:
- Users
- Attendance
- LeaveApply
- SalarySlip
- Any transactional data

---

# SEEDER EXECUTION ORDER

1. RoleMasterSeeder
2. RolePermissionSeeder
3. CompanySettingSeeder

---

# 1. ROLE MASTER SEEDER

## Table: role_master

## Roles to create

- Admin
- HR
- Manager
- Employee

## Rules
- Use updateOrCreate()
- Must NOT create duplicates

---

# 2. ROLE PERMISSION SEEDER (CRITICAL)

## Purpose
Assign permissions to each role.

---

# PERMISSION LIST (GLOBAL SYSTEM PERMISSIONS)

## Employee Module
- employee.view
- employee.create
- employee.edit
- employee.delete
- employee.view.self

## Attendance Module
- attendance.view
- attendance.manage
- attendance.view.self

## Leave Module
- leave.view
- leave.manage
- leave.approve
- leave.reject
- leave.apply
- leave.view.self

## Salary Module
- salary.view
- salary.generate
- salary.manage
- salary.view.self

## Role Management
- role.view
- role.create
- role.edit
- role.delete

## Settings Module
- settings.view
- settings.manage

---

# ROLE → PERMISSION MAPPING (STRICT)

## 👑 Admin (Full Access)
- ALL permissions

---

## 🧑‍💼 HR
- employee.view
- employee.create
- employee.edit

- attendance.view
- attendance.manage

- leave.view
- leave.manage
- leave.approve
- leave.reject

- salary.view
- salary.generate

- settings.view

---

## 🧑‍🔧 Manager
- employee.view
- attendance.view

- leave.view
- leave.approve
- leave.reject

- salary.view

---

## 👷 Employee (Self Access Only)
- employee.view.self
- attendance.view.self
- leave.apply
- leave.view.self
- salary.view.self

---

## RULES FOR IMPLEMENTATION

- Admin role must be assigned ALL permissions
- HR, Manager, Employee must follow exact mapping
- Use RoleMaster → RolePermission relationship
- Use updateOrCreate() for safe re-run
- Do NOT hardcode role IDs

---

# 3. COMPANY SETTING SEEDER

## Table: company_setting

## Default Values

- office_start_time = 10:00:00
- office_end_time = 18:00:00
- late_after_minutes = 15
- half_day_after_minutes = 120
- salary_date = 5
- weekly_off = Sunday

## Rules
- Only ONE record should exist
- Use updateOrCreate()

---

# EXCLUDED SEEDERS

❌ LeaveTypeSeeder (already exists in DB)  
❌ UserSeeder  
❌ AttendanceSeeder  
❌ LeaveApplySeeder  
❌ SalarySlipSeeder  

---

# TECHNICAL REQUIREMENTS

## Best Practices
- Use DB transactions where needed
- Ensure idempotency (safe re-run)
- Avoid duplicate inserts
- Maintain referential integrity

---

# EXECUTION COMMANDS

Run all seeders:

```bash
php artisan db:seed
```

Run specific seeder:

```bash
php artisan db:seed --class=RoleMasterSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=CompanySettingSeeder
```

---

# SUCCESS CRITERIA

Seeder is complete when:

✔ All roles are created correctly  
✔ Role-permission mapping is enforced  
✔ Company settings exist as single record  
✔ No duplicate data on re-run  
✔ LeaveType table remains untouched  
✔ System is ready for RBAC enforcement