---
paths:
  - 'tests/**'
---

# Tests

## Pest Testing Conventions
Write tests using Pest syntax (test(), expect(), beforeEach()). Test against real database interactions using RefreshDatabase and authenticate super admin users via actingAs($this->superAdmin).
