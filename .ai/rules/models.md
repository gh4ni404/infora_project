---
paths:
  - 'app/Models/**'
---

# Models

## Model Mass Assignment and Local Query Scopes
Define mass assignment using $fillable allow-lists. Implement reusable query filters as local scopeXxx(Builder $query, ...) methods with explicit typehints rather than dedicated Builder classes.

## Attribute Text Normalization
Normalize text attributes inside Attribute::make(set: ...) using TextFormatter::titleCase() to preserve educational acronyms or TextFormatter::upper().
