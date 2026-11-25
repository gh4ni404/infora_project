---
paths:
  - 'app/Http/**'
  - 'app/Support/SidebarCache.php'
---

# Http

## Role and Permission Authorization
Do not use Laravel Policies or Gates. Enforce authorization via SuperAdminMiddleware on route groups and explicit helper methods on the User model (isSuperAdmin(), hasMenuAccess(), hasSubMenuAccess()).

## Dynamic Menu Access Enforcement via SuperAdminMiddleware
SuperAdminMiddleware acts as the single, lightweight authorization guard for all internal application routes:
1. **Super Admin Root Bypass:** Users with isSuperAdmin() === true bypass all checks and have universal access.
2. **Utility Routes Exemption:** Essential utility routes (logout, system.under-development, wilayah.dropdown.*, storage.*) are accessible to all authenticated active users.
3. **Automatic Route-to-Menu Mapping:** Operational routes are matched to active Menu or SubMenu entities via SidebarCache::findByRoute($request). Resolution MUST strictly prioritize exact route matches before evaluating prefix/wildcard patterns (`.*`). This prevents broader parent routes (e.g. `dashboard`) from intercepting more specific child routes (e.g. `dashboard.admin`).
4. **HTTP Method to CRUD Action:** Automatically resolve the required action:
   - GET / HEAD -> can_view (routes ending in .create require can_create; routes ending in .edit require can_edit).
   - POST -> can_create.
   - PUT / PATCH -> can_edit.
   - DELETE -> can_delete.
5. **Smart Redirection & Error Handling:**
   - For API / JSON requests: abort with 403 JSON response.
   - For in-app navigation with valid previous session: redirect()->back()->with('error', 'Akses ditolak...').
   - For direct URL access without referer: redirect to user's first allowed menu via SidebarCache::getFirstAllowedUrl($user).
   - For test / CLI direct requests without permissions: abort(403).

## Menu Active State Disambiguation (Menu::isRouteActive)
When checking active state on menus with `isRouteActive()`, exact route matches must take precedence. Wildcard prefix checks (`.*`) must cross-reference `SidebarCache::findByRoute(request())` to ensure a sibling menu with a more specific route name is not falsely marked as active.

## Dynamic Post-Login Landing Page
In LoginController, non-superadmin users are redirected dynamically:
- If the user is an Admin Sekolah (`$user->isAdmin()`) and has access to the admin dashboard -> redirect to route('dashboard.admin').
- If the user has access to the Dashboard menu -> redirect to route('dashboard').
- If the user does not have Dashboard access -> redirect to their first allowed menu via SidebarCache::getFirstAllowedUrl($user).
- If the user has no menu permissions assigned -> immediately logout and reject with a descriptive Indonesian validation error.

## Dual-Mode Node Governance (`INFORA_NODE_ROLE`)
INFORA runs from a single codebase configured via `config('infora.node_role')` (`'school'` default or `'master'`):
1. **Master Portal Isolation:** Routes under `/master-control/*` (`master-control.*`) must use `master_node` middleware (`EnsureMasterNode`, returning `404` in `school` mode) and remain strictly restricted to `super_admin`.
2. **School Route Blocking on Master:** School-operational route patterns configured in `config('infora.school_only_route_patterns')` must remain protected by `block_school_on_master` (`BlockSchoolRoutesOnMaster`, returning `404` in `master` mode).
3. **Master Menu Entitlement (`is_master_enabled`):** Both `SidebarCache::fetchActiveTree()` and `SidebarCache::findByRoute()` filter out menus/sub-menus where `is_master_enabled` is `false` so school admins cannot view, access, or assign Master-locked menus to users.
