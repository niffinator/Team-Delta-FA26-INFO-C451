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
            'copy_page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);

        $validator->after(function ($validator) use ($request) {
            $field = $request->query('book_field');
            $search = $request->query('book_search');
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

        $memberSearch = trim($filters['member_search'] ?? '');
        $bookSearch = trim($filters['book_search'] ?? '');
        $bookField = $filters['book_field'] ?? 'title';
        $copyStatus = $filters['copy_status'] ?? '';

        $selectedMember = null;
        $selectedCopy = null;
        $members = null;
        $bookCopies = null;

        if (!empty($filters['selected_member_id'])) {
            $selectedMember = Member::find($filters['selected_member_id']);

            if (!$selectedMember) {
                $searchErrors->add(
                    'selected_member_id',
                    'The selected member no longer exists. Select another member.'
                );
            }
        }

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

        $searchState = [
            'member_search' => $memberSearch,
            'book_search' => $bookSearch,
            'book_field' => $bookField,
            'copy_status' => $copyStatus,
            'selected_member_id' => $selectedMember?->member_id,
            'selected_copy_id' => $selectedCopy?->copy_id,
        ];

        if ($searchIsValid && $memberSearch !== '') {
            $memberQuery = Member::query();

            if (ctype_digit($memberSearch)) {
                $memberQuery->where('member_id', $memberSearch);
            } else {
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

        if (
            $searchIsValid &&
            ($bookSearch !== '' || $copyStatus !== '')
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

            if ($copyStatus !== '') {
                $copyQuery->where('status', $copyStatus);
            }

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
        $activeHold = Hold::where('book_id', $bookCopy->book_id)
    ->where('status', 'active')
    ->orderBy('hold_date')
    ->first();

if ($activeHold && (int) $activeHold->member_id !== (int) $member->member_id) {
    return back()
        ->withErrors([
            'copy_id' => 'This book is reserved for another member.'
        ])
        ->withInput();
}

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

    public function store(
        Request $request,
        CheckoutService $checkoutService
    ) {
        $user = $request->user();

        abort_unless(
            $user,
            403,
            'You must be signed in to check out a book.'
        );

        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'min:1'],
            'copy_id' => ['required', 'integer', 'min:1'],
        ]);

        $transaction = $checkoutService->checkout(
            $user,
            (int) $validated['member_id'],
            (int) $validated['copy_id']
        );

        return redirect()
            ->route('checkout.index')
            ->with('success', 'The book has been successfully checked out.')
            ->with('checkout_transaction_id', $transaction->transaction_id);
    }
}