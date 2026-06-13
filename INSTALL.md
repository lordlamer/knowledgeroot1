# Installing Knowledgeroot

The legacy `install.php` / `update.php` web installer has been replaced by
[doctrine/migrations]. The database schema lives in `migrations/` as one
portable definition that runs on SQLite, MySQL/MariaDB and PostgreSQL.

## Fresh install

1. Install dependencies:

   ```
   composer install
   ```

2. Create the configuration from the template and set the database
   connection plus an admin login hash:

   ```
   cp config/app.ini.dist config/app.ini
   ```

   In `config/app.ini`:
   - `[db]` — set `adapter` (`pdo_sqlite`, `pdo_mysql` or `pdo_pgsql`) and the
     `params.*` (for SQLite just `params.dbname = "data/knowledgeroot.sqlite"`).
   - `[admin] loginhash` — the break-glass hash for the admin backend,
     computed as `md5(username . password)`, e.g.
     `php -r "echo md5('admin'.'secret');"`.

3. Create the schema:

   ```
   php bin/migrations.php migrate
   ```

   This creates all tables and seeds the default `admin` and `users` groups.

4. Create the first administrator: open `/admin`, log in with the
   credentials behind your `loginhash`, go to **user** (recover) and create
   an administrator. You can then log in normally at `/login`.

## Upgrades

Schema changes ship as new migration classes in `migrations/`. To upgrade an
existing installation, pull the new code and run:

```
php bin/migrations.php migrate
```

## Useful commands

```
php bin/migrations.php migrations:status   # what is applied / pending
php bin/migrations.php list                # all available commands
```

[doctrine/migrations]: https://www.doctrine-project.org/projects/migrations.html
