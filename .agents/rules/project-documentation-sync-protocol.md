# Project Documentation Synchronization Protocol

This rule binds the entire feature development lifecycle, code refactoring, and user interface design across the INFORA project to ensure documentation and trackers consistently reflect the actual real-world state of the codebase.

---

## 1. Project Core Documentation Map
Each aspect of the project is documented in dedicated, complementary files:

| Documentation File | Role & Document Scope |
| :--- | :--- |
| **`detail/DESIGN.md`** | Master specification document for design guidelines, system architecture, color palettes, and layout principles. |
| **`docs/COMPONENTS.md`** | Comprehensive technical catalog of Anonymous Blade Components and companion JavaScript modules. |
| **`CHANGELOG.md`** | Structured version release history following [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and [SemVer](https://semver.org/). |
| **`TODO.md` & `detail/TODO.md`** | Feature implementation roadmap from Phase 1 through Phase 6 with progress checkboxes. |
| **`README.md` & `detail/README.md`** | High-level project summary, Docker quick-start guide, and detailed explanations of core capabilities. |
| **`dev` & `dev.ps1`** | Official cross-platform helper runner scripts automating Docker container commands. |

---

## 2. Mandatory Synchronization Protocol During Feature Development
1. **When a New Feature is Implemented:**
   - Add a concise entry under the `## [Unreleased]` section in `CHANGELOG.md` (categorized under `Added` or `Changed`).
   - Check the `[x]` checkbox on relevant tasks in `TODO.md` and `detail/TODO.md`.
   - Update the feature summary in `README.md` and `detail/README.md` if the feature constitutes a new primary capability.
2. **When Creating a New Blade Component:**
   - Register the component in `docs/COMPONENTS.md` complete with props, slots, Blade view examples, and its JavaScript interactions.
3. **When Architecture or Design Standards Change:**
   - Update related specifications in `detail/DESIGN.md` and ensure synchronization with rules stored in `.agents/rules/`.
4. **Testing Verification:**
   - Every new feature must be accompanied by Pest feature tests in `tests/Feature/` and confirmed 100% passing via `./dev test` before marking tasks as completed in documentation.

---

## 3. Markdown Link Portability Standard
1. **Mandatory Clean Relative Links:**
   - All cross-document links, code references, or file citations in repository documentation files (`docs/`, `detail/`, `CHANGELOG.md`, `README.md`, `TODO.md`) **MUST** use clean, portable relative markdown links (e.g., `../resources/js/infora-wilayah.js`, `COMPONENTS.md#section-6`, `tests/Feature/...`).
2. **Strict Prohibition of Local Machine Absolute URIs:**
   - Never write machine-specific local URIs (such as `file:///home/...`, `C:\Users\...`, or developer-specific workstation absolute paths) into any repository document.
   - Clickable `file:///...` links are strictly reserved for AI assistant IDE chat responses and must never be committed to Git.
