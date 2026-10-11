<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use Illuminate\Http\Request;
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

        if (
            $searchIsValid &&
            $bookSearch !== ''
        ) {
            // Retrieve physical copies and their associated Book records
            $copyQuery = BookCopy::with('book')->whereHas('book');

            if ($bookField === 'copy_id') {

                // Copy ID belongs to the BookCopy table
                $copyQuery->where('copy_id', $bookSearch);

            } elseif ($bookField === 'call_number') {

                // Call number also belongs to the BookCopy table
                $copyQuery->where(
                    'call_number',
                    'like',
                    '%' . $bookSearch . '%'
                );

            } else {

                $copyQuery->whereHas(
                    'book',
                    function ($query) use ($bookField, $bookSearch) {

                        // Book ID is searched as an exact value
                        if ($bookField === 'book_id') {
                            $query->where('book_id', $bookSearch);

                        } else {

                            // LIKE allows partial matches for text searches
                            // Example: "Harry" can match "Harry Potter"
                            $query->where(
                                $bookField,
                                'like',
                                '%' . $bookSearch . '%'
                            );
                        }
                    }
                );
            }

            // Apply the optional availability filter
            if ($copyStatus !== '') {

                // The form uses checked_out, but CheckoutService currently stores the status as 'checked out'
                $databaseStatus = $copyStatus === 'checked_out'
                    ? 'checked out'
                    : $copyStatus;

                $copyQuery->where('status', $databaseStatus);
            }

            // Display 10 copies per page
            $bookCopies = $copyQuery
                ->orderBy('book_id')
                ->orderBy('copy_id')
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
