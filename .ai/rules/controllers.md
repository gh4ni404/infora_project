---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Direct Eloquent Queries in Controllers
Query Eloquent models directly in controller actions without repository or dedicated query classes. Return explicit response types (View, RedirectResponse, JsonResponse) and pass view variables using compact().

## Input Retrieval and Validation Consumption
Retrieve query string and filter parameters using $request->query("param", default) with explicit scalar casting. Consume validated write data using $request->validated().

## Indonesian Language Flash Notifications
Write user-facing redirect notifications in Indonesian using ->with("success", "...") and ->with("error", "...") session flash helpers.

## School Multi-Tenancy Context & Authorization (No Overengineering)
For school-scoped controllers (Dashboard, Konfigurasi Sekolah, Master Data Jurusan, etc.), use the lightweight base methods on `App\Http\Controllers\Controller` instead of building custom DTOs, traits, or global tenancy middleware:
1. **School Context Resolution (`resolveSchoolContext($request)`):**
   - Returns `[$selectedSchool, $selectedSchoolId, $schools]`.
   - Super Admin can switch school context via query parameter `?school_id=...` and receives the full active schools collection for dropdown selectors.
   - Non-superadmin users are strictly locked to `$user->school` (`$user->school_id`) and receive an empty `$schools` collection (`collect()`).
2. **Tenant IDOR Protection (`authorizeSchool($request, $schoolId)`):**
   - Always call `$this->authorizeSchool($request, $resource->school_id)` in mutating actions (`update`, `destroy`, etc.) to prevent non-superadmin users from modifying other schools' resources.
   - For create actions, ensure `$data['school_id'] = $request->user()->school_id` when the user has an assigned school.
3. **Redirect Parameter Persistence (`schoolRedirectParams($request, $schoolId, $params)`):**
   - Automatically prepends `['school_id' => $schoolId]` when the authenticated user is a Super Admin, keeping URL query strings clean for school-specific users.

