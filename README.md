# SmartSakay

A simple PHP + static frontend project for managing trips, drivers, vehicles, and passengers.

## Project structure

- admin/ — admin UI pages (dashboard, drivers, routes, trips, etc.)
- api/ — PHP endpoints and `db.php` database connection
- assets/ — images, icons, logo
- bootstrap/ — Bootstrap CSS/JS used by the UI
- auth-pages/ — login/signup pages for admin and staff
- styles/, scripts/ — custom CSS and JS

## Requirements

- Windows, macOS, or Linux
- XAMPP (Apache + PHP + MySQL) recommended, or any PHP-enabled web server
- PHP 7.4+ (or the version available in your XAMPP)
- A MySQL/MariaDB server for the app database

## Quick start (XAMPP)

1. Copy the `SmartSakay` folder into XAMPP's `htdocs` directory (already placed here for this workspace).
2. Start the XAMPP Control Panel and ensure **Apache** and **MySQL** are running.
3. Open a browser and visit:
   - Public landing page: `http://localhost/SmartSakay/landing-page.html`
   - Admin area: `http://localhost/SmartSakay/admin/dashboard.html`

## Alternative: PHP built-in server (for quick static/PHP testing)

From the project root (`SmartSakay`) run:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/landing-page.html` or `http://localhost:8000/admin/dashboard.html` in your browser.

Note: the built-in server is fine for development/testing but XAMPP is recommended for MySQL support.

## Database setup

- Configure your DB credentials in `api/db.php`.
- This project does not include a database dump. Create the database and tables manually or request the SQL schema if needed.
- Typical `api/db.php` expects host, username, password, and database name — update these values before using endpoints.

## API endpoints

API endpoints are in the `api/` folder. Example files:

- `api/drivers.php` — driver list
- `api/save-driver.php` — save a driver
- `api/trips.php` — trips list

Call these from the frontend via relative paths like `/SmartSakay/api/drivers.php`.

## Development notes

- Static assets and styles are under `assets/`, `styles/`, and `bootstrap/`.
- If pages look broken, ensure `bootstrap.min.css` and `bootstrap.bundle.min.js` are loaded correctly from the `bootstrap/` folder.
- If you move the project, update any absolute paths used in the code.

## Troubleshooting

- If PHP pages show source code in the browser, ensure Apache/PHP are running and the files are served by the server (not opened via `file://`).
- Database connection errors: verify credentials in `api/db.php`, and that MySQL is running.

## Next steps (optional)

- Add a SQL schema file (e.g., `database/schema.sql`) to automate DB creation.
- Add an `.env` or config file for easier credential management.
- Add basic seed/sample data for testing.

--- END
