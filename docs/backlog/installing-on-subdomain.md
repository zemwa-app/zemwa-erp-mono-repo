To host the ERP application on the subdomain **`erp.zemwa.com`** and have tenant subdomains run as **`company1.erp.zemwa.com`**, you can do this purely via configuration. The codebase is already designed to support this structure.

Here is the step-by-step guide to setting this up:

---

### Step 1: Update your `.env` Configuration

Configure the ERP application to treat `erp.zemwa.com` as its primary base domain. Update these values in your `.env` file:

```env
APP_URL=https://erp.zemwa.com
MAIN_DOMAIN_NAME=erp.zemwa.com
MAIN_APPLICATION_SUBDOMAIN=
```

#### Why this works:
* **`MAIN_DOMAIN_NAME=erp.zemwa.com`**: Tells the system that the "base" domain is `erp.zemwa.com`. The helper function `getDomain()` will now return `erp.zemwa.com`.
* **`MAIN_APPLICATION_SUBDOMAIN=` (empty)**: You do not need to specify a subdomain here. Since the base domain of this Laravel installation is `erp.zemwa.com`, the main landing page and sign-in page will load directly on `erp.zemwa.com`.
* **Tenant Subdomain Registration**: When a new company (e.g., `company1`) registers, the system automatically appends `.erp.zemwa.com` to save it as `company1.erp.zemwa.com` in the database.

---

### Step 2: DNS & Web Server Configuration (Crucial)

To allow users to access their subdomains, you need to configure wildcards on your DNS provider and your server:

1. **DNS Wildcard Record**:
   Add a wildcard **A** (or **CNAME**) record pointing to your server's IP address:
   * **Host**: `*.erp` (under the `zemwa.com` zone)
   * **Value**: `[Your Server IP]`
   *(This ensures that `company1.erp.zemwa.com`, `company2.erp.zemwa.com`, etc., all route to your server.)*

2. **Web Server Wildcard (Nginx example)**:
   Ensure your web server configuration is listening for both the base domain and wildcard subdomains:
   ```nginx
   server_name erp.zemwa.com *.erp.zemwa.com;
   ```

---

### Step 3: Custom Domains Feature

The system handles custom domains by matching the request host directly with the database `sub_domain` column. 

When a user points their custom domain (e.g., `mycompany.com`) to your server:
1. The request host is `mycompany.com`.
2. The middleware queries `Company::where('sub_domain', 'mycompany.com')->first()`.
3. If it matches, the user is successfully logged into their space.

#### Enabling Custom Domains for Basic Plan Users:
The Custom Domain feature is registered as a module (`custom_domain`). It is controlled via the SaaS packages system:
1. Log in to your **Super Admin Dashboard**.
2. Go to **Packages** (or **Plans**).
3. Edit your **Basic Plan**.
4. Scroll to the **Allowed Modules** section, locate the **Custom Domain** module, and check/enable it.
5. Save the package.

No code modifications are required; this immediately enables the option for basic plan subscribers to configure custom domains.





Here is the analysis of why the previous developer had `MAIN_APPLICATION_SUBDOMAIN=` in the `.env` file, along with a review of other settings and checks related to installing the application on a subdomain.

---

### 1. Why the previous developer kept `MAIN_APPLICATION_SUBDOMAIN=` in `.env`

In standard Laravel SaaS installations (specifically **Worksuite SaaS**, which this project is based on), there are two ways to structure your domains:

#### **Setup A: Main Portal on Subdomain, Tenants on Root Domain**
Often, a business hosts their marketing website on the root domain (e.g., `zemwa.com`) using WordPress or Webflow, and installs the actual ERP portal on a subdomain (e.g., `app.zemwa.com` or `erp.zemwa.com`). 

However, they want the **tenants** to be subdomains of the root domain rather than nested (e.g., they want `company1.zemwa.com` instead of `company1.erp.zemwa.com`).
* **Config for this setup**:
  ```env
  MAIN_DOMAIN_NAME=zemwa.com
  MAIN_APPLICATION_SUBDOMAIN=erp.zemwa.com
  ```
* **Result**:
  * Root site: `zemwa.com`
  * Main Portal/Registration: `erp.zemwa.com`
  * Tenants: `company1.zemwa.com`, `company2.zemwa.com`

#### **Setup B: Tenants on Nested Subdomains (Your Setup)**
If you leave `MAIN_APPLICATION_SUBDOMAIN` empty and set the main domain name directly to the subdomain:
* **Config for this setup**:
  ```env
  MAIN_DOMAIN_NAME=erp.zemwa.com
  MAIN_APPLICATION_SUBDOMAIN=
  ```
* **Result**:
  * Main Portal/Registration: `erp.zemwa.com`
  * Tenants: `company1.erp.zemwa.com`, `company2.erp.zemwa.com`

**The previous developer left it empty** because:
1. They were running the project in a local environment (e.g. `localhost:8000`), where nested subdomains weren't being used.
2. Under Setup B, leaving `MAIN_APPLICATION_SUBDOMAIN` empty is the correct and intended configuration, as it treats `erp.zemwa.com` as the root domain for all tenant URLs.

---

### 2. Other Locks and Settings in the Codebase

We analyzed all middleware, models, configuration files, and traits using the codebase. Here is what we found regarding other potential locks or settings:

