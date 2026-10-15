# Sun Son Solar (TEAM-F5)

The active application is CodeIgniter 4. The web document root is `public/`; `public/index.php` dispatches application requests. The root `index.html` forwards visitors to `public/` when the repository is served from a folder.

## Requirements

- XAMPP with PHP 8.2 or newer and the `intl`, `mbstring`, and `mysqli` extensions enabled
- Composer 2.0.14 or newer
- MySQL/MariaDB with the existing `team3_db` database

## XAMPP setup on this machine

1. Start MySQL in the XAMPP Control Panel. This project has been configured against `localhost:3306`, database `team3_db`, username `root`, and the blank local XAMPP password used by the existing `db.php`.
2. PHP uses `C:\xampp\php\php.ini`. The required `intl` extension is enabled there. If setting up another XAMPP install, uncomment `extension=intl` in its `php.ini` only when `C:\xampp\php\ext\php_intl.dll` (or the equivalent extension DLL) exists, then restart Apache and open a new terminal. Verify with `C:\xampp\php\php.exe -m`.
3. Copy `.env.example` to `.env` if `.env` does not already exist. Set `database.default.hostname`, `database.default.database`, `database.default.username`, `database.default.password`, and `database.default.port` to that local MySQL instance. Keep `.env` private; it is ignored by Git. Set `cookie.secure = true` only when serving over HTTPS.
4. Import `database.sql` into the existing `team3_db` only if its original schema has not yet been installed. The script adds `service_requests`; its seed upserts leave matching existing customer and employee rows unchanged. Do not drop or recreate the database.
5. Install Composer for Windows from [getcomposer.org](https://getcomposer.org/download/) if the ignored local PHAR is absent. Select `C:\xampp\php\php.exe` as the PHP executable, then reopen PowerShell and verify `composer --version`. Install dependencies from the lock file:

   ```powershell
   C:\xampp\php\php.exe composer.phar install
   ```

   Composer 2.10.3 is available as an ignored local `composer.phar` in this checkout. If you obtain Composer separately, use `composer install` instead. The ZIP extension is optional for the running application; if Composer cannot extract package archives, install/enable PHP ZIP or run `composer install --prefer-source` with Git available.

## Run

From the project root:

```powershell
C:\xampp\php\php.exe composer.phar serve
```

Open **http://localhost:8080/**. The direct equivalent is `C:\xampp\php\php.exe spark serve --host 127.0.0.1 --port 8080`.

For Apache, point the virtual host document root at this project's `public/` folder, enable `mod_rewrite`, and use `public/.htaccess`.

## Application routes

- `GET /` — Sun Son Solar homepage, preserving the original reference layout
- `GET /requests?service=Quote|Consultation|Installation|Maintenance` and `POST /requests` — single shared request form with server-side validation
- `GET|POST /register` — customer account registration
- `GET|POST /login` and `POST /logout` — sign-in and sign-out

All state-changing routes use the global CSRF filter. Registration passwords are hashed; login checks the existing customer/employee tables. Service requests are stored in `team3_db.service_requests`; the team reviews submissions in phpMyAdmin. The site does not provide an employee request dashboard.

Employee rows exist in the local database, but the application has no employee registration endpoint, administrator role, invitation, or approval workflow. Public employee signup and employee creation through the account model are disabled. Before adding employees, the project needs an authorized provisioning policy (for example, administrator approval or controlled invitations) and a corresponding protected workflow. Existing employees sign in at `/login` by choosing **Employee** and entering the email address or username and password issued to them by the organization. There is no public/default employee credential.

For local employee-login testing, use credentials for an existing employee supplied by an authorized account holder. The source code and documentation intentionally contain no employee password. The `/register` page creates customer accounts only; changing the posted account type to `employee` is rejected by the controller, and the account model also refuses employee account creation.

The hero uses the existing `images/solarcell.jpg` solar-panel photo. The public copy is served from `public/images/solarcell.jpg` so the CodeIgniter document root can load it.

The original `registration.html`, `register.php`, `db.php`, and duplicate homepage source files are retained. CodeIgniter routes are the active application entry points when using the documented `public/` document root.
