# HRMS Role Permission Business Logic Task (Laravel 10)

## Objective

Implement the complete business logic for the **Role & Permission (RBAC)** module.

This module is responsible for determining whether a user has permission to perform an action within the HRMS system.

The implementation must be done **only inside the existing RolePermissionService**.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

The following layers are already completed:

- ✅ Rule Book
- ✅ Migrations
- ✅ Models
- ✅ Seeders
- ✅ Service Architecture
- ✅ Form Requests
- ✅ Service Skeleton
- ✅ Controllers
- ✅ Routes
- ✅ Company Setting Business Logic

Implement business logic only inside:

```
app/Services/RolePermissionService.php
```

Do NOT modify:

- Controllers
- Models
- Routes
- Views
- Migrations
- Seeders
- CompanySettingService

---

# 3. Responsibility

RolePermissionService is responsible ONLY for:

- Permission checking
- Role checking
- Permission retrieval
- Authorization validation
- Role lookup

It MUST NOT contain:

- Attendance logic
- Leave logic
- Salary logic
- Company Setting logic

---

# BUSINESS REQUIREMENTS

This service will later be consumed by:

- UserService
- AttendanceService
- LeaveService
- SalaryService
- DashboardService
- Middleware

---

# METHODS TO IMPLEMENT

Implement the following methods.

---

## 1. hasPermission(int $userId, string $permission)

Purpose

Determine whether a user has a specific permission.

Example

```
employee.create
```

Return

```
bool
```

Rules

- Load user's assigned roles
- Load permissions assigned to those roles
- Return true if permission exists
- Otherwise return false

---

## 2. hasRole(int $userId, string $role)

Purpose

Determine whether user belongs to a role.

Example

```
HR
```

Return

```
bool
```

---

## 3. getUserPermissions(int $userId)

Purpose

Return all permissions assigned to a user.

Return

```
Collection
```

Requirements

- Remove duplicate permissions
- Sort alphabetically

Example

```
attendance.manage

attendance.view

employee.create

employee.edit

employee.view
```

---

## 4. getUserRoles(int $userId)

Purpose

Return all assigned roles.

Return

```
Collection
```

---

## 5. authorize(int $userId, string $permission)

Purpose

Authorize a user for a permission.

Behavior

If permission exists

```
return true;
```

Otherwise

Throw

```
AuthorizationException
```

Do NOT use

```
abort(403)
```

inside the service.

---

## 6. roleExists(string $role)

Return

```
bool
```

---

## 7. permissionExists(string $permission)

Return

```
bool
```

---

## 8. getRolePermissions(string $role)

Purpose

Return all permissions for a role.

Return

```
Collection
```

Sorted alphabetically.

---

## 9. getRolesWithPermissions()

Purpose

Return every role together with its permissions.

Return

```
Collection
```

Requirements

- Use eager loading
- Prevent N+1 queries

---

## 10. userHasAnyRole(int $userId, array $roles)

Purpose

Check whether user belongs to any supplied role.

Return

```
bool
```

Example

```
['Admin','HR']
```

---

## 11. userHasAnyPermission(int $userId, array $permissions)

Purpose

Return true if user has at least one permission.

Return

```
bool
```

---

# QUERY RULES

Use Eloquent Relationships.

Example

```
User
    ->roles()
        ->with('permissions')
```

Avoid manual joins whenever possible.

---

# PERFORMANCE RULES

Must use:

- eager loading
- relationship loading
- collections

Avoid:

- N+1 queries
- duplicate queries
- repeated permission lookups

Keep implementation ready for future caching.

Do NOT implement caching in this phase.

---

# VALIDATION RULES

Validate:

Role Name

- required

Permission

- required

User ID

- must exist

Invalid data should throw meaningful exceptions.

---

# ERROR HANDLING

Throw exceptions when:

- User does not exist
- Role does not exist (where applicable)
- Permission does not exist (where applicable)
- Unauthorized access

Never silently fail.

---

# DATABASE RULES

Use Eloquent only.

Do NOT use:

- Raw SQL
- DB::select()
- Manual joins

Prefer:

- belongsToMany()
- hasMany()
- whereHas()
- with()

---

# FUTURE INTEGRATION

This service will later be used by:

- Middleware
- Policies
- Gates
- Controllers
- AttendanceService
- LeaveService
- SalaryService

Keep methods generic and reusable.

---

# OUT OF SCOPE

Do NOT implement:

- Middleware
- Policies
- Gates
- Controllers
- Routes
- Views
- Events
- Notifications
- Queues
- Caching
- Authentication changes

---

# CODE QUALITY RULES

Follow:

- PSR-12
- SOLID
- DRY
- Type Hinting
- Return Types
- Constructor Dependency Injection

No duplicate business logic.

---

# VERIFICATION

The implementation must pass:

```bash
docker compose exec app php -l app/Services/RolePermissionService.php
```

```bash
docker compose exec app php artisan optimize:clear
```

```bash
docker compose exec app php artisan optimize
```

```bash
docker compose exec app php artisan route:list
```

```bash
docker compose exec app php artisan about
```

---

# SUCCESS CRITERIA

Task is complete when:

- RolePermissionService contains all RBAC business logic.
- Permission checking is fully reusable.
- Role checking is fully reusable.
- Authorization uses AuthorizationException.
- Eloquent relationships are used throughout.
- N+1 queries are avoided.
- No controllers contain RBAC business logic.
- No new models, migrations, routes, views, or middleware are created.
- Existing functionality remains unaffected.
- Code follows Laravel 10 best practices.