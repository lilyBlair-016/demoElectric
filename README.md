# Puihaha Electric

A CodeIgniter 4 web application demonstrating login/session authentication,
CRUD functionality, and pagination for an Electric Company system.

## Features

- Landing page, About, Services, and Contact pages
- User registration and email/password login
- Session authentication and protected dashboard
- Customer Account create, list, view, edit, and delete
- Dashboard totals by connection type and active status
- Pagination with 10 customers per page
- Logout, validation, and CSRF protection on login and customer-account forms

## Technology

- CodeIgniter 4.5.5 and PHP 8.1 or newer
- MySQL/MariaDB
- HTML, CSS, JavaScript, Bootstrap 5, and Font Awesome
- XAMPP for local development

Bootstrap and Font Awesome load from external CDNs, so an internet connection
is needed for their styling and scripts.

## Database connections

| Connection group | Database | Table | Purpose |
| --- | --- | --- | --- |
| `default` | `electric_company` | `customer_accounts` | CRUD, summary, pagination |
| `auth` | `electriccompany` | `users` | Registration and login |

These are separate databases. Do not merge or rename them. The repository's
`database/schema.sql` contains only their table definitions, with no records
or credentials. Other SQL dumps and professor reference projects are excluded.

## Local setup with XAMPP

1. Clone the repository into `C:\xampp\htdocs\demoElectric`.
2. Enable PHP extensions `intl`, `mbstring`, and `mysqli`. The optional HTTP
   integration check also requires `curl`.
3. This project includes its `system/` framework and bundled runtime libraries;
   the current application runs without `vendor/`. Keep `system/` in place.
   If you need Composer dependencies or development tools, run `composer install`
   from the project root. No Composer lock file was present in this project;
   review and commit the generated `composer.lock` when adopting that workflow.
4. Copy `.env.example` to `.env` (PowerShell: `Copy-Item .env.example .env`).
   On an existing installation, keep your current `.env` instead of overwriting it.
   Configure the base URL and both database connections for your machine.
   Set any actual database passwords only in `.env`, which Git ignores.
5. Start Apache and MySQL in the XAMPP Control Panel. Open phpMyAdmin and import
   `database/schema.sql` from its Import tab. It creates the two databases and
   tables if absent; it does not erase data or migrate existing tables.
6. Ensure PHP can write to `writable/cache`, `writable/logs`, `writable/session`,
   and `writable/uploads`. The required directory placeholders are included.
7. Open `http://localhost/demoElectric/`. The root `.htaccess` requires Apache's
   rewrite module and serves the existing assets from `public/assets`.
8. Visit `/register` to create a user, then `/login` to sign in with that user's
   email and password. New passwords are hashed. The classroom login also
   supports legacy plaintext records without rewriting their passwords.
   Accounts must be active; this activity allows any active user to access CRUD.
9. Use Add Customer to populate the initially empty customer list. Pagination
   appears as the number of accounts exceeds the page size.

There is no built-in administrator password or pre-created user in this repository.
The Contact form demonstrates validation and a confirmation message; it does not
send email or save enquiries.

## Useful URLs

- Home: `http://localhost/demoElectric/`
- Register: `http://localhost/demoElectric/register`
- Login: `http://localhost/demoElectric/login`
- Dashboard: `http://localhost/demoElectric/dashboard`
- Customers: `http://localhost/demoElectric/customer-accounts`

## Checks

Run from the project root:

```powershell
php spark routes
php tests/customer_accounts_smoke.php
```

The smoke check assumes XAMPP's localhost URL and the two local databases using
the default local connection settings. It temporarily creates its own uniquely
named user/customer records and removes them on completion. Run it only against
a local development installation. Existing rows are checked for preservation;
auto-increment counters may advance.

## Git and deployment

`.env`, Composer's `vendor/`, runtime cache/log/session/upload contents, SQL dumps,
local editor settings, and `professor_refs/` are ignored. The reviewed
`.env.example` and schema-only `database/schema.sql` are included.

To publish future changes after the GitHub remote is connected:

```powershell
git status
git add .
git diff --cached --stat
git commit -m "Describe your changes"
git push origin main
```

GitHub hosts the source repository. GitHub Pages cannot execute this PHP/MySQL
application. To run it online, use PHP-capable hosting with MySQL/MariaDB and
Apache or Nginx. The current root front controller is intended for local XAMPP;
a production deployment should serve `public/`, configure its base URL, keep
private files outside the web root, and use production database credentials.
