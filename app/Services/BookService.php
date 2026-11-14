<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookService
{
    // Create a new book and its first physical copy
    public function createBook(array $bookData): Book
    {
        try {
            return DB::transaction(function () use ($bookData) {

                // Check whether a book with the same ISBN already exists
                $existingBook = Book::where('ISBN', $bookData['ISBN'])->first();

                if ($existingBook) {
                    throw ValidationException::withMessages([
                        'ISBN' => 'A book with this ISBN already exists. Add a physical copy to the existing book instead.',
                    ]);
                }

                // Create the bibliographic book record
                $book = Book::create([
                    'ISBN' => $bookData['ISBN'],
                    'title' => $bookData['title'],
                    'author' => $bookData['author'],
                    'genre' => $bookData['genre'],
                ]);

                // Create the first physical copy associated with the book
                BookCopy::create([
                    'book_id' => $book->book_id,
                    'call_number' => $bookData['call_number'],
                    'status' => 'available',
                ]);

                // Return the newly created book
                return $book;
            });

        } catch (QueryException $exception) {

            // Identify MySQL duplicate key violations
            $mysqlErrorCode = $exception->errorInfo[1] ?? null;

            if (
                DB::getDriverName() === 'mysql' &&
                (int) $mysqlErrorCode === 1062
            ) {
                // Display the existing ISBN validation message
                throw ValidationException::withMessages([
                    'ISBN' => 'A book with this ISBN already exists. Add a physical copy to the existing book instead.',
                ]);
            }

            // Allow unrelated database errors to propagate normally
            throw $exception;
        }
    }
}
