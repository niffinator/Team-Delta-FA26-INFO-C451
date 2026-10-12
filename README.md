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

2.) Install PHP dependencies:

Run
    
    composer install

3.) Install frontend dependency:

    npm install

4.) Create your local environment file:

Copy the provided environment template (.env.example) and rename it to .env

Save it in the project folder beside artisan. Confirm the filename is .env

5.) Generate the Laravel application key

    php artisan key:generate

6.) Build and seed the demonstration database:

May need to create a new MySQL database through your chosen MySQL tool (i.e. MySQL Workbench, MySQL Server, etc.) if you have not already

Configure the local server's host, port, username, and password

Test the connection

Create a schema (like LMS or something apt)

Take that information and post it into your .env file and save the .env file

Run the command in your chosen IDE:
        
        php artisan config:clear

7.) Create the tables and seed the demonstration data from our DemoSeeder file:

Run the command in your chosen IDE:
        php artisan migrate:fresh --seed --seeder=DemoSeeder

8.) Start the application:

Run the command in your chosen IDE:
        php artisan serve

Must keep this terminal open while you're using our application

9.) Open a browser and go to:

    127.0.0.1:8000

Or the address that is printed in your terminal

### User Information Needed for Demonstration

Hard=coded users and passwords (thanks to the Database Seeder):

username: big_bertha
password: 12345

username: simpsonian
password: 246810

username: niraines
password: 54321