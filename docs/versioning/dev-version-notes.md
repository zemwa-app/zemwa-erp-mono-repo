# Zemwa ERP - Developer Version Notes

This file logs version releases and detailed change records. Changes are tracked separately for the Core System, Universal Bundle, and Pro Bundle.

Consolidated Package Version: **v2.0.0**

---

## 1. Core System
* **Current Version**: `v6.0.15` (Base Version)
* **Status**: Stable

### Change Log
* **Initial Setup**: Established the current baseline version at `v6.0.15`.
* **Sub-module Route Adjustments**: Updated the web routing files to ensure seamless integration with the renamed bundles settings pathing.

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
