<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
            DB::transaction(function () {
            
            // Set the seed time for use later on
            $seedTime = now()->startOfSecond();

            $clerkId = DB::table('users')->insertGetId([
                'first_name' => 'Big',
                'last_name' => 'Bertha',
                'username' => 'big_bertha',
                'password' => Hash::make('12345'),
                'role' => 'Clerk',
            ]);

            DB::table('users')->insertGetId([
                'first_name' => 'Nick',
                'last_name' => 'Raines',
                'username' => 'niraines',
                'password' => Hash::make('54321'),
                'role' => 'Librarian',
                'created_at' => $seedTime,
                'updated_at' => $seedTime,
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

            DB::table('book_copies')->insert([
                'book_id' => $hobbitId,
                'call_number' => 'F TOLKIEN',
                'status' => 'available',
            ]);

            $wheelOfTimeCopyId = DB::table('book_copies')->insertGetId([
                'book_id' => $wheelOfTimeId,
                'call_number' => 'F JORDAN',
                'status' => 'checked out',
            ]);

            DB::table('book_copies')->insert([
                'book_id' => $neuromancerId,
                'call_number' => 'SF GIBSON',
                'status' => 'available',
            ]);
            
            $existingCheckoutDate = $seedTime->copy()->subDays(7);

            DB::table('transactions')->insert([
                'user_id' => $clerkId,
                'member_id' => $melId,
                'copy_id' => $wheelOfTimeCopyId,
                'checkout_date' => $existingCheckoutDate,
                'due_date' => $existingCheckoutDate->copy()->addDays(14),
                'return_date' => null,
                'late_fee' => 0,
            ]);

            DB::table('holds')->insert([
                [
                    'member_id' => $pinkId,
                    'book_id' => $hobbitId,
                    'hold_date' => $seedTime->copy()->subDay(),
                    'status' => 'active',
                ],

                [
                    'member_id' => $johnId,
                    'book_id' => $hobbitId,
                    'hold_date' => $seedTime->copy()->subDays(2),
                    'status' => 'active',
                ],

                [
                    'member_id' => $johnId,
                    'book_id' => $wheelOfTimeId,
                    'hold_date' => $seedTime->copy()->subDays(10),
                    'status' => 'cancelled',
                ],
            ]);
        });
    }
}
