---
paths:
  - 'database/migrations/**'
---

# Migrations

## Foreign Keys and Reversible Migrations
Define foreign keys using $table->foreignId("column_id")->constrained("table_name") with explicit cascade or null onDelete behaviors. Always implement a reversible down(): void method.
