# Deployment guide — Hosting Compatibility Suite

This package is a Laravel 13 application whose homepage reports what your hosting
account actually supports at runtime. It ships **ready to upload**: `vendor/` is
already installed and the Vite assets are already built, so the target server
needs neither Composer nor Node.

---

## 1. What is in the archive

```
app/                 Application code (the diagnostics engine)
bootstrap/           Framework bootstrap + cached providers
config/              Configuration
database/            Migrations + an empty SQLite database
public/              >>> THIS IS THE DOCUMENT ROOT <<<
  build/             Pre-compiled Vite assets (CSS/JS)
  index.php          Front controller
  .htaccess          Apache rewrite rules
resources/           Blade views and asset sources
routes/              Route definitions
storage/             Logs, cache, sessions, compiled views (must be writable)
vendor/              PHP dependencies (already installed, production only)
.env.example         Copy this to .env on the server
artisan              CLI entry point
```

---

## 2. Upload and extract

Upload the `.zip` to your account and extract it. Where you extract it depends on
what your provider lets you configure.

### Layout A — you can set the document root (preferred)

Most cPanel / Plesk / DirectAdmin plans allow this, and it is the only layout
Laravel officially supports.

1. Extract the archive **outside** the public web folder, e.g. `~/laravel-test/`.
2. In the control panel, point the domain (or subdomain) document root to
   `~/laravel-test/public`.

Only `public/` is then reachable from the internet. Everything else — including
`.env` — stays private.

### Layout B — the document root is locked to `public_html`

Some cheap plans will not let you move it. Two options, in order of preference:

**B1 — symlink (clean, but needs symlink support):**

```bash
mv ~/public_html ~/public_html.bak
ln -s ~/laravel-test/public ~/public_html
```

The dashboard's **Filesystem → Symlink support** check tells you whether this
will work before you try it.

**B2 — split the app (works everywhere, uglier):**

1. Extract everything into `~/laravel-test/`.
2. Move the *contents* of `~/laravel-test/public/` into `~/public_html/`.
3. Edit `~/public_html/index.php` and fix the two require paths:

```php
require __DIR__.'/../laravel-test/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel-test/bootstrap/app.php';
```

> If you end up on B2, note it: a hosting that forces this layout is a hosting
> that does not really support Laravel — it just happens to run PHP.

---

## 2b. If the server has Composer and Node (recommended path)

The archive is self-contained and needs neither. But if your plan provides them,
use them — it is strictly better, for one specific reason.

### Re-resolve the PHP dependencies on the server

`vendor/` was resolved on a machine running PHP 8.5. Composer bakes the resolved
platform requirement into `vendor/composer/platform_check.php`, and that check
**fatals at boot** if the server runs an older interpreter. To keep the archive
portable, `composer.json` pins `config.platform.php` to `8.3.0` — Laravel 13's
own minimum — so the shipped `vendor/` boots on any PHP from 8.3 upward.

That pin is a floor, not an optimisation. If the server runs 8.4 or newer, let
Composer resolve against the real interpreter and get the better package set:

```bash
composer install --no-dev --optimize-autoloader
```

If you would rather keep the shipped `vendor/` untouched, that is fine too — it
is verified to boot on 8.3+.

### Rebuild the front-end on the server

`package-lock.json` ships in the archive, so the build is reproducible:

```bash
npm ci
npm run build
```

This is worth doing even though `public/build/` is already populated: it proves
the host's Node toolchain can actually compile assets, which is a real thing to
know about a plan that advertises Node support.

### Run the test suite — the hardest probe available

`tests/` and `phpunit.xml` ship in the archive. With dev dependencies installed:

```bash
composer install            # note: WITHOUT --no-dev
php artisan test
```

32 tests, 291 assertions. If they pass on the server, the host runs Laravel
properly — that is a far stronger statement than any dashboard badge. Afterwards,
drop back to `composer install --no-dev --optimize-autoloader` for production.

### A note on extracting

Extract the archive **on the server**. Some `vendor/` paths are long enough to
trip Windows' 260-character path limit if you unzip locally inside an already
deep folder and then upload the tree. Linux hosts have no such limit, and
uploading one `.zip` is faster than uploading 6,582 files anyway.

---

## 3. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

If your plan has **no SSH access**, generate the key on your machine and paste the
`APP_KEY=base64:...` line into `.env` through the file manager. The app will not
boot without it, and the dashboard's **Application → APP_KEY** check will say so.

Then edit `.env`:

```dotenv
APP_NAME="Hosting Compatibility Suite"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.tld

# SQLite needs no credentials and works on almost any host.
# Use it first to prove the app boots, then switch to MySQL to test the real DB.
DB_CONNECTION=sqlite
# DB_DATABASE must be an ABSOLUTE path on shared hosting:
DB_DATABASE=/home/YOUR_USER/laravel-test/database/database.sqlite

# --- Switch to this block to test the MySQL your plan sold you ---
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_db
# DB_USERNAME=your_user
# DB_PASSWORD=your_password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=your.smtp.host
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="test@your-domain.tld"
```

`APP_DEBUG=false` is deliberate. The dashboard reports failures on its own; you do
not need stack traces exposed to the internet to read them.

---

## 4. Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

If `chmod` is unavailable, set `storage` and `bootstrap/cache` (recursively) to
**755** or **775** in the file manager. The dashboard's **Filesystem → Writable
paths** check is the authority here — if it is green, you are done.

---

## 5. Run the migrations

With SSH:

```bash
php artisan migrate --force
```

Without SSH: the SQLite file in `database/database.sqlite` already has the schema
applied, so the app boots as-is. For MySQL you need the migrations to run — if
your plan has no SSH and no cron, that alone is a serious finding about the plan.

---

## 6. Optional: the two things shared hosting usually cannot do

Both are optional for the dashboard to load, and both are worth testing because
they are the usual dealbreakers.

**Scheduler** — add a cron job (every minute):

```
* * * * * cd /home/YOUR_USER/laravel-test && php artisan schedule:run >> /dev/null 2>&1
```

**Queue worker** — a long-running process:

```
php artisan queue:work --tries=3 --timeout=90
```

Most shared plans kill long-running processes. If yours does, the `database`
queue driver still works for dispatching; jobs just will not be consumed until a
worker runs. The dashboard's **Queue** group reports exactly which half works.

---

## 7. Read the results

Open your domain. You get:

| Route          | What it gives you                                      |
| -------------- | ------------------------------------------------------ |
| `/`            | The full dashboard, grouped by subsystem               |
| `/report.json` | The same report as JSON — diff it between two hosts     |
| `/phpinfo`     | Raw `phpinfo()` output                                 |

With SSH, the same suite runs in the terminal:

```bash
php artisan hosting:check
php artisan hosting:check --json
```

### How to read a result

- **pass** — works.
- **warn** — works, but with a caveat worth knowing (old PHP, debug mode on, an
  optional extension missing).
- **fail** — does not work. If a `fail` lands on PHP version, required
  extensions, writable paths or the database connection, the plan cannot run
  Laravel properly. That is the answer you were looking for.
- **skipped** — not applicable to this configuration.

---

## 8. When you are finished testing

Delete the whole thing. `/phpinfo` and `/report.json` describe your server in
detail and there is no authentication on any of it — this is a diagnostic tool,
not something to leave running on a public domain.
