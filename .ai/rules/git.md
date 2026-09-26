---
paths:
  - '**'
---

# Git Commit Rules

Before creating or staging any git commit, strictly follow the repository guidelines in `commit_rules.md`:

## Core Invariants
1. **Commit Message Language**: All commit messages must be written in **Bahasa Indonesia**. Never mix English unless using technical terms that have no Indonesian equivalent (e.g., `fix`, `refactor`, class names).
2. **Conventional Commits**: Use the format `<type>(<scope>): <summary>`.
   - Allowed types: `feat`, `fix`, `refactor`, `style`, `test`, `docs`, `chore`, `perf`, `build`.
3. **Subject Line Length**: The subject line must not exceed **±72 characters**.
4. **Imperative Verb in Base Form**: The summary must start with a base imperative Indonesian verb: `tambah`, `hapus`, `perbaiki`, `ubah`, `pindahkan`, `perbarui`, `ganti`, `bersihkan`.
5. **No Trailing Period**: Do NOT end the subject line with a period (`.`).
6. **Atomic Commits (One Purpose Per Commit)**: Never combine multiple unrelated changes into a single mega-commit. Separate changes into atomic, logical commits:
   - `feat`: new features or components.
   - `refactor`: code/view restructuring without behavioral change.
   - `style`: code formatting (Pint), CSS adjustments, or inline style cleanups.
   - `docs`: documentation or rule files updates.
   - `test`: test additions or updates.
7. **Security & Secrets**: Never commit `.env` or sensitive credentials.
