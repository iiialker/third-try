# TaskFlow on XAMPP

TaskFlow is implemented in the existing Laravel 12 application. The UI uses Blade templates and Tailwind's CDN build; PHP and MySQL run through XAMPP. It uses Laravel's session authentication, CSRF-protected forms, Eloquent, and the standard `users` table.

## 1. Start XAMPP and create the database

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin`.
3. Create a database named `taskflow` using `utf8mb4_unicode_ci` collation.

By default, XAMPP's local MySQL account is `root` with an empty password. For any non-local deployment, create a dedicated database user with a strong password and grant it access only to this database.

## 2. Configure Laravel

The repository's `.env.example` has XAMPP-compatible settings. If `.env` already exists, preserve its `APP_KEY`, then set these values:

```dotenv
APP_NAME=TaskFlow
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskflow
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

Keep `APP_DEBUG=true` only on your local machine. Never publish `.env` or use the XAMPP root account in production.

## 3. Point Apache at Laravel's public directory

For a simple local XAMPP setup, place or link the Laravel project under `C:\xampp\htdocs\taskflow`. The web root **must** be the `public` subdirectory. Open `http://localhost/taskflow/public`. Laravel builds links from the current browser request, so leave `APP_URL` set to the host only; this also keeps command-line tests at the root path.

For a cleaner URL, configure an Apache virtual host whose `DocumentRoot` is `C:/xampp/htdocs/taskflow/public`, and allow `.htaccess` overrides for that directory. Restart Apache after changing its configuration. Do not point Apache at the project root: that can expose application files and secrets.

Alternatively, use Laravel's local PHP development server from the project directory:

```powershell
php artisan serve
```

Then open `http://127.0.0.1:8000`. This is convenient for development but is not a production web-server configuration.

## 4. Install and migrate

From the `example-app` directory:

```powershell
composer install
php artisan key:generate
php artisan config:clear
php artisan migrate
```

If the project already has a valid `APP_KEY`, do not regenerate it. The migration adds the `tasks` table and foreign keys to the existing users table. The `assigned_to` link becomes null when a member is deleted; tasks created by a deleted user are removed.

## 5. Use TaskFlow

Open the app's base URL and choose **Create an account**. The first and later accounts share one team board. Sign in to create tasks, assign team members, change status, edit details, and delete tasks. The root route sends guests to login and authenticated users to the board.

Run the feature tests with:

```powershell
php artisan test --compact
```

The test suite uses in-memory SQLite as configured in `phpunit.xml`; it does not need XAMPP MySQL running.

## Production boundary

This starter supports one shared team workspace per installation. It has no organization/tenant isolation, invitation workflow, role system, or password reset UI. Tailwind's CDN is appropriate for local setup but should be replaced with a compiled asset pipeline for a production deployment. Use HTTPS, disable debug output, set a strong unique database password, and review session cookie settings when deploying publicly.