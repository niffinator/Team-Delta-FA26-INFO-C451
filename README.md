# Team-Delta-FA26-INFO-C451
-- Nicholas Raines, Jennifer Rose, Bryit Sumner, Trinity 

## A.4 Working System Skeleton Demo

### Requirements
-PHP
-Composer
-Node.js/ npm
-MySQL

### Setup

1.) Clone repository from GitHub

2.) Launch Teminal or Powershell

3.) Run command: git clone https://github.com/niffinator/Team-Delta-FA26-INFO-C451.git

4.) Install PHP dependency:
    
    composer install

5.) Install frontend dependency:

    npm install

6.) Create the local environment file:

    A copy of the '.env.example' .env file is provided in our repository

7.) Generate the Laravel application key

    php artisan key:generate

8.) Configure the MySQL connection in '.env'

    may need to create your own password, etc.

9.) Build and seed the demonstration database:

    run the command:
        
        php artisan migrate:fresh --seeder=DemoSeeder

    the above command provides the database with sample records used for this demo

10.) Start Laravel for demonstration page

    php artisan serve

10.5) Probably need to start Vite in another terminal

    npm run dev

11.) Open the application in a browser:

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
