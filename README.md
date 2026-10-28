# Book WRMS A Web-based Records Management System for Libraries
Team-Delta-FA26-INFO-C451
-- Nicholas Raines, Jennifer Rose, Bryit Sumner, Trinity 

## System Setup

### Requirements
- PHP
- Composer
- Node.js/ npm
- MySQL

### Setup

 1.) Clone the Team-Delta-FA26-INFO-C451 repository in your terminal

    git clone https://github.com/niffinator/Team-Delta-FA26-INFO-C451.git
 
 2.) Open the program folder in an IDE

 3.) Configure the MySQL connection in the .env.example file
- Find the .env.example file
- Rename it to .env
- Uncomment the credentials 
- Set your root password
- Save

 4.) Open terminal

 5.) Install PHP dependency:
    
    composer install

 6.) Install frontend dependency:

    npm install

 7.) Run the command

    php artisan config:clear

 8.) Generate the Laravel application key

    php artisan key:generate 

 9.) Build and seed the demonstration database. Run the command:
        
    php artisan migrate:fresh --seeder=DemoSeeder

10.) Start Laravel for demonstration page

    php artisan serve

11.) In another terminal, start Vite for our project's styles, JavaScript, and BootStrap imports; keeps page up-to-date as changes are made

    npm run dev

12.) Open the application in a browser:

    http://127.0.0.1:8000


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
- Availability: checked out
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

Results:
- Checkout is rejected because the book has an active hold
- No transaction is created
- *Note*: this is a current limitation of the system because it does not verify which member has the active hold, as noted in Assignment 3. This will be resolved in the future; however, still displays functionality of the system to reject based on hold status. 
