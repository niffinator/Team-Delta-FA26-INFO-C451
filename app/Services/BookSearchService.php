<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;


class BookSearchService
{
    
    public function searchBooks(
        string $bookSearch,
        string $bookField = 'title',
        string $copyStatus = ''
    ): Builder {
        // Trim the search term to remove any leading or trailing whitespace
        $bookSearch = trim($bookSearch);

        // Start building the query to search for book copies, including their associated book information
        $copyQuery = BookCopy::with('book')->whereHas('book');

        // Filter physical copy by an exact Copy ID match
        if ($bookField === 'copy_id') {
            $copyQuery->where('copy_id', $bookSearch);
            // Filter physical copies by call numbers containing the search text
        } elseif ($bookField === 'call_number') {
            $copyQuery->where(
                'call_number',
                'like',
                '%' . $bookSearch . '%'
            );
            // Filter copies using fields on their related book records
        } else {
            $copyQuery->whereHas(
                'book',
                function ($query) use ($bookField, $bookSearch) {
                    if ($bookField === 'book_id') {
                        $query->where('book_id', $bookSearch);
                    } else {
                        $query->where(
                            $bookField,
                            'like',
                            '%' . $bookSearch . '%'
                        );
                    }
                }
            );
        }
    
        // Apply the optional availability filter if a copy status is provided
        if ($copyStatus !== '') {
            $databaseStatus = $copyStatus === 'checked_out'
                ? 'checked out'
                : $copyStatus;

            $copyQuery->where('status', $databaseStatus);
        }

        // Order the results by book_id and copy_id for consistent output
        $copyQuery
            ->orderBy('book_id')
            ->orderBy('copy_id');
        
        // Return the constructed query builder instance for further use or execution
        return $copyQuery;
    }

    // Search for Book Titles to allow for the book search on the Holds Page
    public function searchBookTitles(
        string $bookSearch,
        string $bookField = 'title'
    ): Builder {
        $columns = [
            'title' => 'title',
            'author' => 'author',
            'isbn' => 'ISBN',
            'genre' => 'genre',
            'book_id' => 'book_id',
        ];

        if (! isset($columns[$bookField])) {
            throw ValidationException::withMessages([
                'book_field' => 'Choose a valid book search field.',
            ]);
        }

        $bookSearch = trim($bookSearch);

        $bookQuery = Book::query();

        if ($bookField === 'book_id') {
            $bookQuery->where('book_id', $bookSearch);
        } else {
            $bookQuery->where(
                $columns[$bookField],
                'like',
                '%' . $bookSearch . '%'
            );
        }

        return $bookQuery
            ->orderBy('title')
            ->orderBy('book_id');
    }
}



