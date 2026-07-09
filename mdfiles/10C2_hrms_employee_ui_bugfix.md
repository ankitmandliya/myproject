# HRMS Employee UI Bug Fix & Stabilization Task (Laravel 10)

## Objective

This phase fixes all remaining functional, UI, data-binding, and usability issues discovered during manual browser testing of the Employee Management module.

This is a **stabilization phase**.

No new features should be added.

The objective is to make the Employee module production-ready before starting the Attendance module.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or Laravel 12 features.

---

## 2. Scope Restriction

This task ONLY fixes existing Employee UI issues.

Allowed changes:

- Blade Views
- Blade Partials
- Existing Controller (if required)
- Existing Form Requests (only if validation bug exists)
- Existing UserService (only if upload/data binding bug exists)
- Existing Upload Service (if required)
- Existing JavaScript inside Blade

Do NOT create

- New Services
- New Controllers
- New Models
- New Routes
- New Migrations
- New Database Tables

---

# EXISTING ARCHITECTURE

Reuse existing

- UserService
- UploadService
- UserController
- Form Requests
- Bootstrap Layout
- Flash Component
- Breadcrumb Component
- Existing Sidebar
- Existing Navbar

Do NOT redesign the application.

---

# BUG 1 — MULTI STEP FORM VALIDATION (CRITICAL)

## Current Issue

User can move to the next step even when required fields are empty.

---

## Required Behaviour

Before navigating to the next step

Validate only the fields of the current step.

If validation fails

- Stay on current step
- Highlight invalid fields
- Display field-level validation messages
- Do NOT move to the next step

Laravel FormRequest validation must still execute on final Submit.

---

# BUG 2 — MULTI STEP CSS / UI (CRITICAL)

## Current Issue

Multi-step wizard styling is broken.

---

## Required Behaviour

Bootstrap wizard must display

```
Step 1

Step 2

Step 3

Step 4

Step 5
```

Requirements

- Equal width navigation
- Active step highlighted
- Completed steps highlighted
- Responsive layout
- Proper spacing
- No overlapping
- No broken alignment

Do NOT use external wizard plugins.

Use Bootstrap Tabs or Bootstrap Pills only.

---

# BUG 3 — IMAGE UPLOAD (CRITICAL)

## Current Issue

Profile image upload is not working.

---

## Verify

- Form uses

```html
enctype="multipart/form-data"
```

- Input type is

```html
<input type="file">
```

- Correct field name
- File reaches Controller
- File reaches UserService
- Existing UploadService stores image
- Database stores image path
- Validation allows image upload
- Update preserves existing image when no new image is uploaded

---

# BUG 4 — IMAGE DISPLAY (CRITICAL)

## Current Issue

Employee image is not displayed in Employee List.

---

## Required Behaviour

Display employee image on

- Employee List
- Employee View
- Employee Edit

If image exists

Display uploaded image.

If image is missing

Display default avatar.

Verify

- Storage path
- asset()
- Storage::url()
- Existing upload location

Do NOT hardcode image paths.

---

# BUG 5 — JOINING DATE (CRITICAL)

## Current Issue

Joining Date is blank on Edit page.

---

## Required Behaviour

Edit page must pre-populate

```
Joining Date
```

using existing employee data.

Example

```blade
value="{{ old('joining_date', $employee->userDetail->joining_date ?? '') }}"
```

Verify all edit fields use proper old() fallback.

---

# BUG 6 — REVIEW STEP (CRITICAL)

## Current Issue

Review page is blank during Create Employee.

---

## Required Behaviour

Review page must display

- Employee Code
- Name
- Email
- Gender
- DOB
- Department
- Designation
- Joining Date
- Role
- Status
- Salary
- Aadhaar
- PAN

without submitting the form.

Use JavaScript only.

No AJAX.

No database queries.

Update review dynamically whenever user navigates between steps.

---

# BUG 7 — FLASH MESSAGE SPACING

## Current Issue

Large blank space exists between flash message and Employee form.

---

## Required Behaviour

Reduce

- Margin
- Padding
- Empty wrappers

Flash message should appear directly above the Employee card.

Optional

Auto-hide success messages after

```
5 seconds
```

without affecting layout.

---

# BUG 8 — FORM SPACING

Review

- Bootstrap Grid
- Cards
- Row spacing
- Form spacing
- Button spacing

Remove unnecessary vertical gaps.

Keep consistent spacing throughout the Employee module.

---

# BUG 9 — VALIDATION DISPLAY

Validation must

- Display below the correct field
- Preserve old input
- Preserve current step
- Highlight invalid inputs
- Scroll to first validation error (optional)

Do NOT clear previously entered values.

---

# BUG 10 — FILE PREVIEW (OPTIONAL)

If a new profile image is selected

Display preview before upload.

Use lightweight JavaScript only.

No third-party plugins.

---

# EDIT FORM VERIFICATION

Verify all fields are prefilled correctly

- Employee Code
- First Name
- Last Name
- Email
- Gender
- DOB
- Joining Date
- Department
- Designation
- Salary
- Role
- Status
- Aadhaar
- PAN
- Address
- Profile Image

---

# CREATE FORM VERIFICATION

Verify

- Multi-step works
- Required validation works
- Review page works
- Image upload works
- Employee saves successfully

---

# BROWSER VERIFICATION (MANDATORY)

After implementation manually verify

Employee List

Create Employee

Edit Employee

View Employee

Image Upload

Image Preview

Employee Image

Joining Date

Multi-step Navigation

Review Page

Required Validation

Flash Messages

Mobile View

Tablet View

Desktop View

No SQL Errors

No Undefined Variables

No Broken Layout

No 404 Errors

---

# VERIFICATION COMMANDS

Run

```bash
docker compose exec app php artisan optimize:clear
```

```bash
docker compose exec app php artisan optimize
```

```bash
docker compose exec app php artisan view:cache
```

```bash
docker compose exec app php artisan route:list
```

```bash
docker compose exec app php artisan about
```

Run PHP lint

```bash
find app -name "*.php" -exec php -l {} \;
```

---

# PROGRESS UPDATE

Update

```
progress.md
```

Include

- Files modified
- Multi-step validation fixed
- Multi-step UI fixed
- Image upload fixed
- Image display fixed
- Joining Date fixed
- Review step fixed
- Flash spacing fixed
- Form spacing improved
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Multi-step wizard works correctly.
- Step validation prevents invalid navigation.
- Profile image uploads successfully.
- Uploaded image displays on List, View, and Edit pages.
- Existing image is preserved during update.
- Joining Date displays correctly on Edit.
- Review step displays all entered data.
- Flash message spacing is clean.
- Form spacing is consistent.
- Validation messages display correctly.
- Old input is preserved.
- Responsive layout works.
- No SQL errors.
- No Blade errors.
- No undefined variables.
- Existing architecture remains unchanged.
- Existing services and controllers are reused.
- Manual browser verification passes successfully.
- All verification commands pass successfully.