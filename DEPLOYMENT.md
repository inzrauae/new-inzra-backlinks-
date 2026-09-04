# Deploying INZRA to cPanel — from an empty account to live

This app deploys via cPanel's **Git Version Control** feature, which runs
`.cpanel.yml` automatically whenever you deploy from a repository. The
Laravel application lives **outside** the public webroot
(`/home/seoweb/inzra-app/`); `public_html/` only ever receives a copy of
Laravel's `public/` folder — never the app source, `.env`, or `vendor/`.

**Before you start — one assumption to verify:** every path in
`.cpanel.yml` and `deploy/public-index.php` uses the cPanel username
`seoweb` (carried over from this site's original hosting setup). Log in to
cPanel and check the username shown top-right (or in your host's welcome
email). If it's **not** `seoweb`, replace every occurrence of `seoweb` in
those two files with your real username before deploying, and push that
change.

The code is already on GitHub at
**https://github.com/inzrauae/new-inzra-backlinks-** (branch `main`), which
is what cPanel will pull from.

---

## Part 1 — Server basics (10–15 minutes)

### 1. Create the MySQL database

cPanel → **MySQL® Databases** → under "Create New Database", enter `inzra`
(cPanel will prefix it, e.g. `seoweb_inzra`) → Create Database.

### 2. Create the MySQL user

Same page, "MySQL Users" → "Add New User" → username `inzra`, and click
**Password Generator** for a strong password (save it somewhere — you'll
need it in Part 3). Create User.

### 3. Add the user to the database

Same page, "Add User To Database" → select the user and database you just
made → Add → check **ALL PRIVILEGES** → Make Changes.

You now have two full names to remember, e.g. `seoweb_inzra` (database) and
`seoweb_inzra` (user) — cPanel shows the exact prefixed names on this page.

### 4. Set the PHP version

cPanel → **MultiPHP Manager** → select your domain → set PHP to **8.2 or
newer** (check `composer.json`'s `"php"` line if unsure of the floor) →
Apply.

### 5. Enable required PHP extensions

Still in MultiPHP Manager (or **Select PHP Extensions**), confirm these are
checked for your domain's PHP version: `pdo_mysql`, `mbstring`, `openssl`,
`tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`. These are
enabled by default on almost every cPanel PHP build — just verify.

### 6. Open a terminal

cPanel → **Terminal** (built into most modern cPanel installs — no separate
SSH client needed). Keep this open; you'll use it in Parts 2–4.

### 7. Find your real PHP and Composer paths

The `.cpanel.yml` in this repo calls `/usr/local/bin/php` and
`/usr/local/bin/composer`. Confirm these actually resolve to your chosen
PHP 8.2+ version:

```bash
/usr/local/bin/php -v
which composer
```

If `php -v` shows an old version (cPanel's "default" CLI PHP can differ
from what MultiPHP Manager sets for the *website*), find the versioned
binary instead — usually something like:

```bash
ls /opt/cpanel/ea-php*/root/usr/bin/php
```

If you had to use a different path, edit `.cpanel.yml` and swap every
`/usr/local/bin/php` for that exact path (e.g.
`/opt/cpanel/ea-php82/root/usr/bin/php`), then commit and push.

If `which composer` returns nothing, install it once:

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /home/seoweb/bin/composer   # create ~/bin if needed, and ensure it's on PATH
```

---

## Part 2 — Get the code onto the server

### 8. Set up Git Version Control

cPanel → **Git™ Version Control** → **Create**:
- Clone URL: `https://github.com/inzrauae/new-inzra-backlinks-.git`
- Repository Path: `/home/seoweb/repo` (anything **other than**
  `public_html` — this is a staging clone, not the live app)
- Repository Name: `inzra`

Click **Create**. cPanel clones the repo.

### 9. Run the first deploy

On the Git Version Control page, open the `inzra` repo → **Manage** →
**Pull or Deploy** tab → **Deploy HEAD Commit**.

This runs every task in `.cpanel.yml` end to end: rsyncs the app into
`/home/seoweb/inzra-app/`, runs `composer install`, creates `.env` from
`.env.example` (first time only), runs migrations, caches config/routes/
views, and publishes `public/` into `public_html/`.

**The very first deploy will partially fail** — `artisan migrate` needs a
real database connection, which isn't configured yet. That's expected;
continue to Part 3, then re-run **Deploy HEAD Commit** afterward.

---

## Part 3 — Configure `.env`

In the Terminal:

```bash
nano /home/seoweb/inzra-app/.env
```

Fill in (leave everything else from `.env.example` as-is unless you know
you need to change it):

```env
APP_NAME=INZRA
APP_ENV=production
APP_KEY=                      # leave blank — generated in step 10
APP_DEBUG=false
APP_URL=https://inzra.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoweb_inzra      # exact name from step 1
DB_USERNAME=seoweb_inzra      # exact name from step 2
DB_PASSWORD=                  # the generated password from step 2

ADMIN_EMAIL=you@inzra.com
ADMIN_PASSWORD=                # a strong password — seeds the one admin account

MAIL_MAILER=smtp
MAIL_HOST=                     # cPanel mail host or a transactional provider
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=support@inzra.com

# Optional — "Continue with Google" only appears once these are set.
# See Part 5.
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://inzra.com/auth/google/callback
```

Save (`Ctrl+O`, `Enter`, `Ctrl+X` in nano).

**Note:** PayPal credentials are **not** set in `.env` — they're configured
from `/admin/settings/payment` after the site is live (Part 5).

Every future deploy leaves this file alone — it's excluded from the rsync
in `.cpanel.yml`, so editing it here is permanent.

### 10. Generate the app key

```bash
cd /home/seoweb/inzra-app
php artisan key:generate
```

### 11. Re-run the deploy

Back in cPanel's Git Version Control → **Pull or Deploy** → **Deploy HEAD
Commit** again. This time migrations should succeed. Watch the output for
errors — if `composer install` or `artisan migrate` fail, see
Troubleshooting at the bottom.

### 12. Seed the database (once only)

```bash
cd /home/seoweb/inzra-app
php artisan db:seed
```

This creates the 3 real product categories, 8 marketing categories, 40
products, 40 blog posts, and the one admin account from
`ADMIN_EMAIL`/`ADMIN_PASSWORD`. **Don't run this again later** — re-seeding
after real customers/orders exist isn't destructive to orders, but it will
recreate products from the fixture files, so only run it once, here.

---

## Part 4 — Point the domain at it

### 13. Set the document root

cPanel → **Domains** → your domain → confirm the document root is
`/home/seoweb/public_html`. This should already be correct on most
accounts by default; don't point it at `inzra-app` or the git repo clone.

### 14. Enable SSL

cPanel → **SSL/TLS Status** → select the domain → **Run AutoSSL**. Takes a
few minutes. Once issued, confirm `https://` loads the site.

### 15. Confirm storage & permissions

These run automatically on every deploy via `.cpanel.yml`
(`artisan storage:link`, `chmod -R 775 storage bootstrap/cache`) — just
sanity-check after the first deploy:

```bash
ls -la /home/seoweb/public_html/storage   # should be a symlink
```

### 16. Cron (optional, not required)

Nothing in this app currently needs a scheduled job — mail sends
synchronously (`QUEUE_CONNECTION=sync`). Skip this unless you add a
scheduled task later, in which case add the standard Laravel cron entry via
cPanel → **Cron Jobs**:

```
* * * * * cd /home/seoweb/inzra-app && php artisan schedule:run >> /dev/null 2>&1
```

---

## Part 5 — Turn on PayPal and Google sign-in (optional, post-launch)

Both are fully built but intentionally **off** until you provide real
credentials — nothing shows up on the site until you do this.

### PayPal

1. Create an app at
   [developer.paypal.com/dashboard/applications](https://developer.paypal.com/dashboard/applications)
   — a **Live** app for real payments (or Sandbox first, to test).
2. Log in to the site as your admin account → `/admin/settings/payment`.
3. Check **Enable PayPal checkout**, choose **Live** (or **Sandbox** to
   test first), paste in the Client ID and Secret, save.
4. Back in the PayPal app dashboard, add a webhook pointing at
   `https://inzra.com/webhooks/paypal`, subscribed to
   `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.DENIED`, and
   `PAYMENT.CAPTURE.REFUNDED`. Copy the **Webhook ID** it gives you back
   into the same settings page.

No redeploy needed — this takes effect immediately (it's stored in the
database, read fresh on every page load).

### Google sign-in

1. Create OAuth credentials at
   [console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials).
2. Application type: **Web application**. Authorized redirect URI must be
   **exactly**: `https://inzra.com/auth/google/callback`
3. SSH/Terminal in and edit `.env`:
   ```bash
   nano /home/seoweb/inzra-app/.env
   ```
   Fill in `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`.
4. Clear the config cache so the change takes effect:
   ```bash
   cd /home/seoweb/inzra-app
   php artisan config:cache
   ```

The "Continue with Google" button now appears on `/login` and `/register`.

---

## Part 6 — Test everything before calling it done

- **Core pages**: `/`, `/marketplace`, `/categories`, `/pricing`, `/blog`,
  `/contact` all load with correct titles.
- **Register**: create a throwaway account at `/register` — lands on
  `/dashboard`.
- **Login/logout** work; **forgot password** email arrives (once `MAIL_*`
  is real).
- **Google sign-in** (if configured): "Continue with Google" logs you in
  and creates/links an account.
- **Order via PayPal** (if configured): open a `/products/{slug}` page
  while logged in, click **Pay with PayPal**, complete a real or sandbox
  payment, confirm you land back on `/orders/{id}` marked **Paid**.
- **Admin panel**: log in as the seeded admin, visit `/admin`, open the
  order just placed, change its status — confirm the customer's order page
  updates and a notification email sends.
- **Legacy redirects**: a few of the ~35 old numbered-slug URLs (see
  `config/legacy_redirects.php`) should 301 to their current slug; any
  `/page.html` URL should 301 to `/page`.
- **`/sitemap.xml`** returns all current URLs; **`/robots.txt`** still
  disallows `/admin/`.
- Run a couple of live product/blog pages through
  [Google's Rich Results Test](https://search.google.com/test/rich-results)
  to confirm the JSON-LD still validates.

---

## Ongoing deploys

Every future code change: push to `main` on GitHub, then cPanel → **Git
Version Control** → **inzra** → **Pull or Deploy** → **Update from Remote**,
then **Deploy HEAD Commit**. `.env` is never touched; migrations run
automatically; caches rebuild automatically.

---

## Troubleshooting

- **500 error, blank page**: check
  `/home/seoweb/inzra-app/storage/logs/laravel.log` for the real error.
  With `APP_DEBUG=false` the browser never shows it.
- **"could not find driver" / DB connection errors**: double-check
  `DB_DATABASE`/`DB_USERNAME` include the cPanel account prefix (e.g.
  `seoweb_inzra`, not just `inzra`).
- **`composer install` fails with a memory error**: some shared hosts cap
  CLI memory low. Try
  `php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader`.
- **Deploy runs but the site still shows old content**: `view:cache` and
  `config:cache` ran against a *previous* deploy's files — SSH in and run
  `php artisan optimize:clear` in `/home/seoweb/inzra-app`, then redeploy.
- **`public_html/index.php` errors about a missing `vendor/autoload.php`**:
  the `seoweb` username assumption at the top of this guide wasn't updated
  to match your real account — fix `deploy/public-index.php` and
  `.cpanel.yml`, push, redeploy.
