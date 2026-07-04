# HRMS Model Layer Task (Laravel 10)

## Objective

This document defines the Model layer structure for the HRMS system.

The purpose is to create clean Laravel Eloquent Models with proper relationships, scopes, and architecture standards without modifying any existing database structure.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version Constraint

- Use Laravel 10 only
- Do NOT use Laravel 11 or 12 features or syntax

---

## 2. Database Safety Rules

- NEVER modify existing tables
- NEVER modify existing migrations
- Only create or update MODEL layer files
- Do NOT break existing authentication
- Do NOT break existing CRUD functionality

---

## 3. Architecture Principles

You MUST follow:

- DRY (Don't Repeat Yourself)
- SOLID principles (where applicable)
- Separation of Concerns
- Service Layer Ready Architecture
- PSR-4 Coding Standards

---

## 4. Eloquent Best Practices

All models MUST include (where applicable):

- `$fillable`
- `$casts`
- Proper Relationships
- Local Query Scopes
- Eager Loading Ready Structure

Use `$hidden` only when sensitive fields exist.

Do NOT hide fields unnecessarily.

---

# 5. MODEL NAMING CONVENTION (CRITICAL)

Use EXACTLY the following model names.

- User
- UserDetail
- RoleMaster
- UserRole
- RolePermission
- Attendance
- LeaveApply
- LeaveType
- Holiday
- SalarySlip
- CompanySetting

## Strict Rules

❌ Do NOT rename models

❌ Do NOT pluralize model names

❌ Do NOT generate duplicate models

❌ Do NOT invent alternative names

Examples NOT allowed

- Users
- Roles
- EmployeeRole
- Leave
- LeaveApplication
- CompanySettings

---

# 6. EXISTING MODELS

The following models already exist.

- User
- LeaveType
- Holiday

These models MUST NOT be recreated.

They may ONLY be updated to include:

- Relationships
- Fillable
- Casts
- Local Scopes
- Helper Methods

---

# 7. NAMESPACE RULE

Every model must use

```php
namespace App\Models;
```

Follow Laravel PSR-4 autoloading.

---

# MODEL DEFINITIONS

## 1. User (Existing)

Table

```
users
```

Relationships

```php
userDetail()

attendance()

leaveApplications()

salarySlips()

roles()
```

---

## 2. UserDetail

Table

```
user_details
```

Relationship

```php
user()
```

---

## 3. RoleMaster

Table

```
role_master
```

Relationships

```php
users()

permissions()
```

---

## 4. UserRole

Table

```
user_role
```

Relationships

```php
user()

role()
```

---

## 5. RolePermission

Table

```
role_permission
```

Relationship

```php
role()
```

---

## 6. Attendance

Table

```
attendance
```

Relationship

```php
user()
```

Local Scopes

```php
present()

absent()

late()

byMonth($month)
```

---

## 7. LeaveApply

Table

```
leave_apply
```

Relationships

```php
user()

leaveType()

approvedBy()
```

---

## 8. LeaveType (Existing)

Table

```
leave_types
```

Relationship

```php
leaveApplications()
```

---

## 9. Holiday (Existing)

Table

```
holidays
```

No relationships required.

---

## 10. SalarySlip

Table

```
salary_slip
```

Relationship

```php
user()
```

---

## 11. CompanySetting

Table

```
company_setting
```

No relationships required.

Used for application-wide HRMS settings.

---

# PRIMARY KEY RULE

All models use Laravel's default primary key.

```php
id
```

Do NOT override

```php
protected $primaryKey
```

unless explicitly required.

---

# TIMESTAMP RULE

All models use Laravel timestamps.

Do NOT disable timestamps.

Do NOT set

```php
public $timestamps = false;
```

unless explicitly required.

---

# FILLABLE REQUIREMENT

Every model should define

```php
protected $fillable = [
    //
];
```

using only database columns that are intended for mass assignment.

---

# CASTS REQUIREMENT

Use appropriate casts.

Example

```php
protected $casts = [
    'status' => 'boolean',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];
```

---

# RELATIONSHIP RULES

Use only Laravel Eloquent relationships.

Allowed

- hasOne
- hasMany
- belongsTo
- belongsToMany

Do NOT write raw SQL joins inside models.

---

# LOCAL SCOPES

Reusable filters should be implemented as Local Scopes.

Example

```php
public function scopeActive($query)
{
    return $query->where('status', 1);
}
```

---

# GLOBAL SCOPES

Do NOT create Global Scopes unless explicitly requested.

Use only Local Scopes for this task.

---

# BOOT METHODS

Do NOT override

```php
boot()

booted()
```

unless absolutely required.

Do NOT register observers inside models.

---

# OBSERVER READY

Models should be designed so that Observers can be attached later.

Support future events:

- created
- updated
- deleted

without changing the model structure.

---

# PERFORMANCE REQUIREMENTS

Always write models that support:

- Eager Loading
- Prevention of N+1 Queries
- Reusable Local Scopes
- Clean Relationship Loading

---

# OUT OF SCOPE

Do NOT generate

- Controllers
- Routes
- Blade Views
- APIs
- Services
- Repositories
- Seeders
- Factories
- Migrations
- Business Logic

---

# EXPECTED DELIVERABLES

Generate only

- Models
- Relationships
- Fillable arrays
- Casts
- Local Scopes
- PHPDoc blocks (optional)

Nothing else.

---

# PROGRESS TRACKING

After completing this task, update

```
progress.md
```

Include

- Task completed
- Files created or modified
- Summary of implementation
- Current status
- Remaining work (if any)
- Issues encountered (if any)

---

# SUCCESS CRITERIA

The task will be considered complete when

- All required models are created or updated.
- Existing models are NOT recreated.
- All relationships are correctly defined.
- Fillable properties are configured.
- Casts are properly configured.
- Local scopes are implemented where required.
- Laravel 10 coding standards are followed.
- Existing authentication remains untouched.
- Existing CRUD functionality remains untouched.
- No database schema has been modified.
- Code is clean, reusable, maintainable, and production-ready.