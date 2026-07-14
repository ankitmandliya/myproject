# HRMS Leave UI Refinement & Browser Bug Fixes (Laravel 10)

## Task

10F2_hrms_leave_ui_refinement.md

---

# Objective

Polish the Leave Management module after manual browser testing.

This phase focuses ONLY on

- UI refinements
- Browser bug fixes
- UX improvements
- Validation improvements
- Responsive improvements

No new business logic should be introduced.

---

# IMPORTANT

Reuse existing

- LeaveService
- LeaveController
- Form Requests
- Layout
- Sidebar
- Navbar
- Flash Messages
- Breadcrumb
- Bootstrap

Do NOT redesign the Admin Panel.

---

# SCOPE

May modify

- Leave Blade files
- Blade partials
- Existing JavaScript
- Existing CSS classes
- LeaveController only if required for UI integration
- progress.md

Do NOT modify

- Business Logic
- Database
- Models
- Services
- Migrations

unless fixing an integration bug.

---

# MULTI-STEP FORM

Review the Leave Apply wizard.

Verify

- Previous button
- Next button
- Active step
- Completed step
- Responsive layout

Fix any broken navigation.

---

# STEP VALIDATION

Before moving to the next step

validate the current step.

Do NOT allow

Next

until required fields are valid.

Display validation errors beside fields.

---

# REVIEW STEP

Review page must display

Employee

Employee Code

Department

Designation

Leave Type

From Date

To Date

Total Days

Reason

Attachment (filename if uploaded)

Status

Nothing should appear blank.

---

# REQUIRED FIELDS

Display

*

for every required field.

Use consistent styling.

---

# OLD INPUT

When validation fails

retain

- selected leave type
- dates
- reason
- attachment name (display only)
- current wizard step

---

# FLASH MESSAGE

Reuse existing flash component.

Reduce unnecessary top spacing.

Auto-hide success message.

Keep validation errors visible.

---

# FILTERS

Verify

Employee

Department

Leave Type

Status

Date Range

retain values after filtering.

---

# TABLE

Improve

- column alignment
- avatar size
- badge spacing
- responsive behavior
- action button spacing

Reuse Bootstrap.

---

# STATUS BADGES

Verify consistent colors

Pending

Approved

Rejected

Same style throughout module.

---

# EMPLOYEE PHOTO

Verify

List

Details

Approval

all display

uploaded employee image

Fallback avatar if missing.

---

# CALENDAR

Verify

- Previous month
- Current month
- Next month

Display

Approved Leave

Holiday

Weekly Off

No overlap rendering.

No dummy/demo data.

---

# TIMELINE

Improve readability.

Display

Applied

↓

Pending

↓

Approved

or

Rejected

Highlight current stage.

---

# APPROVAL PAGE

Improve

- button alignment
- remarks display
- employee information
- leave duration

Approval and Reject buttons must disable during submit.

Prevent duplicate submission.

---

# LOADING STATE

Buttons

Apply

Update

Approve

Reject

Delete

must

- disable during request
- show spinner
- prevent double click

---

# EMPTY STATES

Improve

No Leave Requests Found

Reuse existing HRMS illustration/icon if available.

---

# DELETE CONFIRMATION

Pending Leave deletion must show

Bootstrap confirmation modal.

No browser alert().

---

# RESPONSIVE DESIGN

Verify

Desktop

Tablet

Mobile

No horizontal overflow.

---

# ACCESSIBILITY

Verify

- Labels linked to inputs
- Buttons have titles where appropriate
- Modal keyboard close works
- Focus returns after modal closes

---

# BROWSER VERIFICATION

Verify manually

✓ Apply Leave

✓ Edit Pending Leave

✓ Delete Pending Leave

✓ View Leave

✓ Leave Approval

✓ Leave Calendar

✓ Timeline

✓ Review Step

✓ Step Validation

✓ Filters

✓ Pagination

✓ Employee Photo

✓ Status Badges

✓ Flash Messages

✓ Breadcrumb

✓ Sidebar Active Menu

✓ Responsive Layout

✓ No Undefined Variables

✓ No Broken Images

✓ No Duplicate Submit

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

progress.md

Include

- Leave UI refined
- Wizard validation improved
- Review step completed
- Flash spacing improved
- Calendar refined
- Timeline improved
- Approval UI refined
- Duplicate submission prevention added
- Browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Leave Apply wizard is polished.
- Step validation works correctly.
- Review step displays complete data.
- Employee photos display correctly.
- Calendar renders correctly.
- Timeline displays correctly.
- Approval page is polished.
- Duplicate submissions are prevented.
- Flash messages are clean.
- Responsive layout is preserved.
- Existing Admin Panel design remains unchanged.
- Manual browser testing passes.
- All verification commands pass successfully.