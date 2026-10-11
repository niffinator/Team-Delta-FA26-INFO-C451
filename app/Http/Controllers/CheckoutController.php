<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Models\BookCopy;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $checkoutTransaction = null;

        $user = $request->user();

        // Get the most recently completed checkout from the session
        $transactionId = $request->session()->get('checkout_transaction_id');

        if ($user && $transactionId) {
            $checkoutTransaction = Transaction::with([
                'member',
                'bookCopy.book',
                'user',
            ])
                ->where('user_id', $user->user_id)
                ->find($transactionId);
        }

        // Validate the search parameters entered by the user
        $validator = Validator::make($request->query(), [
            'member_search' => ['nullable', 'string', 'max:150'],
            'book_search' => ['nullable', 'string', 'max:150'],
            'book_field' => [
                'nullable',
                'in:title,author,isbn,genre,book_id,copy_id,call_number'
            ],
            'copy_status' => ['nullable', 'in:available,checked_out'],
            'selected_member_id' => ['nullable', 'integer', 'min:1'],
            'selected_copy_id' => ['nullable', 'integer', 'min:1'],
            'member_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'copy_page' => ['nullable', 'integer', 'min:1', 'max:100000'],

            'book_search_action' => ['nullable', 'in:1']
        ]);

        // Perform additional validation for book searches
        $validator->after(function ($validator) use ($request) {
            $field = $request->query('book_field');
            $search = $request->query('book_search');

            // We only apply this rule when the book search form is submitted
            if (
                $request->query('book_search_action') === '1' &&
                (!is_string($search) || trim($search) === '')
            ) {
                $validator->errors()->add(
                    'book_search',
                    'Please enter book information before searching.'
                );
            }

            // Check that Book ID and Copy ID searches contain numbers
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

        $searchIsValid = $validator->passes();
        $searchErrors = $validator->errors();
        $filters = $searchIsValid ? $validator->validated() : [];

        // Prepare validated search values for use in database queries
        $memberSearch = trim($filters['member_search'] ?? '');
        $bookSearch = trim($filters['book_search'] ?? '');
        $bookField = $filters['book_field'] ?? 'title';
        $copyStatus = $filters['copy_status'] ?? '';

        $selectedMember = null;
        $selectedCopy = null;
        $members = null;
        $bookCopies = null;

        // Find the member selected from the member search results
        if (!empty($filters['selected_member_id'])) {
            $selectedMember = Member::find($filters['selected_member_id']);

            if (!$selectedMember) {
                $searchErrors->add(
                    'selected_member_id',
                    'The selected member no longer exists. Select another member.'
                );
            }
        }

        // Find the selected physical copy and its associated book
        if (!empty($filters['selected_copy_id'])) {
            $selectedCopy = BookCopy::with('book')
                ->find($filters['selected_copy_id']);

            if (!$selectedCopy || !$selectedCopy->book) {
                $selectedCopy = null;

                $searchErrors->add(
                    'selected_copy_id',
                    'The selected book copy could not be found. Select another copy.'
                );
            }
        }

        // Keep the existing search and selection values between requests
        $searchState = [
            'member_search' => $memberSearch,
            'book_search' => $bookSearch,
            'book_field' => $bookField,
            'copy_status' => $copyStatus,
            'selected_member_id' => $selectedMember?->member_id,
            'selected_copy_id' => $selectedCopy?->copy_id,
        ];

        // Search for members by their ID or name
        if ($searchIsValid && $memberSearch !== '') {
            $memberQuery = Member::query();

            if (ctype_digit($memberSearch)) {
                $memberQuery->where('member_id', $memberSearch);
            } else {

                // \s+ separates a full name that may have multiple whitespace characters
                $nameParts = preg_split('/\s+/', $memberSearch);

                foreach ($nameParts as $part) {
                    $memberQuery->where(function ($query) use ($part) {
                        $query->where('first_name', 'like', '%' . $part . '%')
                            ->orWhere('last_name', 'like', '%' . $part . '%');
                    });
                }
            }

            $members = $memberQuery
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('member_id')
                ->simplePaginate(
                    10,

                    ['*'],
                    'member_page',
                    (int) ($filters['member_page'] ?? 1)
                )
                ->appends($searchState);
        }

        // The copy status remains an optional filter for that search
        if (
            $searchIsValid &&
            $bookSearch !== ''
        ) {
            $copyQuery = BookCopy::with('book')->whereHas('book');

            if ($bookSearch !== '') {
                if ($bookField === 'copy_id') {
                    $copyQuery->where('copy_id', $bookSearch);
                } elseif ($bookField === 'call_number') {
                    $copyQuery->where(
                        'call_number',
                        'like',
                        '%' . $bookSearch . '%'
                    );
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
            }

            // Apply the optional availability filter
            if ($copyStatus !== '') {

                // The form sends checked_out, but the service currently stores the value as 'checked out'. No issues found but keep in mind
                $databaseStatus = $copyStatus === 'checked_out'
                    ? 'checked out'
                    : $copyStatus;

                $copyQuery->where('status', $databaseStatus);
            }

            $bookCopies = $copyQuery
                ->orderBy('book_id')
                ->orderBy('copy_id')
                ->simplePaginate(
                    10,

                    // Corrected the column wildcard due to 
                    ['*'],
                    'copy_page',
                    (int) ($filters['copy_page'] ?? 1)
                )
                ->appends($searchState);
        }

        // Return the checkout page with search results and selections
        return view('checkout.index', [
            'checkoutTransaction' => $checkoutTransaction,
            'memberSearch' => $memberSearch,
            'bookSearch' => $bookSearch,
            'bookField' => $bookField,
            'copyStatus' => $copyStatus,
            'selectedMember' => $selectedMember,
            'selectedCopy' => $selectedCopy,
            'members' => $members,
            'bookCopies' => $bookCopies,
            'searchState' => $searchState,
            'searchErrors' => $searchErrors
        ]);
    }

    // Store a new checkout transaction
    public function store(
        Request $request,
        CheckoutService $checkoutService
    ) {
        $user = $request->user();

        // Only authenticated users may complete a checkout
        abort_unless(
            $user,
            403,
            'You must be signed in to check out a book.'
        );

        // Confirm that a member and physical book copy were submitted
        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'min:1'],
            'copy_id' => ['required', 'integer', 'min:1'],
        ]);

        // Pass the checkout to the service for business-rule checks
        $transaction = $checkoutService->checkout(
            $user,
            (int) $validated['member_id'],
            (int) $validated['copy_id']
        );

        // Return to the checkout page with a confirmation message
        return redirect()
            ->route('checkout.index')
            ->with('success', 'The book has been successfully checked out.')
            ->with('checkout_transaction_id', $transaction->transaction_id);
    }
}
