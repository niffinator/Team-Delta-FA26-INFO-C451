Team-Delta-FA26-INFO-C451
-- Nicholas Raines, Jennifer Rose, Bryit Sumner, Trinity Young
# Book_WRMS
A web-based records management system designed for library employees' internal use to manage book inventory and member borrowing statuses.

### Requirements for Installation and Operation
- PHP
- Composer
- Node.js/ npm
- MySQL

### Setup

1.) Clone the Team-Delta-FA26-INFO-C451 public repository from GitHub:
    https://github.com/niffinator/Team-Delta-FA26-INFO-C451

2.) Install PHP dependency:
    
    composer install

3.) Install frontend dependency:

    npm install

4.) Create the local environment file:

    A copy of the '.env.example' .env file is provided in our repository

5.) Generate the Laravel application key

    php artisan key:generate

6.) Configure the MySQL connection in '.env'

    may need to create your own password, etc.

7.) Build and seed the demonstration database:

    run the command:
        
        php artisan migrate:fresh --seeder=DemoSeeder

    the above command provides the database with sample records used for this demo

8.) Start Laravel for demonstration page

    php artisan serve

8.5) Probably need to start Vite in another terminal

    npm run dev

9.) Open the application in a browser:

    http://127.0.0.1:8000

    go to the 'Check out' page and run the demonstration provided below


## UC-4 Checkout Demonstration 

### To get a successful checkout
Member ID: 2
Copy ID: 3

*Press Submit

Results:
- Transaction is created 
- Status of book copy 3 changed to 'checked out'
- A message indicating a successful check out is shown

### Failure: Copy Already Checked Out
Member ID: 2
Copy ID: 2

*Press Submit

Results:
- Checkout is rejected
- A message indicating the error is displayed
- No transaction is created this time

### Failure: Active Hold
Member ID: 2
Copy ID: 1

*Press Submit

Results:
- Checkout is rejected because the book has an active hold
- No transaction is created
- *Note*: this is a current limitation of the system because it does not verify which member has the active hold, as noted in Assignment 3. This will be resolved in the future; however, still displays functionality of the system to reject based on hold status. 
