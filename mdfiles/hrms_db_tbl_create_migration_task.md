# HRMS Database Migration Plan

## Objective

This document defines the database migration plan for the HRMS module of the Corp Panel.

The goal is to extend the existing database without modifying any existing tables or breaking the current application.

---

# Important Rules

The following rules are mandatory and must be followed throughout the implementation.

## Rule 1

Do NOT modify any existing table.

Do NOT:

- Add new columns
- Remove existing columns
- Rename columns
- Change column data types
- Change indexes
- Change existing relationships

Existing functionality must continue to work without any changes.

---

## Rule 2

Do NOT modify existing authentication.

The following features are already working:

- User Registration
- Login
- Forgot Password
- OTP Verification
- Existing CRUD Operations

These modules must remain unchanged.

---

## Rule 3

Only create new migration files.

Every new feature must be implemented by creating new database tables.

Do not update or recreate existing migrations.

---

# Existing Tables

These tables already exist in the project.

| Table | Status |
|--------|--------|
| users | Existing |
| leave_types | Existing |
| holidays | Existing |
| migrations | Laravel Default |

These tables must remain exactly as they are.

---

# New Tables to Create

The following tables need to be created.

## 1. user_details

### Purpose

Stores complete employee profile information.

### Relationship

```
users.id
    ↓
user_details.user_id
```

Migration Name

```
create_user_details_table
```

---

## 2. role_master

### Purpose

Stores all available user roles.

Example

- Super Admin
- HR
- Manager
- Employee

Migration Name

```
create_role_master_table
```

---

## 3. user_role

### Purpose

Assigns a role to a user.

Relationship

```
users.id
        ↓
user_role.user_id

role_master.id
        ↓
user_role.role_id
```

Migration Name

```
create_user_role_table
```

---

## 4. role_permission

### Purpose

Stores permissions assigned to each role.

Relationship

```
role_master.id
        ↓
role_permission.role_id
```

Migration Name

```
create_role_permission_table
```

---

## 5. attendance

### Purpose

Stores daily employee attendance.

Relationship

```
users.id
      ↓
attendance.user_id
```

Migration Name

```
create_attendance_table
```

---

## 6. leave_apply

### Purpose

Stores leave requests submitted by employees.

Relationship

```
users.id
        ↓
leave_apply.user_id

leave_types.id
        ↓
leave_apply.leave_type_id
```

Migration Name

```
create_leave_apply_table
```

---

## 7. salary_slip

### Purpose

Stores monthly salary records.

Relationship

```
users.id
      ↓
salary_slip.user_id
```

Migration Name

```
create_salary_slip_table
```

---

## 8. company_setting

### Purpose

Stores global HRMS configuration.

Examples

- Office Start Time
- Office End Time
- Late Arrival Rules
- Half Day Rules
- Salary Generation Date

Migration Name

```
create_company_setting_table
```

---

# Database Relationship Overview

```
users
│
├── user_details
├── attendance
├── leave_apply
├── salary_slip
└── user_role
        │
        ├── role_master
        └── role_permission

leave_types
│
└── leave_apply

holidays

company_setting
```

---

# Migration Execution Order

Create migrations in the following order.

1. create_user_details_table
2. create_role_master_table
3. create_user_role_table
4. create_role_permission_table
5. create_attendance_table
6. create_leave_apply_table
7. create_salary_slip_table
8. create_company_setting_table

---

# Expected Deliverables

The implementation should include only the following:

- New Laravel migration files
- Proper foreign key relationships
- Appropriate indexes
- Rollback support
- Laravel migration best practices

---

# Out of Scope

The following tasks are NOT part of this document.

- Models
- Controllers
- Routes
- Views
- Blade Files
- APIs
- CRUD Logic
- Authentication Changes
- Business Logic
- Seeders
- Factories

These will be implemented in separate tasks.

---

# Success Criteria

The task will be considered complete when:

- No existing table has been modified.
- All new tables are created successfully.
- Foreign keys are correctly configured.
- Migrations run successfully using:

```bash
php artisan migrate
```

- Rollback works successfully using:

```bash
php artisan migrate:rollback
```

- Existing authentication and CRUD continue to function without any code changes.