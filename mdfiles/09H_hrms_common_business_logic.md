# HRMS Common Business Logic Task (Laravel 10)

## Objective

Implement the **Common Business Logic Layer** for the HRMS system.

The purpose of this phase is to centralize reusable logic that is shared across multiple modules, avoiding duplicate code and ensuring consistency throughout the application.

This layer must contain **generic reusable functionality only**.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Existing Architecture

The following modules are already completed.

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
- ✅ User Business Logic
- ✅ Attendance Business Logic
- ✅ Leave Apply Business Logic
- ✅ Salary Business Logic
- ✅ Dashboard Business Logic

This task must only create reusable common components.

---

# SCOPE

Create

```
app/Services/Common/
```

Inside this directory create the following services.

```
CommonService.php
DateService.php
FileUploadService.php
PaginationService.php
EmployeeCodeService.php
ResponseService.php
```

Create matching interfaces inside

```
app/Contracts/Common/
```

```
CommonServiceInterface.php
DateServiceInterface.php
FileUploadServiceInterface.php
PaginationServiceInterface.php
EmployeeCodeServiceInterface.php
ResponseServiceInterface.php
```

Register every interface inside

```
AppServiceProvider
```

using dependency injection.

---

# SERVICE RESPONSIBILITIES

## 1. CommonService

Purpose

General reusable helper methods.

Implement

```php
isActive(bool $status): bool

generateUuid(): string

generateReferenceNumber(string $prefix): string

sanitizeString(string $value): string

emptyToNull(mixed $value): mixed
```

---

## 2. DateService

Purpose

Centralized date handling.

Implement

```php
today()

now()

formatDate($date, string $format = 'd-m-Y')

formatDateTime($date)

startOfMonth()

endOfMonth()

daysBetween($from, $to)

isWeekend($date)

isFutureDate($date)

isPastDate($date)
```

Use Carbon.

---

## 3. EmployeeCodeService

Purpose

Generate employee codes.

Implement

```php
generateEmployeeCode()

employeeCodeExists(string $code)

nextSequence()
```

Format

```
EMP0001

EMP0002

EMP0003
```

Do NOT modify existing employees.

Only generate.

---

## 4. PaginationService

Purpose

Reusable pagination.

Implement

```php
perPage()

paginate($query)

simplePaginate($query)
```

Use configurable per-page values.

Default

```
15
```

---

## 5. FileUploadService

Purpose

Centralize file upload operations.

Implement

```php
uploadImage()

uploadDocument()

deleteFile()

fileExists()

generateUniqueFilename()
```

Do NOT implement cloud storage.

Only local storage.

---

## 6. ResponseService

Purpose

Standardize service responses.

Implement

```php
success()

error()

validationError()

notFound()

forbidden()
```

Return arrays only.

Example

```php
[
    'success' => true,
    'message' => '',
    'data' => []
]
```

Do NOT return

- Views
- Redirects
- JsonResponse

---

# DEPENDENCY RULES

Use constructor dependency injection only.

Never instantiate services manually.

---

# BUSINESS RULES

These services MUST NOT contain

- Attendance logic
- Leave logic
- Salary logic
- Role logic
- Dashboard logic
- Notification logic

They must remain generic.

---

# PERFORMANCE RULES

- Reuse existing helpers.
- Avoid duplicate implementations.
- Keep methods lightweight.
- Use Carbon where appropriate.
- Avoid unnecessary database queries.

---

# ERROR HANDLING

Throw meaningful exceptions where appropriate.

Do NOT silently fail.

---

# FUTURE READY

Design these services to support future integration with

- Notifications
- Reports
- Exports
- APIs
- Queues
- Jobs

without modification.

---

# OUT OF SCOPE

Do NOT create or modify

- Controllers
- Routes
- Blade
- APIs
- Models
- Migrations
- Seeders
- Existing Business Logic

Do NOT implement

- Email
- SMS
- WhatsApp
- PDF generation
- Excel export
- Cloud storage

---

# CODE QUALITY

Follow

- PSR-12
- SOLID
- DRY
- Constructor Dependency Injection
- Strict Return Types
- Type Hinting

Keep all services framework-independent as much as possible.

---

# VERIFICATION

Implementation must pass

```bash
docker compose exec app php -l app/Services/Common/CommonService.php
```

```bash
docker compose exec app php -l app/Services/Common/DateService.php
```

```bash
docker compose exec app php -l app/Services/Common/FileUploadService.php
```

```bash
docker compose exec app php -l app/Services/Common/PaginationService.php
```

```bash
docker compose exec app php -l app/Services/Common/EmployeeCodeService.php
```

```bash
docker compose exec app php -l app/Services/Common/ResponseService.php
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

Task is complete when

- All common services are created.
- All interfaces are created.
- All bindings are registered in AppServiceProvider.
- Date utilities are centralized.
- Employee code generation is centralized.
- File upload logic is centralized.
- Pagination logic is centralized.
- Standard response helpers are implemented.
- Generic helper methods are reusable across the HRMS.
- No business logic from Attendance, Leave, Salary, Dashboard, or Roles is duplicated.
- Existing functionality remains unaffected.
- Code follows Laravel 10 best practices and is production-ready.
```