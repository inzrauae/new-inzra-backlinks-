# Deploying INZRA to cPanel

This app deploys via cPanel's Git Version Control feature, which runs `.cpanel.yml`
automatically on every push/pull. The Laravel application lives **outside** the
public webroot (`/home/seoweb/inzra-app/`); `public_html/` only ever receives a
copy of Laravel's `public/` folder. See `.cpanel.yml` and `deploy/public-index.php`
for the mechanics.

## One-time setup

### 1. Create the MySQL database

cPanel → **MySQL Databases** → create a database (e.g. `seoweb_inzra`).

### 2. Create the MySQL user

Same page → create a user with a strong, generated password. Note the
**full** username/database names — cPanel prefixes both with your account
username (e.g. `seoweb_inzra`, `seoweb_inzrauser`).

### 3. Assign database privileges

Add the user to the database with **All Privileges**.

### 4. Set up Git Version Control

cPanel → **Git Version Control** → create a repository pointing at this repo,
with the deploy path set to `/home/seoweb/repo` (or similar — NOT
`public_html`). Enable **Pull or Deploy** so pushes trigger `.cpanel.yml`.

### 5. Configure the document root

cPanel → **Domains** → set the domain's document root to
`/home/seoweb/public_html`. Do **not** point it at the repo or at
`inzra-app/public` directly — deployment copies files into `public_html`.

### 6. Configure `.env`

The first deploy copies `.env.example` to `/home/seoweb/inzra-app/.env` if no
`.env` exists yet (see `.cpanel.yml`). SSH in and edit it:

```
APP_NAME=INZRA
APP_ENV=production
APP_KEY=                      # generate in step 10
APP_DEBUG=false
APP_URL=https://inzra.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoweb_inzra
DB_USERNAME=seoweb_inzrauser
DB_PASSWORD=                  # the password from step 2

ADMIN_EMAIL=you@inzra.com
ADMIN_PASSWORD=                # a strong password — seeds the one admin account

MAIL_MAILER=smtp
MAIL_HOST=                     # cPanel mail host or a transactional provider
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=support@inzra.com
```

Subsequent deploys never touch an existing `.env` — it's excluded from the
rsync in `.cpanel.yml`.

### 7. Set the PHP version

cPanel → **MultiPHP Manager** → set PHP 8.2+ (whatever `composer.json`'s
`"php"` constraint currently resolves to — check after `composer install`)
for the domain.

### 8. Enable required PHP extensions

cPanel → **MultiPHP INI Editor** (or **Select PHP Extensions**) → ensure
`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`,
`bcmath`, `fileinfo`, `curl` are all enabled (standard on most cPanel PHP
builds).

### 9. Install Composer dependencies

Handled automatically by `.cpanel.yml` on every deploy
(`composer install --no-dev --optimize-autoloader`). For the very first
deploy, confirm via SSH that `composer` resolves — if not, adjust the path in
`.cpanel.yml` to your host's actual Composer binary
(`which composer` over SSH).

### 10. Generate `APP_KEY`

SSH in and run once:

```
cd /home/seoweb/inzra-app
php artisan key:generate
```

### 11. Run migrations

Handled automatically by `.cpanel.yml` (`php artisan migrate --force`) on
every deploy.

### 12. Run seeders

Not run automatically (seeding on every deploy would be wrong once the store
has real data). Run once, manually, after the first successful deploy:

```
cd /home/seoweb/inzra-app
php artisan db:seed
```

This creates the 3 product categories, 8 marketing categories, 40 products,
40 blog posts, and the one admin account from `ADMIN_EMAIL`/`ADMIN_PASSWORD`.

### 13. Configure storage

`.cpanel.yml` runs `php artisan storage:link` automatically. Confirm
`/home/seoweb/public_html/storage` exists as a symlink after the first
deploy (falls back to a no-op if symlinks aren't supported — check manually
if so).

### 14. Configure permissions

`.cpanel.yml` runs `chmod -R 775` on `storage/` and `bootstrap/cache/`
automatically on every deploy.

### 15. Configure mail

Set the `MAIL_*` values in `.env` (step 6) — either cPanel's own mail server
(`mail.yourdomain.com`) or a transactional provider (Postmark, SES, etc.).
Order-status-change emails (`app/Mail/OrderStatusChanged.php`) send
synchronously (`QUEUE_CONNECTION=sync`), so no queue worker/cron is required.

### 16. Enable SSL

cPanel → **SSL/TLS Status** → run AutoSSL for the domain (or install a
purchased certificate). Once live, uncomment the HTTPS-redirect block in
`legacy-static-reference/.htaccess` if you want it reinstated — the current
`public/.htaccess` does not force HTTPS by default; add a redirect rule there
if required.

### 17. Configure cron (optional)

Not required for core functionality — mail is synchronous and there's no
scheduled job yet. If a scheduled task is added later (`app/Console/Kernel`
schedule), add the standard Laravel cron entry:

```
* * * * * cd /home/seoweb/inzra-app && php artisan schedule:run >> /dev/null 2>&1
```

### 18. Clear/configure Laravel caches

Handled automatically by `.cpanel.yml` (`config:cache`, `route:cache`,
`view:cache`) on every deploy. If you ever need to clear them manually over
SSH: `php artisan optimize:clear`.

## After every deploy — verify

### 19. Test login

Visit `/login`, sign in with the seeded admin (or a real account) — confirm
the dashboard loads.

### 20. Test registration

Visit `/register`, create a throwaway account — confirm it lands on
`/dashboard` and appears in `users`.

### 21. Test orders

As a logged-in customer, open any `/products/{slug}` page and click **Order
on WhatsApp** — confirm it opens `wa.me` with the message pre-filled, and
that the order appears under `/orders`.

### 22. Test admin panel

Log in as the seeded admin, visit `/admin`, open the order just placed, and
change its status/payment — confirm the customer's `/orders/{id}` reflects
it and a status-change email is sent (check inbox or your mail provider's
log).

## SEO regression checklist

Before considering a deploy final, spot-check:

- The 6 core pages (`/`, `/marketplace`, `/categories`, `/pricing`, `/blog`,
  `/contact`) all 200, with correct `<title>`/canonical/meta.
- A sample of the 40 `/products/{slug}` and `/blog/{slug}` pages 200.
- A sample of the ~35 legacy numbered-slug URLs (see
  `config/legacy_redirects.php`) 301 to their new slug.
- `/products.html`, `/marketplace.html` etc. 301 to the extensionless URL.
- `/sitemap.xml` returns all current URLs; `/robots.txt` still disallows
  `/admin/`.
- Run a few live product/blog pages through Google's Rich Results Test to
  confirm Product/BreadcrumbList/FAQPage/BlogPosting JSON-LD still validates.
