<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Member;
use App\Models\Hold;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $clerkId = DB::table('users')->insertGetId([
            'first_name' => 'Big',
            'last_name' => 'Bertha',
            'username' => 'big_bertha',
            'password' => Hash::make('12345'),
            'role' => 'Clerk',
        ]);

        $librarianID = DB::table('users')->insertGetId([
            'first_name' => 'Nick',
            'last_name' => 'Raines',
            'username' => 'niraines',
            'password' => Hash::make('54321'),
            'role' => 'Librarian',
        ]);

        DB::table('users')->insert([
            'first_name' => 'Maggie',
            'last_name' => 'Simpson',
            'username' => 'simpsonian',
            'password' => Hash::make('246810'),
            'role' => 'Librarian',
        ]);
        
        $johnId = DB::table('members')->insertGetId([
            'first_name' => 'John',
            'last_name' => 'Books',
            'email' => 'john.books@fake.com',
            'phone' => '765-555-5555',
        ]);

        $melId = DB::table('members')->insertGetId([
            'first_name' => 'Mel',
            'last_name' => 'Brooks',
            'email' => 'mel@bigshothollywood.com',
            'phone' => '317-555-5555',
        ]);

        $pinkId = DB::table('members')->insertGetId([
            'first_name' => 'Pink',
            'last_name' => 'Floyd',
            'email' => 'band@UK.gov',
            'phone' => '54-3223-455',
        ]);

        $hobbitId = DB::table('books')->insertGetId([
            'ISBN' => '9780547928227',
            'title' => 'The Hobbit',
            'author' => 'J.R.R. Tolkien',
            'genre' => 'fantasy',
        ]);

        $wheelOfTimeId = DB::table('books')->insertGetId([
            'ISBN' => '9781250768681',
            'title' => 'Wheel of Time: The Eye of the World',
            'author' => 'Robert Jordan',
            'genre' => 'fantasy',
        ]);

        $neuromancerId = DB::table('books')->insertGetId([
            'ISBN' => '9780441007462',
            'title' => 'Neuromancer',
            'author' => 'William Gibson',
            'genre' => 'science-fiction',
        ]);        

        $hobbitCopyId = DB::table('book_copies')->insertGetId([
            'book_id' => $hobbitId,
            'call_number' => 'F TOLKIEN',
            'status' => 'available',
        ]);

        $wheelOfTimeCopyId = DB::table('book_copies')->insertGetId([
            'book_id' => $wheelOfTimeId,
            'call_number' => 'F JORDAN',
            'status' => 'checked out',
        ]);

        $neuromancerCopyId = DB::table('book_copies')->insertGetId([
            'book_id' => $neuromancerId,
            'call_number' => 'SF GIBSON',
            'status' => 'available',
        ]);        

        DB::table('holds')->insert([
            [
                'member_id' => $johnId,
                'book_id' => $hobbitId,
                'hold_date' => '2026-09-09',
                'status' => 'active',
            ],

            [
                'member_id' => $johnId,
                'book_id' => $wheelOfTimeId,
                'hold_date' => '2026-09-26',
                'status' => 'cancelled',
            ],
        ]);
 
    }
}
