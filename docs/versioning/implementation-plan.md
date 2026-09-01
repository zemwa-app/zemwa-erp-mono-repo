# Zemwa ERP - Versioning Implementation Plan

This document details the implementation steps for executing version releases and tagging for the consolidated release **v2.2.0**, including individual tracking for sub-modules.

---

## 1. Directory Structure

The version-controlled documentation is maintained under the project root:
```text
zemwa-erp-web-core/
└── docs/
    └── versioning/
        ├── requirements.md           # Naming conventions & base versions specifications
        ├── implementation-plan.md     # Code tagging & release deployment workflow
        └── dev-version-notes.md       # Consolidated developer changelog
```

---

## 2. Implementation Steps

### Step 1: Update Configuration Files
Ensure that version strings inside configuration and composer files match the defined release numbers:
* **Core System**: Update `version.txt` or target version constant to `6.2.0` (base).
* **Universal Bundle**: Ensure sub-module metadata version matches `2.0.15` (base).
* **Pro Bundle**: Define metadata version string as `2.0.0` (baseline).

### Step 2: Tagging Strategy
When deploying to git repositories, use the following tag formats:
* Consolidated Release tag: `v2.2.0`
* Core System tag: `core-v6.2.0`
* Universal Bundle tag: `universal-v2.0.15`
* Pro Bundle tag: `pro-v2.0.0`

### Step 3: Run Database Schema Migrations
During deployments, run migrations in sequential layers:
1. Run Core System migrations:
   ```bash
   php artisan migrate
   ```
2. Run Bundle migrations:
   ```bash
   php artisan module:migrate
   ```

### Step 4: Clear Application and Route Caches
Ensure the routes and settings lists refresh cleanly:
```bash
php artisan optimize:clear
```
