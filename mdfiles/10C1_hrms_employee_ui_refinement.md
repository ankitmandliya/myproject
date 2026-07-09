# HRMS Employee UI Refinement Task (Laravel 10)

## Objective

This phase refines the Employee Management UI after manual browser testing.

The purpose is to fix functional issues, improve user experience, and polish the Employee module without changing the existing architecture.

This task is a **refinement phase only**.

No new business logic should be introduced.

---

# IMPORTANT RULES (MANDATORY)

## 1. Laravel Version

- Use Laravel 10 only
- Do NOT use Laravel 11 or Laravel 12 features

---

## 2. Scope Restriction

This phase ONLY includes

- Employee Blade improvements
- Form usability improvements
- Validation improvements
- Image upload field
- UI polishing
- Browser bug fixes

Do NOT modify

- Models
- Migrations
- Seeders
- Services Business Logic
- Existing Architecture

Only make the minimum changes required.

---

# EXISTING UI REUSE (MANDATORY)

Reuse existing

- Admin Panel Layout
- Bootstrap Components
- Sidebar
- Navbar
- Breadcrumb
- Flash Messages
- Existing Form Structure

Do NOT redesign the Admin Panel.

---

# BUG FIXES (CRITICAL)

## 1. Joining Date Issue

Current Issue

```
SQLSTATE[23000]

Column 'joining_date' cannot be null
```

Required Fix

- Verify joining_date input exists.
- Verify field name matches database.
- Verify old() binding.
- Verify FormRequest validation.
- Verify controller receives the field.
- Verify UserService passes the value correctly.
- Verify UserDetail is saved correctly.

Create Employee must save successfully.

---

## 2. Validation Issue

Current Issue

Validation is not working correctly.

Required Fix

- Verify FormRequest is being used.
- Display validation messages below each field.
- Preserve old values using

```php
old()
```

- Display first validation error.
- Validation must work for

Create

Edit

---

## 3. Required Fields

Required fields must display a red

```
*
```

Required fields

- Employee Code
- First Name
- Email
- Joining Date
- Role
- Status
- Password (Create only)
- Confirm Password (Create only)

Use Bootstrap text-danger.

Example

```html
<label>
First Name
<span class="text-danger">*</span>
</label>
```

---

# PROFILE PHOTO

Replace current input with

```html
<input type="file">
```

Requirements

Accept

- jpg
- jpeg
- png
- webp

Display

Current image on Edit page.

If no image exists

Display default avatar.

Optional

Live preview before upload.

Do NOT add third-party libraries.

---

# MULTI STEP FORM

Convert Create/Edit Employee into a Bootstrap Wizard.

No external plugin.

Use Bootstrap Tabs or Bootstrap Pills.

---

## STEP 1

Account Information

Fields

- Employee Code
- First Name
- Last Name
- Email
- Password
- Confirm Password

---

## STEP 2

Personal Information

Fields

- Gender
- Date of Birth
- Mobile
- Address
- Profile Photo

---

## STEP 3

Employment Information

Fields

- Joining Date
- Department
- Designation
- Basic Salary
- Role
- Status

---

## STEP 4

Government Details

Fields

- Aadhaar
- PAN

---

## STEP 5

Review

Display all entered information.

Buttons

Previous

Submit

---

# FORM NAVIGATION

Buttons

```
Previous

Next

Submit
```

Rules

- Previous disabled on first step.
- Next disabled on last step.
- Submit only appears on last step.

Keep implementation lightweight.

---

# VALIDATION DURING MULTI STEP

Requirements

- Validation errors must return to the correct step.
- Preserve entered values.
- Do NOT clear previous steps.
- Required field indicators remain visible.

---

# FLASH MESSAGE IMPROVEMENT

Current Issue

Large vertical blank space appears after flash messages.

Required Fix

- Reduce top margin.
- Remove unnecessary padding.
- Keep flash inside Bootstrap container.
- Keep form positioned immediately below flash.

Optional

Auto-hide success message after

```
5 seconds
```

using lightweight JavaScript.

Do NOT use jQuery plugins.

---

# EMPTY SPACE OPTIMIZATION

Review

- Margins
- Padding
- Card spacing
- Form spacing
- Alert spacing

Reduce excessive white space while keeping the UI readable.

---

# BUTTON IMPROVEMENTS

Primary buttons

- Save
- Update
- Next
- Previous

Secondary buttons

- Back
- Cancel

Reuse existing Bootstrap button styles.

---

# BREADCRUMB

Verify breadcrumb works on

- Employee List
- Create
- Edit
- View

---

# RESPONSIVE DESIGN

Verify

Desktop

Tablet

Mobile

Multi-step form should remain usable.

---

# BLADE RESPONSIBILITY

Blade MUST NOT

- Query database
- Call Services
- Execute business logic
- Perform calculations

Blade only renders UI.

---

# ACCESSIBILITY

Improve usability

- Associate labels with inputs
- Required fields visible
- Keyboard navigation works
- File input accessible
- Buttons properly labeled

---

# CODE QUALITY

Follow

- Laravel Blade Best Practices
- Bootstrap Best Practices
- DRY
- Reusable partials
- Clean HTML

Do NOT duplicate form code.

---

# BROWSER VERIFICATION

Verify

Employee List

Create Employee

Edit Employee

View Employee

Image Upload

Validation

Required *

Multi-step Navigation

Joining Date Save

Flash Messages

Responsive Layout

No SQL Errors

No Undefined Variables

No Blade Errors

No Broken Layout

---

# VERIFICATION

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

Perform manual browser testing after login.

---

# PROGRESS UPDATE

Update

```
progress.md
```

Include

- Files modified
- Joining Date issue fixed
- Validation fixed
- Required field indicators added
- File upload integrated
- Multi-step form completed
- Flash UI improved
- Responsive verification completed
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Employee can be created successfully.
- Joining Date saves correctly.
- Validation works correctly.
- Validation messages display properly.
- Old input is preserved.
- Profile photo upload works.
- Existing profile photo displays on edit.
- Required fields display a red *.
- Multi-step form works smoothly.
- Flash messages do not create excessive vertical spacing.
- Responsive layout works.
- Existing Admin Panel UI remains unchanged.
- No new business logic is introduced.
- No existing functionality is broken.
- All verification commands pass.
- Manual browser testing passes successfully.