#### **A. Custom Domain Dependency Lock**
In [`app/Models/Module.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/app/Models/Module.php#L2112-L2118), there is a dependency check:
```php
if (strtolower($key) == 'subdomain') {
    $moduleInactive[strtolower('custom_domain')] = strtolower($module);
}
```
* **What it means**: The `custom_domain` feature is tied to the `subdomain` module. If the `subdomain` module is disabled inside your Super Admin panel, the custom domain module will automatically be deactivated and locked as well.

#### **B. Google OAuth Redirects**
In [`app/Traits/GoogleOAuth.php`](file:///d:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo/app/Traits/GoogleOAuth.php#L14-L21), the Google login redirection URI is dynamically determined:
```php
$subdomain = config('app.main_application_subdomain');
$rootCrmSubDomain = preg_replace('#^https?://#', '', $subdomain);
$domain = request()->getScheme() . '://' . ($rootCrmSubDomain ?: getDomain());
```
* **What it means**: If `MAIN_APPLICATION_SUBDOMAIN` is empty, it falls back to `getDomain()` (which returns your `MAIN_DOMAIN_NAME`). Thus, if `MAIN_DOMAIN_NAME=erp.zemwa.com`, your Google OAuth redirection URI is dynamically built as `https://erp.zemwa.com/account/settings/google-auth`. This ensures social logins continue to work correctly under the subdomain setup.

#### **C. Short Domain Configuration (`SHORT_DOMAIN_NAME`)**
In `config/app.php`, there is a configuration key:
```php
'short_domain_name' => env('SHORT_DOMAIN_NAME', false),
```
* **What it means**: If your domain name has multiple dots (like `erp.zemwa.com`), setting `SHORT_DOMAIN_NAME=true` tells the URL-parsing helper `getDomain()` to treat it differently (e.g. parsing subdomains differently when the domain ends in double extensions like `.co.uk`). 
* **Recommendation**: **Keep it `false` (or leave it out of `.env` entirely)** so that `erp.zemwa.com` is consistently parsed as a single root domain name.



### Part 1: How to test subdomains locally (Localhost)

You actually **can** test subdomains on localhost! You don't need a VPS just to test them. Here are the two best ways to do it locally:

#### **Option A: Using `lvh.me` (Easiest, no setup required)**
`lvh.me` is a free service where the domain and all its subdomains are hard-coded to resolve back to `127.0.0.1` (localhost).
1. Change your `.env` settings to:
   ```env
   APP_URL=http://lvh.me:8000
   MAIN_DOMAIN_NAME=lvh.me:8000
   ```
2. Start your server:
   ```bash
   php83 artisan serve
   ```
3. Open your browser and navigate to:
   * Main App: `http://lvh.me:8000`
   * Tenant App: `http://company1.lvh.me:8000` (this will automatically route to your local server, and Laravel will detect the subdomain `company1`).

#### **Option B: Editing the Windows Hosts File**
If you want to use custom local domains (like `zemwa.test` and `company1.zemwa.test`):
1. Open Notepad (or VS Code) **as Administrator**.
2. Open the file `C:\Windows\System32\drivers\etc\hosts`.
3. Add these lines to the bottom of the file:
   ```text
   127.0.0.1 zemwa.test
   127.0.0.1 company1.zemwa.test
   127.0.0.1 company2.zemwa.test
   ```
4. Update your `.env` to `MAIN_DOMAIN_NAME=zemwa.test` and access them via `http://zemwa.test:8000`.

---

### Part 2: How to Deploy to a VPS

When you are ready to deploy to production on a VPS (like DigitalOcean, Linode, AWS, or Vultr running Ubuntu), here is the deployment process:

#### **Step 1: Point your DNS Records to the VPS**
Log into your domain provider (Cloudflare, Namecheap, GoDaddy, etc.) and add **two** DNS records:
1. **A Record**: 
   * Name: `erp`
   * IP Address: `[Your VPS IP]`
2. **Wildcard A Record** (This handles `*.erp.zemwa.com` dynamically):
   * Name: `*.erp`
   * IP Address: `[Your VPS IP]`

---

#### **Step 2: Prepare the VPS Environment**
Connect to your VPS via SSH and install the PHP 8.3 stack:
```bash
# Update server packages
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 and common Laravel extensions
sudo apt install -y php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath

# Install Nginx and MySQL (if hosting MySQL on the same VPS)
sudo apt install -y nginx mysql-server
```

---

#### **Step 3: Clone Code and Set Up permissions**
1. Clone your repository to `/var/www/zemwa-erp`.
2. Copy your `.env` and set it for production:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://erp.zemwa.com
   MAIN_DOMAIN_NAME=erp.zemwa.com
   MAIN_APPLICATION_SUBDOMAIN=
   ```
3. Install production dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force
   ```
4. Set folder permissions so Nginx can write to the log and cache folders:
   ```bash
   sudo chown -R www-data:www-data /var/www/zemwa-erp
   sudo chmod -R 775 /var/www/zemwa-erp/storage /var/www/zemwa-erp/bootstrap/cache
   ```

---

#### **Step 4: Configure Nginx**
Create an Nginx configuration file at `/etc/nginx/sites-available/erp.zemwa.com`:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name erp.zemwa.com *.erp.zemwa.com; # Listens to the main domain and wildcards
    root /var/www/zemwa-erp/public;

    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```
Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/erp.zemwa.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

#### **Step 5: Set up SSL Certificates (Let's Encrypt)**
To secure both the main subdomain and wildcard subdomains with HTTPS, you need a wildcard SSL certificate:
```bash
sudo apt install certbot python3-certbot-nginx
```
Because Let's Encrypt requires a DNS challenge (DNS-01) to verify ownership for wildcard certificates (`*.erp.zemwa.com`), run:
```bash
sudo certbot certonly --manual --preferred-challenges=dns -d erp.zemwa.com -d *.erp.zemwa.com
```
Follow the prompts to add a temporary `TXT` record to your DNS settings. Once verified, configure Nginx to use the generated certificates.