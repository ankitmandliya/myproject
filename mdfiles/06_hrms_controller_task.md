# HRMS Controller Layer Task (Laravel 10)

## Objective

This task defines the Controller layer for the HRMS system.

Controllers are responsible only for coordinating requests and responses.

Controllers MUST NOT contain business logic.

All business operations must be delegated to the Service Layer.

---

# PREREQUISITES (MANDATORY)

The following tasks MUST already be completed.

- 01_hrms_db_tbl_create_migration_task.md
- 02_hrms_model_task.md
- 03_hrms_seeder_task.md
- 04_hrms_service_architecture_task.md
- 05_hrms_form_request_task.md
- 05A_hrms_service_implementation_task.md

Controllers MUST consume ONLY the Service Interfaces created in previous tasks.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only
- Do NOT use Laravel 11 or Laravel 12 features

---

## 2. Controller Responsibility

Controllers are responsible only for:

- Receiving HTTP requests
- Calling Form Request validation
- Calling Service Interface methods
- Returning Views
- Returning Redirect responses
- Returning JSON responses (future API)

Controllers MUST NOT:

- Contain business logic
- Perform calculations
- Access database directly
- Execute raw SQL
- Use Query Builder
- Use DB facade
- Perform manual validation
- Instantiate Models

---

## 3. Architecture

The application architecture MUST follow:

```
Request
    ↓
Form Request
    ↓
Controller
    ↓
Service Interface
    ↓
Service
    ↓
Model
    ↓
Database
```

Controllers must never skip the Service Layer.

---

# CONTROLLERS TO CREATE

Create the following controllers.

```
UserController

HolidayController

LeaveTypeController

AttendanceController

LeaveApplyController

SalaryController

RoleController

CompanySettingController
```

---

# CONTROLLER → SERVICE MAPPING

Each controller MUST inject ONLY its corresponding Service Interface.

| Controller | Service Interface |
|------------|-------------------|
| UserController | UserServiceInterface |
| HolidayController | HolidayServiceInterface |
| LeaveTypeController | LeaveTypeServiceInterface |
| AttendanceController | AttendanceServiceInterface |
| LeaveApplyController | LeaveServiceInterface |
| SalaryController | SalaryServiceInterface |
| RoleController | RoleServiceInterface |
| CompanySettingController | CompanySettingServiceInterface |

Never inject Models directly.

---

# DEPENDENCY INJECTION

Use constructor property promotion.

Example

```php
public function __construct(
    protected HolidayServiceInterface $holidayService
) {
}
```

Do NOT use:

```php
private HolidayServiceInterface $holidayService;

public function __construct(HolidayServiceInterface $holidayService)
{
    $this->holidayService = $holidayService;
}
```

---

# FORM REQUEST USAGE

Controllers MUST use Form Requests.

Example

```php
public function store(StoreHolidayRequest $request)
{
    $this->holidayService->store($request->validated());

    return redirect()->route('holidays.index');
}
```

Update example

```php
public function update(UpdateHolidayRequest $request, int $id)
{
    $this->holidayService->update($id, $request->validated());

    return redirect()->route('holidays.index');
}
```

Never use:

```php
Request $request

Validator::make()

$request->validate()
```

---

# REQUIRED CRUD METHODS

Every CRUD controller MUST contain:

```php
index()

create()

store()

show()

edit()

update()

destroy()
```

Controllers without CRUD (if applicable) should contain only the required actions.

---

# SERVICE METHOD MAPPING

Controller methods MUST call the following service methods.

| Controller Method | Service Method |
|-------------------|----------------|
| index() | paginate() |
| show() | getById() |
| store() | store() |
| update() | update() |
| destroy() | delete() |

No additional database operations are allowed.

---

# RESPONSE RULES

Controllers MAY return:

- View
- RedirectResponse
- JsonResponse (future API)

Controllers MUST NOT return:

- Eloquent Models directly
- Query Builder instances

---

# FORBIDDEN CODE

The following code MUST NOT appear inside any controller.

```php
Holiday::create(...);

Holiday::where(...);

Holiday::find(...);

User::create(...);

Attendance::create(...);

DB::table(...);

DB::select(...);

Validator::make(...);

$request->validate(...);

new Holiday();

new User();

new Attendance();
```

---

# ALLOWED CODE

Controllers should only call services.

Example

```php
$this->holidayService->paginate();

$this->holidayService->getById($id);

$this->holidayService->store($request->validated());

$this->holidayService->update($id, $request->validated());

$this->holidayService->delete($id);
```

---

# ERROR HANDLING

Controllers should not contain business exception handling.

If required, catch only unexpected exceptions for user-friendly responses.

Business exceptions should be handled inside the Service Layer in future phases.

---

# PERFORMANCE RULES

Controllers must never:

- Eager load models
- Optimize queries
- Cache data

These responsibilities belong to the Service Layer.

---

# OUT OF SCOPE

Do NOT create:

- Routes
- APIs
- Blade files
- Business logic
- Validation rules
- Seeders
- Migrations
- Events
- Listeners
- Jobs
- Notifications
- Middleware
- Policies

---

# VERIFICATION

Verify inside Docker.

```bash
docker compose exec app php artisan optimize:clear

docker compose exec app php artisan optimize

docker compose exec app php artisan route:list

docker compose exec app php artisan about

docker compose exec app php -l app/Http/Controllers/HolidayController.php
```

All controllers must pass PHP syntax validation.

Laravel must resolve all injected service interfaces successfully.

---

# SUCCESS CRITERIA

The task is complete when:

- All required controllers are created.
- Every controller injects only its corresponding Service Interface.
- Every controller uses Form Requests.
- No controller accesses Models directly.
- No controller contains business logic.
- No controller performs validation manually.
- CRUD methods are implemented with service delegation only.
- Controllers follow Laravel 10 best practices.
- PHP lint passes for all controllers.
- Laravel application boots successfully.