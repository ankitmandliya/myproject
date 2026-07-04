# HRMS Route Registration Task (Laravel 10)

## Objective

This phase is responsible for registering all HRMS routes.

Only route definitions should be created.

No business logic, middleware implementation, or controller implementation should be added in this phase.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 route syntax.

---

## 2. Scope Restriction (CRITICAL)

This phase is ONLY for:

✔ Registering routes

✔ Organizing routes

✔ Naming routes

✔ Applying existing middleware groups

DO NOT:

- Implement controller logic
- Add business logic
- Create middleware
- Modify services
- Modify models
- Modify migrations
- Modify seeders

---

## 3. Existing Modules Rule

Do NOT modify any existing working module.

Existing Authentication must remain unchanged.

Existing Leave Type module must remain unchanged.

Existing Holiday module must remain unchanged.

Only add new HRMS routes.

---

# ROUTE FILE

Use:

```
routes/web.php
```

Do NOT create additional route files.

---

# ROUTE PREFIX

All HRMS routes must use:

```
/hrms
```

Example

```
/hrms/users
/hrms/attendance
/hrms/roles
```

---

# ROUTE NAME PREFIX

All route names must begin with:

```
hrms.
```

Examples

```
hrms.users.index
hrms.users.create
hrms.users.store
```

---

# CONTROLLER NAMESPACE

Controllers are located in:

```
App\Http\Controllers\HRMS
```

Use class imports.

Example

```php
use App\Http\Controllers\HRMS\UserController;
```

Do NOT use string based controller syntax.

---

# REQUIRED RESOURCE ROUTES

Register resource routes for:

## User

```
Route::resource('users', UserController::class);
```

---

## Attendance

```
Route::resource('attendance', AttendanceController::class);
```

---

## Leave Apply

```
Route::resource('leave-apply', LeaveApplyController::class);
```

---

## Salary

```
Route::resource('salary', SalaryController::class);
```

---

## Role

```
Route::resource('roles', RoleController::class);
```

---

## Company Setting

Only one configuration record exists.

Use:

```
GET  /company-setting

PUT  /company-setting
```

Do NOT register resource routes.

---

# ROUTE GROUP

All HRMS routes must be inside:

```php
Route::prefix('hrms')
    ->name('hrms.')
    ->middleware(['auth'])
    ->group(function () {

    });
```

Only use middleware that already exists.

Do NOT create RBAC middleware in this phase.

---

# EXCLUDED MODULES

The following modules are already completed.

Do NOT modify or recreate their routes.

- Holiday
- Leave Type

---

# ROUTE ORGANIZATION

Group related routes together.

Recommended order:

1. User
2. Attendance
3. Leave Apply
4. Salary
5. Roles
6. Company Setting

---

# ROUTE MODEL BINDING

Use Laravel Resource routing.

Do NOT implement custom Route Model Binding.

---

# URL CONVENTION

Examples

```
GET     /hrms/users
GET     /hrms/users/create
POST    /hrms/users

GET     /hrms/users/{user}

GET     /hrms/users/{user}/edit

PUT     /hrms/users/{user}

DELETE  /hrms/users/{user}
```

---

# PERFORMANCE RULES

- Use Resource Routes wherever applicable.
- Avoid duplicate route definitions.
- Keep route file clean and readable.

---

# OUT OF SCOPE

Do NOT generate:

- Middleware
- Business Logic
- Services
- Models
- Blade Views
- Validation
- Events
- Policies
- Gates
- API Routes

---

# VERIFICATION

The following commands must execute successfully:

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

# PROGRESS FILE

Update `progress.md` after completion.

Include:

- Task completed
- Files modified
- Routes added
- Verification results
- Any implementation notes

---

# SUCCESS CRITERIA

The task is complete when:

- All HRMS routes are registered.
- Route names follow the `hrms.*` convention.
- URLs use the `/hrms` prefix.
- Controllers are correctly referenced.
- Existing Holiday routes remain untouched.
- Existing Leave Type routes remain untouched.
- Authentication remains unchanged.
- `php artisan route:list` executes successfully.
- No business logic is added.
- No middleware is created.