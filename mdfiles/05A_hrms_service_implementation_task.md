# HRMS Service Implementation Task (Laravel 10)

## Objective

This task extends the previously created Service Architecture.

The goal is to complete the Service Layer by defining all required service contracts and implementing service skeletons that the Controller layer can consume.

This phase is still NOT responsible for business logic.

It only prepares a complete service layer for dependency injection and future implementation.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only
- Do NOT use Laravel 11 or Laravel 12 features

---

## 2. Scope

This phase ONLY includes:

- Creating missing Service Interfaces
- Creating missing Service Classes
- Adding CRUD method signatures
- Binding interfaces in AppServiceProvider
- Dependency Injection readiness

DO NOT implement:

- Business logic
- Validation
- Events
- Notifications
- Jobs
- Queue
- Caching
- Transactions
- Database calculations

---

## 3. Architecture Rules

Follow:

- SOLID
- DRY
- Single Responsibility Principle
- Interface Segregation Principle
- Dependency Injection
- Service Layer Pattern

Controllers MUST communicate ONLY with service interfaces.

---

# DIRECTORY STRUCTURE

Interfaces

```
app/Contracts/
```

Services

```
app/Services/
```

---

# EXISTING SERVICES

The following services already exist.

DO NOT recreate them.

- AttendanceService
- LeaveService
- SalaryService
- RolePermissionService
- CompanySettingService

If required, update their interfaces only.

---

# CREATE NEW SERVICE INTERFACES

Create the following interfaces.

## UserServiceInterface

## HolidayServiceInterface

## LeaveTypeServiceInterface

## RoleServiceInterface

---

# CREATE NEW SERVICES

Create:

- UserService
- HolidayService
- LeaveTypeService
- RoleService

Each service must implement its interface.

Example

```php
class HolidayService implements HolidayServiceInterface
{
}
```

---

# STANDARD CRUD CONTRACT

Every CRUD service MUST expose the following methods.

```php
public function paginate(int $perPage = 10);

public function getById(int $id);

public function store(array $data);

public function update(int $id, array $data);

public function delete(int $id);
```

---

# HOLIDAY SERVICE

HolidayServiceInterface

Must include

```php
paginate()

getById()

store()

update()

delete()

active()
```

Example

```php
public function active();
```

---

# LEAVE TYPE SERVICE

LeaveTypeServiceInterface

Must include

```php
paginate()

getById()

store()

update()

delete()

active()
```

---

# USER SERVICE

UserServiceInterface

Must include

```php
paginate()

getById()

store()

update()

delete()

assignRole(int $userId, int $roleId)

removeRole(int $userId)
```

---

# ROLE SERVICE

RoleServiceInterface

Must include

```php
paginate()

getById()

store()

update()

delete()

permissions(int $roleId)
```

---

# UPDATE EXISTING INTERFACES

AttendanceServiceInterface

Keep existing methods.

Add

```php
paginate(int $perPage = 10);

getById(int $id);
```

---

LeaveServiceInterface

Keep existing methods.

Add

```php
paginate(int $perPage = 10);

getById(int $id);
```

---

SalaryServiceInterface

Keep existing methods.

Add

```php
paginate(int $perPage = 10);

getById(int $id);
```

---

CompanySettingServiceInterface

Keep existing methods.

Add

```php
public function getSettings();

public function updateSettings(array $data);
```

---

RolePermissionServiceInterface

Keep existing methods.

Add

```php
public function getRolePermissions(int $roleId);

public function syncPermissions(int $roleId, array $permissions);
```

---

# DEPENDENCY INJECTION

All services MUST use constructor injection.

Example

```php
public function __construct(
    protected Holiday $holiday
) {}
```

DO NOT instantiate models manually.

Never use

```php
new Holiday();
```

---

# METHOD IMPLEMENTATION RULE

Every method must contain only a placeholder.

Example

```php
public function store(array $data)
{
    //
}
```

OR

```php
public function store(array $data)
{
    throw new \BadMethodCallException('Not implemented.');
}
```

No database queries.

No business logic.

No validation.

---

# RETURN TYPE RULE

Methods may declare return types when appropriate.

Allowed

- Model
- Collection
- LengthAwarePaginator
- array
- bool
- void
- mixed

Do NOT return

- View
- JsonResponse
- RedirectResponse

---

# APP SERVICE PROVIDER

Update

```
app/Providers/AppServiceProvider.php
```

Bind every interface.

Example

```php
$this->app->bind(
    HolidayServiceInterface::class,
    HolidayService::class
);
```

Repeat for all services.

---

# CONTROLLER READINESS

After this task every controller must be able to inject its service.

Example

```php
public function __construct(
    protected HolidayServiceInterface $holidayService
)
{
}
```

No controller should directly access Eloquent Models.

---

# OUT OF SCOPE

Do NOT create

- Controllers
- Routes
- Blade files
- APIs
- Form Requests
- Policies
- Middleware
- Business Logic
- Repository Pattern
- Events
- Listeners
- Jobs
- Notifications

---

# VERIFICATION

Verify using Docker.

```bash
docker compose exec app php artisan optimize:clear

docker compose exec app php artisan optimize

docker compose exec app php artisan about

docker compose exec app php -l app/Services/HolidayService.php
```

Ensure Laravel can resolve every interface.

Example

```php
app(\App\Contracts\HolidayServiceInterface::class);

app(\App\Contracts\UserServiceInterface::class);

app(\App\Contracts\RoleServiceInterface::class);
```

---

# SUCCESS CRITERIA

Task is complete when

- All service interfaces exist
- All services implement their interfaces
- All CRUD method signatures are defined
- Existing services are updated where required
- All interfaces are bound in AppServiceProvider
- Laravel resolves every service successfully
- No business logic is implemented
- Controllers are ready for dependency injection
- Code follows Laravel 10 best practices