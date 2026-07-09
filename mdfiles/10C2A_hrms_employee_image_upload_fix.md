# HRMS Employee Image Upload Fix Task (Laravel 10)

## Objective

This task fixes the Employee Profile Image upload functionality.

The image must be uploaded successfully, stored inside the project's public upload directory, saved in the database, and displayed throughout the Employee module.

This is a bug-fix task only.

No new functionality should be introduced.

---

# IMPORTANT RULES (MANDATORY)

## Laravel Version

- Use Laravel 10 only.
- Do NOT use Laravel 11 or 12 features.

---

# SCOPE

Only fix

- Image Upload
- Image Storage
- Image Display
- Existing Upload Service (if required)
- Existing UserService (if required)
- Existing Employee Blade Views

Do NOT modify

- Database structure
- Migrations
- Routes
- Controllers architecture
- Business Logic

---

# STORAGE LOCATION (MANDATORY)

Employee profile images MUST be stored inside

```
public/uploads/employees/
```

If the folder does not exist

Create it automatically.

Do NOT store employee images inside

```
storage/app
```

or

```
storage/app/public
```

unless the existing UploadService already requires it.

The final browser URL should be

```
/uploads/employees/{filename}
```

Example

```
public/uploads/employees/EMP001.jpg
```

---

# FILE NAMING RULE

Generate unique filenames.

Example

```
EMP001_20260705121530.jpg
```

or

```
employee_15_20260705121530.jpg
```

Never overwrite another employee image.

---

# IMAGE VALIDATION

Accept only

- jpg
- jpeg
- png
- webp

Maximum size

```
2 MB
```

Reject any other file types.

---

# IMAGE REPLACEMENT

When updating an employee

If a new image is uploaded

- Save the new image.
- Delete the old image from

```
public/uploads/employees/
```

if it exists.

If no new image is uploaded

Keep the existing image.

---

# DATABASE STORAGE

Store ONLY the relative path.

Example

```
uploads/employees/EMP001.jpg
```

Do NOT store

```
C:\xampp\htdocs\...

```

or

```
https://example.com/uploads/...
```

---

# IMAGE DISPLAY

Employee image must display correctly on

- Employee List
- Employee View
- Employee Edit

Use

```blade
asset($employee->profile_image)
```

or the existing helper.

Do NOT hardcode URLs.

---

# DEFAULT IMAGE

If employee image is missing

Display

```
uploads/default-avatar.png
```

or the existing default avatar.

Never display broken images.

---

# FORM REQUIREMENTS

Verify

```
enctype="multipart/form-data"
```

exists on

- Create Employee
- Edit Employee

Verify

```
<input type="file">
```

uses the correct field name.

---

# SERVICE LAYER

Reuse the existing UploadService.

If UploadService already exists

Fix it.

Do NOT create another upload service.

UserService must delegate upload handling to UploadService.

---

# DELETE OLD IMAGE

When replacing profile image

Delete the previous file safely.

If file does not exist

Do nothing.

Do NOT throw exceptions.

---

# BROWSER VERIFICATION

Verify manually

✓ Upload new image

✓ Image saved inside

```
public/uploads/employees/
```

✓ Database path saved

✓ Image visible in Employee List

✓ Image visible in Employee View

✓ Image visible in Employee Edit

✓ Replace image

✓ Old image removed

✓ Default avatar works

✓ Validation works

✓ No broken image icons

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
- Image upload fixed
- Image storage fixed
- Image display fixed
- Old image replacement implemented
- Manual browser verification completed
- Verification commands passed

---

# SUCCESS CRITERIA

Task is complete when

- Employee image uploads successfully.
- Images are stored inside

```
public/uploads/employees/
```

- Database stores only the relative image path.
- Employee List displays the uploaded image.
- Employee View displays the uploaded image.
- Employee Edit displays the uploaded image.
- Existing image is retained if no new image is uploaded.
- Old image is deleted when replaced.
- Default avatar works correctly.
- No broken image links exist.
- Existing architecture remains unchanged.
- All verification commands pass.