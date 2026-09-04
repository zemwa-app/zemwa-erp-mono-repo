Viewed module.json:1-16
Viewed version.txt:1-2
Listed directory UniversalBundle
Viewed version.txt:1-2
Viewed version.txt:1-2

Here is the breakdown of why versioning is structured this way and how it works for the main app and modules:

### 1. Why the version number is not in `package.json`
* **`package.json` is for Node.js / NPM**: In web apps, `package.json` is used purely by frontend tooling (like Node, Vite, Webpack, or Laravel Mix) to manage Javascript packages and compile assets.
* **This is a PHP/Laravel App**: The actual business logic runs on the PHP server. When the app is deployed in production, Node/NPM is not used. 
* **Backend Version Storage**: To keep the backend version decoupled from the frontend build tools, Laravel applications store their version number in:
  1. A simple text file (like [`version.txt`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/version.txt) and [`public/version.txt`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/public/version.txt)).
  2. Inside the database settings tables.
  3. Or inside Laravel configuration files (e.g., `config/app.php`).

---

### 2. Separate version numbers for each component
You are correct. Because the application uses a modular architecture (based on the `laravel-modules` package), the main application, the bundles, and each custom module have **completely separate version numbers and release cycles**:

* **Main App Version**: **`6.0.15`** 
  * Defined in: [`version.txt`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/version.txt) and [`public/version.txt`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/public/version.txt).
* **Universal Bundle Module Version**: **`2.0.16`**
  * Defined in: [`Modules/UniversalBundle/version.txt`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/Modules/UniversalBundle/version.txt).
* **Pro Bundle Module Version**: **`2.0.0`**
  * Defined in: [`Modules/ProBundle/version.txt`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/Modules/ProBundle/version.txt).
* **Subdomain Module Version**: **`3.0.4`**
  * Defined in: [`Modules/Subdomain/version.txt`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/Modules/Subdomain/version.txt).

Each module inside the [`Modules/`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/Modules) directory has its own `version.txt` containing its specific version code, allowing you to update and version each feature module independently.