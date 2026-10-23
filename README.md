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
6. Apply the CodeIgniter migrations from the project folder:

   ```powershell
   C:\xampp\php\php.exe spark migrate
   ```

   The migrations add an optional customer link to service requests and an employee role field. Existing requests stay unchanged. Migration `2026-10-24-000003_UpdateNamedEmployeeAccounts` updates the existing Katherine and Sol accounts to administrator access and sets Apollo's existing account as a technician. It requires the process environment variables `TEAMF5_KATHERINE_PASSWORD`, `TEAMF5_SOL_PASSWORD`, and `TEAMF5_APOLLO_PASSWORD`; supply them locally before running migrations, then clear them. They are not stored in source files.

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
- `GET /admin/dashboard` shows employee and service request records to signed-in employees whose database role is `admin`.
- `GET /admin/employees/new` and `POST /admin/employees` let an administrator create an employee account. New accounts always get the `employee` role.
- `GET /customer/dashboard` shows a signed-in customer's own linked service requests and account details.
- `GET /technician/dashboard` is available to signed-in employees in the Installation & Operations or Maintenance & Service departments.
- `GET /requests?service=Quote|Consultation|Installation|Maintenance` opens the shared request form. `POST /requests` submits it.
- `GET` and `POST /register` handle customer registration.
- `GET` and `POST /login` handle sign in. `POST /logout` signs out.

## Dashboard workflows

- Customers can review and cancel their own pending service requests, update their contact details and password, save notification preferences, view in-app notices and payment receipt records, and open or reply to support tickets.
- Administrators can manage employee details and deactivate employee accounts without deleting them, view the request calendar, approve or decline requests, set dates and times, assign active field employees, manage service categories/prices/availability notes, send in-app notices, reply to support tickets, review technician reports, and record payments received offline.
- Technicians see only jobs assigned to their active employee account. The job list omits customer email and phone. Check-in stores the browser-provided location only after the technician presses the button and grants location permission. Technicians can update job progress and submit problem reports tied to their assignments.
- Payments are recorded after an offline payment is verified. This site does not charge cards or send payment receipts by email. Notifications stay inside the application; email/SMS delivery is not configured. The schedule availability field is an informational note; the administrator selects actual booking dates and times.
- Dashboard tables are added by migration `2026-10-24-000001_CreateDashboardWorkflows`. It adds workflow tables, an employee active flag, and widens the existing service type column to allow new categories; existing requests and accounts are retained. Run `C:\xampp\php\php.exe spark migrate` after pulling these changes.

Forms that change data use CSRF protection. Registration stores a password hash. Sign in checks the existing customer or employee table, and inactive employees cannot sign in. New service requests are saved in `team3_db.service_requests`. The admin dashboard uses authorized database queries to manage employees and service workflows without displaying password hashes.

Public registration is for customers only. An administrator can add, edit, or deactivate non-admin employee accounts through the protected dashboard; employee registration cannot grant administrator access. Employees sign in at `/login` by choosing **Employee**. Administrators go to `/admin/dashboard`; active field technicians with the `technician` role or a field-service employee role go to `/technician/dashboard`. Technician jobs, check-ins, reports, customer support tickets, payment records, and in-app notifications use the additive dashboard-workflow migration described above.

The homepage hero uses `images/solarcell.jpg`; a copy in `public/images/solarcell.jpg` is served by the site. The older HTML/PHP files are still in the project, but the CodeIgniter routes are used when running the website as described above.
