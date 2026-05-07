# Project Manager Selection UI Implementation

## Summary
Implemented department-scoped project manager selection UI for department heads (chef_departement) creating and editing projects. The implementation ensures that only chefs de projet from the same department can be selected as project managers.

## Changes Made

### 1. Backend - Controller (ProjectController.php)

#### Added Method: `getAvailableProjectManagers()`
- Filters available project managers based on user role
- For chef_departement: Returns only chefs de projet from their department and sub-structures
- For admin: Returns all active chefs de projet
- For other roles: Returns only chefs de projet from their structure

#### Updated Method: `create()`
- Now passes `$availableProjectManagers` to the view
- Cleanly separates business logic from the view

#### Updated Method: `store()`
- Now accepts and validates `user_id` parameter (required)
- Validates that selected user_id is in the available managers list
- Prevents unauthorized cross-department manager assignments
- Returns 403 Forbidden if invalid manager selected

#### Updated Method: `edit()`
- Passes `$availableProjectManagers` to the view for department-scoped editing

#### Updated Method: `update()`
- Accepts and validates `user_id` parameter
- Enforces same authorization checks as store()
- Prevents changing project manager to unauthorized users

### 2. Frontend - Views

#### create.blade.php
- Replaced inline PHP logic with simple variable loop
- Uses `$availableProjectManagers` passed from controller
- Added descriptive label: "Responsable (Chef de Projet)"
- Shows manager name and email for better UX

#### edit.blade.php
- Same improvements as create.blade.php
- Properly displays currently selected manager
- Uses `old()` helper for form preservation on validation errors

### 3. Database Factories

#### StructureFactory.php (NEW)
- Creates test department structures
- Includes required fields: `type` and `level`
- Enables proper testing of department-scoped queries

#### ProjectFactory.php (NEW)
- Creates test projects with proper relationships
- Supports customization via factory methods
- Generates realistic date ranges

### 4. Model Updates

#### Project Model
- Added `HasFactory` trait to enable factory usage in tests
- Ensures factories work correctly in feature tests

### 5. Tests - ProjectCreationWithManagerTest.php

Created 5 comprehensive tests:

1. **test_chef_departement_can_create_project_with_selected_manager**
   - Verifies chef_departement can create project with selected chef_projet from same department
   - Confirms project_manager_id is stored correctly

2. **test_chef_departement_cannot_assign_manager_from_different_department**
   - Ensures authorization check prevents cross-department assignments
   - Returns 403 Forbidden on unauthorized attempt

3. **test_admin_can_create_project_with_any_manager**
   - Confirms admin has no department restrictions
   - Can assign any active chef_projet as manager

4. **test_project_creation_requires_user_id**
   - Validates that user_id is required field
   - Form submission fails without it

5. **test_edit_view_shows_available_managers**
   - Verifies edit form displays filtered managers
   - Confirms availableProjectManagers passed to view

### Test Results
- All 14 tests pass (9 notification + 5 project creation)
- No regressions in existing functionality

## Security Features

1. **Role-Based Access Control**
   - Only allows selection of users with chef_projet role
   - Enforces active status check

2. **Department Scoping**
   - Chef_departement can only assign managers from their department
   - Uses recursive structure tree query for sub-department support
   - Admin can assign from any department

3. **Validation**
   - user_id validation in create and edit forms
   - Authorization check before allowing assignment
   - Returns appropriate HTTP status codes (403 for forbidden)

## User Experience

1. **Cleaner Code**
   - Removed inline database queries from blade templates
   - Business logic properly separated in controller

2. **Better Form UX**
   - Clear label: "Responsable (Chef de Projet)"
   - Shows both name and email for disambiguation
   - Proper error messages for validation failures

3. **Proper Scoping**
   - Department heads only see relevant managers
   - Admin sees all available managers
   - Form selection persists on validation errors

## Files Modified/Created

```
✅ app/Http/Controllers/ProjectController.php - Added getAvailableProjectManagers(), updated create/store/edit/update
✅ resources/views/projects/create.blade.php - Updated to use controller data
✅ resources/views/projects/edit.blade.php - Updated to use controller data
✅ app/Models/Project.php - Added HasFactory trait
✅ database/factories/StructureFactory.php - Created
✅ database/factories/ProjectFactory.php - Created
✅ tests/Feature/ProjectCreationWithManagerTest.php - Created (5 tests)
```

## Testing & Validation

```bash
# Run project tests
php vendor/bin/phpunit --filter Project

# Run all tests with Project/Notification filters
php vendor/bin/phpunit --filter "Project|Notification"

# Result: 14/14 passing (9 notification + 5 project)
```

## Next Steps (Optional)

1. Add form field constraints (required field styling, help text)
2. Add frontend validation (JavaScript)
3. Add audit logging for manager changes
4. Add notifications when project manager is assigned/changed
5. Add permission levels for manager editing (can admin only change? can dept head change?)
