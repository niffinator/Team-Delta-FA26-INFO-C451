<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MemberSearchService;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;

class ReturnController extends Controller
{
    // Display the Return Books page and member search results
    public function index(
        Request $request,
        MemberSearchService $memberSearchService
    ): View {
        Gate::authorize('process-circulation');

        // Validate search and member selection parameters
        $validator = Validator::make($request->query(), [
            'member_search' => ['nullable', 'string', 'max:150'],
            'selected_member_id' => ['nullable', 'integer', 'min:1'],
            'member_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
        ]);

        $searchIsValid = $validator->passes();
        $searchErrors = $validator->errors();
        $filters = $searchIsValid ? $validator->validated() : [];

        // Prepare the search term and selected member
        $memberSearch = trim($filters['member_search'] ?? '');

        $selectedMember = null;
        $members = null;
        $checkedOutTransactions = null;

        // Preserve search and selection information between requests
        $searchState = [
            'member_search' => $memberSearch,
            'selected_member_id' => $filters['selected_member_id'] ?? null,
        ];

        // Search members using the existing shared service
        if ($searchIsValid && $memberSearch !== '') {
            $members = $memberSearchService
                ->searchMembers($memberSearch)
                ->simplePaginate(
                    10,
                    ['*'],
                    'member_page',
                    (int) ($filters['member_page'] ?? 1)
                )
                ->appends($searchState);
        }

        // Retrieve the member selected from the search results
        if (
            $searchIsValid &&
            ! empty($filters['selected_member_id'])
        ) {
            $selectedMember = Member::find(
                $filters['selected_member_id']
            );

            if (! $selectedMember) {
                $searchErrors->add(
                    'selected_member_id',
                    'The selected member no longer exists. Select another member.'
                );
            }
        }

        // Retrieve all active checkout records for the selected member
        if ($selectedMember) {
            $checkedOutTransactions = Transaction::with([
                'bookCopy.book',
                'user',
            ])
                ->where('member_id', $selectedMember->member_id)
                ->whereNull('return_date')
                ->orderBy('due_date')
                ->orderBy('transaction_id')
                ->get();
        }

        // Retrieve completed returns from the most recent submission
        $returnedTransactions = collect();

        $returnedTransactionIds = $request->session()
            ->get('returned_transaction_ids', []);

        if (
            $request->session()->has('success') &&
            is_array($returnedTransactionIds) &&
            ! empty($returnedTransactionIds)
        ) {
            $returnedTransactions = Transaction::with([
                'member',
                'bookCopy.book',
                'user',
                'returnedBy',
            ])
                ->whereIn(
                    'transaction_id',
                    $returnedTransactionIds
                )
                ->whereNotNull('return_date')
                ->where(
                    'returned_by_user_id',
                    $request->user()->user_id
                )
                ->orderBy('transaction_id')
                ->get();
        }

        // Display the member search, active checkouts, and return results
        return view('returns.index', [
            'memberSearch' => $memberSearch,
            'members' => $members,
            'selectedMember' => $selectedMember,
            'checkedOutTransactions' => $checkedOutTransactions,
            'returnedTransactions' => $returnedTransactions,
            'searchState' => $searchState,
            'searchErrors' => $searchErrors,
        ]);
    }

    // Process one or multiple selected book returns
    public function store(
        Request $request,
        ReturnService $returnService
    ): RedirectResponse {
        Gate::authorize('process-circulation');

        $user = $request->user();

        // Only authenticated library employees may process returns
        abort_unless(
            $user instanceof User,
            403,
            'You must be signed in to return books.'
        );

        // Validate the selected member and checkout transactions
        $validated = $request->validate([
            'member_id' => [
                'required',
                'integer',
                'min:1',
                'exists:members,member_id',
            ],
            'transaction_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'transaction_ids.*' => [
                'required',
                'integer',
                'min:1',
                'distinct',
            ],
        ]);

        // Pass all selected returns to the service as one operation
        $transactions = $returnService->returnBooks(
            $user,
            (int) $validated['member_id'],
            array_map('intval', $validated['transaction_ids'])
        );

        // Store the completed transaction IDs for confirmation
        $returnedTransactionIds = $transactions
            ->pluck('transaction_id')
            ->all();

        $returnedCount = count($returnedTransactionIds);

        $successMessage = $returnedCount === 1
            ? 'The selected book has been successfully returned.'
            : $returnedCount . ' books have been successfully returned.';

        // Return to the selected member with updated checkout information
        return redirect()
            ->route('returns.index', [
                'selected_member_id' => $validated['member_id'],
            ])
            ->with('success', $successMessage)
            ->with(
                'returned_transaction_ids',
                $returnedTransactionIds
            );
    }
}
