---
paths:
  - 'app/Http/**'
---

# Http

## Role and Permission Authorization
Do not use Laravel Policies or Gates. Enforce authorization via SuperAdminMiddleware on route groups and explicit helper methods on the User model (isSuperAdmin(), hasMenuAccess()).
