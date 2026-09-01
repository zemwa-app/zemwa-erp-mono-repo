# Zemwa ERP - Developer Version Notes

This file logs version releases and detailed change records. Changes are tracked separately for the Core System, Universal Bundle, and Pro Bundle.

Consolidated Package Version: **v2.2.0**

---

## 1. Core System
* **Current Version**: `v6.2.0`
* **Status**: Stable

### Change Log
* **Initial Setup**: Established the current baseline version at `v6.0.15`.
* **Sub-module Route Adjustments**: Updated the web routing files to ensure seamless integration with the renamed bundles settings pathing.
* **License Verification Bypass (v6.1.0 / Consolidated v2.1.0)**:
  * Modified `isLegal()` in `AppBoot` trait to return `true` immediately, bypassing main app license verification on all domains.
  * Modified `isModuleLegal()` in `ModuleVerify` trait to return `true` immediately, bypassing all module-specific licensing verification (e.g. Subdomain module).
* **System Updates Restriction (v6.1.0 / Consolidated v2.1.0)**:
  * Added `getSystemUpdateAttribute()` accessor to `GlobalSetting.php` to force-disable `system_update` dynamically across all views and components (automatically hiding the Updates settings sidebar item, dashboard update alerts, and toggle switch).
  * Disabled Auto-Updates by forcing `checkPermission()` to return `false` in `UpdateScriptVersionController.php`.
  * Disabled manual update installations by overriding the `install()` method in `UpdateAppController.php` to reject system update zip extractions with an error.
* **cPanel Atomic Deployment Architecture (v6.2.0 / Consolidated v2.2.0)**:
  * Created automated release-based deployment script ([`scripts/deploy.sh`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/scripts/deploy.sh)) supporting atomic symlink switches (`mv -Tf`).
  * Built non-locking pre-migration MySQL backups with InnoDB transaction consistency (`--single-transaction --quick --routines --triggers --events`).
  * Added explicit cPanel PHP binary path resolution (`PHP_BIN`), strict abort-on-backup-failure, and safe route caching fallback.
* **Super Admin Dashboard Subdomain & Logo Fix (v6.2.0 / Consolidated v2.2.0)**:
  * Fixed missing `sub_domain`, `logo`, and `light_logo` selections on eager loaded company relationships across Top Paying Companies, Renewals, and Invoices Due Soon in [`DashboardController.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/app/Http/Controllers/SuperAdmin/DashboardController.php), resolving false-positive "subdomain not added" warnings.
* **Typography & Readability Overhaul (v6.2.0 / Consolidated v2.2.0)**:
  * Integrated Google Font **`Inter`** globally across [`layouts/app.blade.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/resources/views/layouts/app.blade.php) and [`layouts/public.blade.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/resources/views/layouts/public.blade.php), replacing the uninstalled Apple-exclusive `Helvetica Neue`.
  * Improved table contrast by darkening header text (`#475569`) and body cells (`#1e293b`), eliminating thin, washed-out, and jagged font rendering on Windows screens.
* **Single Source of Truth Version Management (v6.2.0 / Consolidated v2.2.0)**:
  * Created central `app_version()` helper function in [`start.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/app/Helper/start.php) reading from the root `version.txt`.
  * Refactored [`sidebar.blade.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/resources/views/sections/sidebar.blade.php), [`HomeController.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/app/Http/Controllers/HomeController.php), [`CustomModuleController.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/app/Http/Controllers/CustomModuleController.php), [`Module.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/app/Models/Module.php), and `/version.txt` web routes to consume the single version source.

---

## 2. Universal Bundle
* **Current Version**: `v2.0.15` (Base Version)
* **Status**: Stable

### Change Log
* **Initial Setup**: Established the current baseline version at `v2.0.15`.
* **Sidebar UI Upgrades**: Synchronized the sidebar component list with module activation triggers.

---

## 3. Pro Bundle
* **Current Version**: `v2.0.0`
* **Base Version**: *None (Developed recently)*
* **Status**: Active Release

### Change Log (v2.0.0)
* **Initial Release**: Packaged the new premium modules (`LandingPagePro`, `LeadFormsPro`, `TrainingPro`, `PublicAssessmentPro`) as the initial Pro Bundle release.
* **UI Clean-up (Removing "Pro" Text)**:
  * Stripped the "Pro" text suffixes from user-facing navigation lists and sidebar menus inside route files (`Routes/web.php`) and English localization configs (`Resources/lang/eng/app.php`).
  * Preserved internal controller names (e.g. `->names('landingpagepro')`) to ensure existing controllers and templates continue resolving correctly.
* **Super Admin Settings 500 Route Error Fix**:
  * Added explicit settings route naming aliases (`->names(...)`) to all four submodules' settings resources in their respective `web.php` route files:
    * `landingpage-settings` -> `landingpagepro-settings`
    * `leadforms-settings` -> `leadformspro-settings`
    * `training-settings` -> `trainingpro-settings`
    * `publicassessment-settings` -> `publicassessmentpro-settings`
  * This cleaned up the settings URLs (hiding "Pro" text) while preventing `RouteNotFoundException` (500 Server Error) in the settings dashboard.
* **Sales Returns Documentation**:
  * Compiled standalone training documentation guides (`index.html` and `sales-returns-guide-v3.pdf`) under the portable `zemwa-erp-docs/` folder, incorporating the real company logo and side-by-side screenshots with cropped blank spaces.
