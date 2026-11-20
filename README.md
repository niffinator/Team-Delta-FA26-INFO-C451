# Book WRMS: A Web-Based Records Management System for Libraries

Team Delta â€” FA26 INFO-C451
Nicholas Raines, Jennifer Rose, Bryit Sumner, Trinity Young

## System Setup

### Requirements

- **PHP 8.4.1 or later** for this submitted version. The current `composer.lock` contains dependencies that require PHP >= 8.4.1.
- **Composer** for PHP dependencies.
- **Node.js 20.19+ or 22.12+**, with npm, for the current Vite version.
- **MySQL with InnoDB support** (MySQL 8.4 is the team's documented version). The application uses foreign keys and database transactions.
- WAMP or another working local PHP/MySQL environment.

On Windows, confirm the PHP **command-line** version with `php -v`. The version selected in the WAMP tray does not necessarily change PowerShell's PHP version. If PHP 8.4.15 is installed in WAMP, you can select it for the current PowerShell window with:

```powershell
$env:Path = "C:\wamp64\bin\php\php8.4.15;$env:Path"
php -v
```

### Setup

1. Clone the repository and open the project folder in a terminal:

   ```powershell
   git clone https://github.com/niffinator/Team-Delta-FA26-INFO-C451.git
   cd Team-Delta-FA26-INFO-C451
   ```

2. Start MySQL and create a **new, empty database** in phpMyAdmin (for example, `delta_lms_demo`). Do not reuse a database containing important data.

3. Copy `.env.example` to `.env` and configure the database connection for the database you created:

   ```powershell
   Copy-Item .env.example .env
   ```

   Example `.env` settings (use your real MySQL credentials):

   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=delta_lms_demo
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Keep `.env` out of Git; it may contain database passwords.

4. Install dependencies and generate the application key:

   ```powershell
   composer install
   npm install
   php artisan key:generate
   php artisan config:clear
   ```

5. Verify that `.env` points to your new database. The project's MySQL configuration specifies the **InnoDB** storage engine so table creation does not depend on a local MyISAM default.

6. Create the tables and demonstration data:

   ```powershell
   php artisan migrate:fresh --seeder=DemoSeeder
   ```

   **Warning:** `migrate:fresh` deletes all tables in the configured database. Only use it on a disposable demo/test database.

7. Start Laravel:

   ```powershell
   php artisan serve
   ```

8. In a second terminal in the project folder, start Vite:

   ```powershell
   npm run dev
   ```

   Or run `npm run build` to generate static frontend assets without leaving the Vite development server running.

9. Open `http://127.0.0.1:8000` in a browser and sign in with a sample account below.
## Sign In as Sample User

### Circulation Clerk
- Username: big_bertha
- Password: 12345

### Librarian
- Username: niraines
- Password: 54321

## Sample Books

- Book ID: 1
- Copy ID: 1
- Title: The Hobbit
- Author: J.R.R. Tolkien
- ISBN: 9780547928227
- Call Number: F TOLKIEN
- Availability: available
-----
- Book ID: 1
- Copy ID: 2
- Title: The Hobbit
- Author: J.R.R. Tolkien
- ISBN: 9780547928227
- Call Number: F TOLKIEN
- Availability: available
-----
- Book ID: 1
- Copy ID: 3
- Title: The Hobbit
- Author: J.R.R. Tolkien
- ISBN: 9780547928227
- Call Number: F TOLKIEN
- Availability: available
-----
- Book ID: 2
- Copy ID: 4
- Title: Wheel of Time: The Eye of the World
- Author: Robert Jordan
- ISBN: 9781250768681
- Call Number: F JORDAN
- Availability: checked out
-----
- Book ID: 3
- Copy ID: 5
- Title: Neuromancer
- Author: William Gibson
- ISBN: 9780441007462
- Call Number: SF GIBSON
- Availability: available

## Sample Members

- Member ID: 1
- Member Name: John Books
- Email: john.books@fake.com
- Phone: 765-555-5555
-----
- Member ID: 2
- Member Name: Mel Brooks
- Email: mel@bigshothollywood.com
- Phone: 317-555-5555
-----
- Member ID: 3
- Member Name: Pink Floyd
- Email: band@UK.gov
- Phone: 54-3223-455
