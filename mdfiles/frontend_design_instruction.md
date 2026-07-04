---

# EXISTING UI & LAYOUT COMPATIBILITY RULES (MANDATORY)

Before making any changes, first inspect the existing Admin Panel.

If the required layout, components, styling, navigation, forms, tables, cards, alerts, breadcrumbs, or reusable Blade partials already exist, they MUST be reused.

Do NOT redesign or recreate the UI.

Always extend the existing Admin Panel implementation.

---

## Existing Layout Detection

Before creating any Blade file, first check whether the following already exist.

- Main Layout
- Sidebar
- Navbar
- Footer
- Breadcrumb
- Cards
- Tables
- Forms
- Buttons
- Alerts
- Pagination
- Modal
- Validation Error Display
- Flash Messages

If available, continue using the existing implementation.

Do NOT duplicate them.

---

## Existing CSS & JS Rules

Reuse all existing project assets.

Do NOT introduce

- New CSS framework
- New JavaScript framework
- New UI library
- New Bootstrap version
- New Font library
- New Admin Template

Use the same

- CSS classes
- JavaScript
- jQuery plugins
- Bootstrap components
- Font Awesome icons
- Existing responsive utilities

No duplicate CSS or JS files should be created.

---

## Existing Design Rules

The HRMS module must look like it was originally developed as part of the existing Admin Panel.

Maintain exactly the same

- Colors
- Typography
- Spacing
- Card design
- Table design
- Form layout
- Button styles
- Input styles
- Icons
- Header/Footer styling
- Sidebar styling
- Animations
- Hover effects
- Responsive behavior

Do NOT redesign any existing page.

---

## Existing Component Reuse

Before creating new HTML, first search for reusable Blade components or partials.

Reuse existing

- Form layouts
- Card layouts
- Table layouts
- Modal layouts
- Alert layouts
- Breadcrumb layouts
- Pagination layouts

Only create new Blade markup when no reusable component exists.

---

## Existing Form Rules

All HRMS forms MUST use the same form structure already used throughout the Admin Panel.

Reuse the existing

- form-group classes
- row/col layout
- input classes
- select classes
- textarea classes
- checkbox classes
- radio classes
- validation classes
- submit button styles
- cancel button styles

Do NOT introduce a different form design.

The HRMS forms must be visually identical to the existing Admin Panel forms.

---

## Existing Table Rules

Reuse the same table design already available.

Maintain

- Table classes
- Responsive wrapper
- Action button styling
- Badge styling
- Pagination styling
- Search layout

Do NOT introduce a different table layout.

---

## Existing Responsive Rules

The existing responsive behavior must remain unchanged.

Reuse the same Bootstrap grid and responsive utilities already used in the project.

Do NOT implement a different responsive strategy.

The HRMS pages must behave exactly like existing Admin Panel pages on

- Desktop
- Tablet
- Mobile

---

## Existing Functionality Protection

Do NOT modify, remove, rename, or refactor any existing working functionality.

Specifically preserve

- Authentication
- Dashboard
- Holiday Module
- Leave Type Module
- Existing Sidebar
- Existing Navbar
- Existing Layout
- Existing CSS
- Existing JavaScript
- Existing Blade Components
- Existing Routes
- Existing Menu Behaviour

Only extend the existing application.

Never break existing functionality.

---

## Implementation Strategy

Before implementing any new HRMS page

1. Inspect the existing Admin Panel.
2. Reuse existing layouts.
3. Reuse existing CSS classes.
4. Reuse existing JavaScript.
5. Reuse existing Blade partials.
6. Reuse existing form structure.
7. Reuse existing responsive layout.
8. Only add new HTML where absolutely necessary.

The final UI must appear as a natural extension of the existing Admin Panel and should be visually indistinguishable from the already implemented modules.

---