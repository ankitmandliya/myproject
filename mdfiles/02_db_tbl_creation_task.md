# HRMS Database Schema

## Project

Corp Panel - HRMS Module

---

# Existing Tables

The following tables already exist and MUST NOT be modified.

- users
- leave_types
- holidays

---

# 1. user_details

## Purpose

Stores complete employee profile information.

## Migration

create_user_details_table

| Column | Type | Null | Default | Key | Notes |
|----------|-------------|------|----------|------|----------------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK | Primary Key |
| user_id | BIGINT UNSIGNED | NO | - | FK, UK | Reference users.id (One-to-One) |
| emp_code | VARCHAR(20) | NO | - | UK | Employee Code |
| first_name | VARCHAR(100) | NO | - | | |
| last_name | VARCHAR(100) | YES | NULL | | |
| gender | ENUM('Male','Female','Other') | YES | NULL | | |
| dob | DATE | YES | NULL | | |
| joining_date | DATE | NO | CURRENT_DATE | | |
| department | VARCHAR(100) | YES | NULL | | |
| designation | VARCHAR(100) | YES | NULL | | |
| basic_salary | DECIMAL(10,2) | NO | 0.00 | | |
| address | TEXT | YES | NULL | | |
| aadhaar | VARCHAR(20) | YES | NULL | UK | |
| pan | VARCHAR(20) | YES | NULL | UK | |
| profile_photo | VARCHAR(255) | YES | NULL | | File Path |
| status | BOOLEAN | NO | 1 | | Active / Inactive |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | | |
| updated_at | TIMESTAMP | YES | NULL | | |

Relationship

```
users.id
      ↓
user_details.user_id
```

---

# 2. role_master

## Migration

create_role_master_table

| Column | Type | Null | Default | Key | Notes |
|----------|----------------|------|----------|------|----------------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK | |
| role_name | VARCHAR(100) | NO | - | UK | |
| description | TEXT | YES | NULL | | |
| status | BOOLEAN | NO | 1 | | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | | |
| updated_at | TIMESTAMP | YES | NULL | | |

---

# 3. user_role

## Migration

create_user_role_table

| Column | Type | Null | Default | Key |
|----------|----------------|------|----------|------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK |
| user_id | BIGINT UNSIGNED | NO | - | FK |
| role_id | BIGINT UNSIGNED | NO | - | FK |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

Relationship

```
users.id
      ↓
user_role.user_id

role_master.id
      ↓
user_role.role_id
```

---

# 4. role_permission

## Migration

create_role_permission_table

| Column | Type | Null | Default | Key |
|----------|----------------|------|----------|------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK |
| role_id | BIGINT UNSIGNED | NO | - | FK |
| permission_name | VARCHAR(100) | NO | - | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

Example Permissions

- employee.view
- employee.create
- employee.edit
- employee.delete
- attendance.manage
- leave.manage
- payroll.manage

---

# 5. attendance

## Migration

create_attendance_table

| Column | Type | Null | Default | Key |
|----------|----------------|------|----------|------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK |
| user_id | BIGINT UNSIGNED | NO | - | FK |
| attendance_date | DATE | NO | - | |
| check_in | TIME | YES | NULL | |
| check_out | TIME | YES | NULL | |
| working_hours | DECIMAL(5,2) | YES | 0 | |
| late_minutes | INTEGER | NO | 0 | |
| half_day | BOOLEAN | NO | 0 | |
| status | ENUM('Present','Absent','Leave','Holiday') | NO | Present | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | YES | NULL | |

Unique Index

```
user_id + attendance_date
```

---

# 6. leave_apply

## Migration

create_leave_apply_table

| Column | Type | Null | Default | Key |
|----------|----------------|------|----------|------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK |
| user_id | BIGINT UNSIGNED | NO | - | FK |
| leave_type_id | BIGINT UNSIGNED | NO | - | FK |
| from_date | DATE | NO | - | |
| to_date | DATE | NO | - | |
| total_days | INTEGER | NO | 1 | |
| reason | TEXT | YES | NULL | |
| status | ENUM('Pending','Approved','Rejected') | NO | Pending | |
| approved_by | BIGINT UNSIGNED | YES | NULL | FK |
| approved_at | TIMESTAMP | YES | NULL | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | YES | NULL | |

Relationships

```
users.id
      ↓
leave_apply.user_id

leave_types.id
      ↓
leave_apply.leave_type_id
```

---

# 7. salary_slip

## Migration

create_salary_slip_table

| Column | Type | Null | Default | Key |
|----------|----------------|------|----------|------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK |
| user_id | BIGINT UNSIGNED | NO | - | FK |
| month | TINYINT | NO | - | |
| year | YEAR | NO | - | |
| basic_salary | DECIMAL(10,2) | NO | 0 | |
| allowance | DECIMAL(10,2) | NO | 0 | |
| deduction | DECIMAL(10,2) | NO | 0 | |
| overtime | DECIMAL(10,2) | NO | 0 | |
| leave_deduction | DECIMAL(10,2) | NO | 0 | |
| net_salary | DECIMAL(10,2) | NO | 0 | |
| pdf_path | VARCHAR(255) | YES | NULL | |
| generated_at | TIMESTAMP | YES | NULL | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

Unique Index

```
user_id + month + year
```

---

# 8. company_setting

## Migration

create_company_setting_table

| Column | Type | Null | Default | Key |
|----------|----------------|------|----------|------|
| id | BIGINT UNSIGNED | NO | Auto Increment | PK |
| office_start_time | TIME | NO | 10:00:00 | |
| office_end_time | TIME | NO | 18:00:00 | |
| late_after_minutes | INTEGER | NO | 15 | |
| half_day_after_minutes | INTEGER | NO | 120 | |
| salary_date | TINYINT | NO | 5 | |
| weekly_off | VARCHAR(50) | NO | Sunday | |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | YES | NULL | |

---

# Foreign Key Summary

| Table | Foreign Key | References |
|---------|------------|------------|
| user_details | user_id | users.id |
| user_role | user_id | users.id |
| user_role | role_id | role_master.id |
| role_permission | role_id | role_master.id |
| attendance | user_id | users.id |
| leave_apply | user_id | users.id |
| leave_apply | leave_type_id | leave_types.id |
| leave_apply | approved_by | users.id |
| salary_slip | user_id | users.id |

---

# Database Statistics

| Category | Count |
|------------|------|
| Existing Tables | 3 |
| New Tables | 8 |
| Total Tables | 11 |
| Foreign Keys | 9 |
| Unique Constraints | 4 |
| One-to-One Relations | 2 |
| One-to-Many Relations | 6 |