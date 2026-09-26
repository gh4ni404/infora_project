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
