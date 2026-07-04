You are working inside a Laravel 10 project (NOT Laravel 11 or 12).

IMPORTANT FIRST STEP (MANDATORY):
Before doing anything, carefully analyze and understand the existing project structure, completed work, frontend design, and backend implementation.
This is REQUIRED so you can maintain consistency with existing patterns and avoid breaking changes.

Do NOT assume anything. Always inspect existing code first.

---

# GLOBAL RULES (MANDATORY)

## 1. Laravel Version Constraint
- Always use Laravel 10 conventions only.
- Do NOT use Laravel 11 or 12 features or syntax.

---

## 2. Code Quality & Security Standards
You MUST strictly follow:
- OWASP Top 10 security guidelines
- Prevent SQL Injection (use Eloquent or Query Builder only; avoid raw queries unless absolutely necessary)
- Proper input validation using Laravel Form Request classes
- Proper error handling using try-catch with meaningful messages
- Secure authentication practices
- Password hashing using Laravel Hash facade

---

## 3. Clean Architecture Principles
You MUST follow:
- DRY (Don’t Repeat Yourself)
- SOLID principles where applicable
- Separation of concerns
- Use Service Layer pattern for complex business logic

---

## 4. Database Design Rules
- NEVER modify existing tables, columns, or migrations
- Only create new migration files when required
- Maintain strict database integrity
- Use proper relationships:
  - One-to-One
  - One-to-Many
  - Many-to-Many (only if necessary)

---

## 5. Performance Optimization
You MUST:
- Use Eager Loading to avoid N+1 problem
- Use Laravel Scopes for reusable query logic
- Use caching (Cache::remember) where applicable
- Optimize database queries for performance

---

## 6. Laravel Best Practices
You MUST use:
- Proper Eloquent relationships
- Model Scopes for filtering logic
- Observers for model events (created, updated, deleted)
- Events & Listeners for decoupled architecture
- Form Request validation classes
- Resource Controllers (RESTful structure)
- API Resources (if API responses are required)

---

## 7. Frontend Consistency Rule
- ALWAYS follow the existing frontend template exactly
- Do NOT modify UI structure or styling patterns
- Reuse existing Blade components whenever possible
- Maintain full consistency with current layout system

---

## 8. Project Awareness Rule (CRITICAL)
Before implementing anything:
- Understand existing implementation completely
- Review related modules and dependencies
- Avoid duplication of existing logic
- Do NOT assume missing functionality
- If needed, provide a brief understanding summary before coding

---

## 9. Execution Flow Rule
Follow this strict order:
1. Understand current system
2. Analyze the given task
3. Plan the implementation approach
4. Implement the solution
5. Ensure no breaking changes
6. Verify correctness

---

## 10. Safety Rules (MANDATORY)
You MUST NEVER:
- Break authentication system
- Break existing CRUD functionality
- Modify existing working modules
- Change frontend design structure

You are only allowed to extend the system safely.

---

## 11. Progress Tracking (NEW RULE)
After completing ANY task, you MUST update a file named:

progress.md

This file must contain:
- What task was completed
- What files were created or modified
- What changes were made
- Current status of implementation
- Any issues or notes

This is mandatory for every execution step.

