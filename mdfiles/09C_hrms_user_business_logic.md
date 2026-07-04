# HRMS User Business Logic Task (Laravel 10)

## Objective

Implement the complete business logic for the **User Management** module.

This module is responsible for employee management, user profile handling, role assignment, employee activation/deactivation, and user retrieval.

The implementation must be done **only inside the existing UserService**.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

The following modules are already completed:

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
- ✅ Role Permission Business Logic

Implement business logic only inside:

```
app/Services/UserService.php
```

Do NOT modify:

- Controllers
- Routes
- Views
- Models
- Migrations
- Seeders
- RolePermissionService
- CompanySettingService

---

# 3. Responsibilities

UserService is responsible ONLY for:

- Employee CRUD business logic
- User profile management
- User status management
- Role assignment
- Employee lookup
- Employee search
- Employee listing

It MUST NOT contain:

- Attendance logic
- Leave logic
- Salary logic
- Dashboard logic

---

# DEPENDENCIES

UserService may consume:

- RolePermissionServiceInterface
- CompanySettingServiceInterface

Use constructor dependency injection only.

---

# BUSINESS REQUIREMENTS

Implement the following methods.

---

## 1. getAllUsers()

Purpose

Return paginated users.

Requirements

- Latest first
- Use pagination
- Eager load:
  - userDetail
  - roles

Return

```
LengthAwarePaginator
```

---

## 2. getUserById(int $id)

Purpose

Return complete employee profile.

Requirements

Load

- User
- UserDetail
- Roles

Throw exception if not found.

Return

```
User
```

---

## 3. createUser(array $data)

Purpose

Create employee.

Requirements

Must create:

- users record
- user_details record

Assign role if provided.

Hash password using Laravel Hash facade.

Return

```
User
```

---

## 4. updateUser(int $id, array $data)

Purpose

Update employee profile.

Requirements

Update

- users
- user_details

Only update password if provided.

Return

```
User
```

---

## 5. deleteUser(int $id)

Purpose

Delete employee.

Requirements

Delete:

- user_details

Detach

- roles

Delete user.

Return

```
bool
```

---

## 6. activateUser(int $id)

Purpose

Activate employee.

Requirements

Update status.

Return

```
bool
```

---

## 7. deactivateUser(int $id)

Purpose

Deactivate employee.

Requirements

Update status.

Return

```
bool
```

---

## 8. assignRole(int $userId, int $roleId)

Purpose

Assign role.

Rules

Avoid duplicate assignments.

Return

```
bool
```

---

## 9. removeRole(int $userId, int $roleId)

Purpose

Remove assigned role.

Return

```
bool
```

---

## 10. syncRoles(int $userId, array $roles)

Purpose

Replace existing roles.

Return

```
bool
```

---

## 11. searchUsers(string $keyword)

Purpose

Search employees.

Search fields

- first_name
- last_name
- email
- emp_code

Return

```
Collection
```

---

## 12. getActiveUsers()

Return

```
Collection
```

Requirements

Use model scopes whenever available.

---

## 13. getInactiveUsers()

Return

```
Collection
```

---

## 14. getEmployeesByRole(string $role)

Purpose

Return all employees belonging to a role.

Return

```
Collection
```

Use eager loading.

---

## 15. userExists(int $id)

Return

```
bool
```

---

## 16. emailExists(string $email)

Return

```
bool
```

---

## 17. employeeCodeExists(string $empCode)

Return

```
bool
```

---

## 18. getEmployeeProfile(int $userId)

Purpose

Return complete employee information.

Include

- User
- UserDetail
- Roles

Return

```
User
```

---

# DATABASE RULES

Use:

- Eloquent ORM
- Relationships
- Transactions where multiple tables are affected

Avoid:

- Raw SQL
- Manual joins
- Duplicate queries

---

# TRANSACTION RULES

Use database transactions for:

- createUser()
- updateUser()
- deleteUser()
- assignRole()
- syncRoles()

Rollback automatically on failure.

---

# PERFORMANCE RULES

Use eager loading for:

- userDetail
- roles

Prevent:

- N+1 queries
- Duplicate queries

Use pagination for listing methods.

---

# VALIDATION RULES

Validate before processing.

Examples

- User exists
- Role exists
- Email uniqueness
- Employee code uniqueness

Throw meaningful exceptions.

---

# SECURITY RULES

Passwords must always be hashed.

Never expose:

- password
- remember_token

Never store plain text passwords.

---

# ERROR HANDLING

Throw exceptions for:

- User not found
- Duplicate email
- Duplicate employee code
- Invalid role
- Invalid user

Never silently fail.

---

# FUTURE INTEGRATION

UserService will later be consumed by:

- AttendanceService
- LeaveService
- SalaryService
- DashboardService
- NotificationService

Keep methods reusable.

---

# OUT OF SCOPE

Do NOT implement:

- Controllers
- Routes
- Views
- Blade
- Middleware
- Notifications
- Events
- Queues
- Authentication changes
- API Resources

---

# CODE QUALITY RULES

Follow:

- PSR-12
- SOLID
- DRY
- Constructor Dependency Injection
- Strict Return Types
- Type Hinting

No duplicate business logic.

---

# VERIFICATION

Implementation must pass:

```bash
docker compose exec app php -l app/Services/UserService.php
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

- UserService contains all user management business logic.
- Employee CRUD is fully centralized.
- User profile management is implemented.
- Role assignment/removal is implemented.
- Passwords are securely hashed.
- Database transactions are used where required.
- Eager loading prevents N+1 queries.
- Controllers remain thin.
- No routes, views, migrations, models, or controllers are modified.
- Existing authentication remains unaffected.
- Code follows Laravel 10 best practices and is production-ready.