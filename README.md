# Sun Son Solar (TEAM-F5)

This is our Sun Son Solar website. It uses CodeIgniter 4 for the pages and backend features. The active site starts at `public/index.php`. If the project folder is opened through its root, `index.html` sends the visitor to `public/`.

## What is needed

- XAMPP with PHP 8.2 or newer and the `intl`, `mbstring`, and `mysqli` extensions
- Composer 2
- MySQL or MariaDB and the existing `team3_db` database

## Set up with XAMPP

1. Open the XAMPP Control Panel and start MySQL.
2. This computer uses `C:\xampp\php\php.ini`. I checked that PHP loads `intl`, `mbstring`, and `mysqli`. To check your PHP setup, run:

   ```powershell
   C:\xampp\php\php.exe -m
   ```

   If `intl` is missing on another XAMPP installation, enable `extension=intl` in that installation's `php.ini` if its `ext\php_intl.dll` file exists. Restart Apache if you use Apache, then check the modules again.
3. Keep the existing `.env` file. If it is missing, copy `.env.example` to `.env`. Set the database hostname, database name, username, password, and port for your MySQL setup. The current local setup uses `localhost:3306` and `team3_db`. Do not share `.env` or upload it to Git. Use HTTPS before setting `cookie.secure = true`.
4. Use `database.sql` only if you still need to add the project schema. It creates the `service_requests` table and its matching customer/employee seed rows are left unchanged if they already exist. Do not drop or recreate `team3_db`.
5. Install the PHP packages from the project folder:

   ```powershell
   C:\xampp\php\php.exe composer.phar install
   ```

   This project includes Composer 2.10.3 as `composer.phar`. That file is local and ignored by Git. If it is missing, install Composer for Windows from [getcomposer.org](https://getcomposer.org/download/) and select `C:\xampp\php\php.exe` when asked for PHP. Then run `composer install`. If package extraction fails, enable PHP ZIP or try `composer install --prefer-source` with Git installed.

## Run the website

From the project folder, run:

```powershell
C:\xampp\php\php.exe composer.phar serve
```

Then open **http://localhost:8080/**. You can also start it with:

```powershell
C:\xampp\php\php.exe spark serve --host 127.0.0.1 --port 8080
```

For Apache, set the document root to this project's `public` folder and enable `mod_rewrite` so `public/.htaccess` can handle the routes.

## Main routes

- `GET /` opens the homepage.
- `GET /requests?service=Quote|Consultation|Installation|Maintenance` opens the shared request form. `POST /requests` submits it.
- `GET` and `POST /register` handle customer registration.
- `GET` and `POST /login` handle sign in. `POST /logout` signs out.

Forms that change data use CSRF protection. Registration stores a password hash. Sign in checks the existing customer or employee table. New service requests are saved in `team3_db.service_requests`; the team checks them in phpMyAdmin. There is no employee request dashboard in the site.

Registration is for customers only. There is no employee signup, administrator account, invitation, or approval process. Existing employees can sign in at `/login` by choosing **Employee** and using the username or email and password issued by the organization. For testing, ask an authorized account holder for valid employee credentials. Do not use a made-up or default password.

The homepage hero uses `images/solarcell.jpg`; a copy in `public/images/solarcell.jpg` is served by the site. The older HTML/PHP files are still in the project, but the CodeIgniter routes are used when running the website as described above.
