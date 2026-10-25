<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use Illuminate\Http\Request;
use App\Services\BookSearchService;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    // Display the Books page and handle book searches
    public function index(Request $request)
    {
        // Validate the search parameters entered by the user
        $validator = Validator::make($request->query(), [
            'book_search' => ['nullable', 'string', 'max:150'],
            'book_field' => [
                'nullable',
                'in:title,author,isbn,genre,book_id,copy_id,call_number'
            ],
            'copy_status' => ['nullable', 'in:available,checked_out'],
            'copy_page' => ['nullable', 'integer', 'min:1', 'max:100000'],

            'book_search_action' => ['nullable', 'in:1']
        ]);

        // Perform additional validation for the search
        $validator->after(function ($validator) use ($request) {
            $field = $request->query('book_field');
            $search = $request->query('book_search');

            if (
                $request->query('book_search_action') === '1' &&
                (!is_string($search) || trim($search) === '')
            ) {
                $validator->errors()->add(
                    'book_search',
                    'Please enter book information before searching.'
                );
            }

            // Book ID and Copy ID must contain numbers only
            if (
                in_array($field, ['book_id', 'copy_id'], true) &&
                is_string($search) &&
                trim($search) !== '' &&
                !ctype_digit(trim($search))
            ) {
                $validator->errors()->add(
                    'book_search',
                    'Enter a number when searching by Book ID or Book Copy ID.'
                );
            }
        });

        // Determine whether validation passed
        $searchIsValid = $validator->passes();
        $searchErrors = $validator->errors();

        // Only use the submitted values when validation succeeds
        $filters = $searchIsValid ? $validator->validated() : [];

        $bookSearch = trim($filters['book_search'] ?? '');
        $bookField = $filters['book_field'] ?? 'title';
        $copyStatus = $filters['copy_status'] ?? '';

        $bookCopies = null;

        // Preserve the search filters when changing result pages
        $searchState = [
            'book_search' => $bookSearch,
            'book_field' => $bookField,
            'copy_status' => $copyStatus,
        ];

        // Keep this; need to pass to query
        $bookCopies = null;

        // Validate the search, query for book copies, and paginate the results 
        if ($searchIsValid && $bookSearch !== '') {
            $bookSearchService = app(BookSearchService::class);

            $bookCopies = $bookSearchService
                ->searchBooks(
                    $bookSearch,
                    $bookField,
                    $copyStatus
                )
                ->simplePaginate(
                    10,
                    ['*'],
                    'copy_page',
                    (int) ($filters['copy_page'] ?? 1)
                )
                ->appends($searchState);
        }

        // Send the search values and results to books/index.blade.php
        return view('books.index', [
            'bookSearch' => $bookSearch,
            'bookField' => $bookField,
            'copyStatus' => $copyStatus,
            'bookCopies' => $bookCopies,
            'searchErrors' => $searchErrors,
            'searchState' => $searchState
        ]);
    }
}
