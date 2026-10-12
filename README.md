# Team-Delta-FA26-INFO-C451
-- Nicholas Raines, Jennifer Rose, Bryit Sumner, Trinity 

## Project Readme File

### Requirements
-PHP
-Composer
-Node.js/ npm
-MySQL

### Setup

1.) Clone our repository

2.) Install PHP dependency:
    
    composer install

3.) Install frontend dependency:

    npm install

4.) Create and configure the local environment file:

    We have provided a copy of the example.env; copy and past this in the same folder

    Rename the new file to just .env 

    Open your chosen method of accessing MySQL and create an empty database 

    Update your .env file (can be done in your chosen IDE) to reflect your MySQL setup

5.) Generate the Laravel application key

    php artisan key:generate

6.) Configure the MySQL connection in '.env'

7.) Build and seed the demonstration database:

    run the command:
        
        php artisan migrate:fresh --seeder=DemoSeeder

8.) Start Laravel for demonstration page

    php artisan serve

8.5) Probably need to start Vite in another terminal

    npm run dev

9.) Open the application in a browser:

    http://127.0.0.1:8000

10.) Enjoy the fruits of my labor and perform the tests/demonstrations as described in Assignment 5


### User Information Needed for Demonstration

Hard=coded users and passwords (thanks to the Database Seeder):

username: big_bertha
password: 12345

username: simpsonian
password: 246810

username: niraines
password: 54321