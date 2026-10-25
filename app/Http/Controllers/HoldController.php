<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Hold;
use App\Models\Member;
use App\Services\BookSearchService;
use App\Services\MemberSearchService;
use App\Services\HoldService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class HoldController extends Controller
{
    // Show the hold placement page.
    public function index(
        Request $request,
        MemberSearchService $memberSearchService,
        BookSearchService $bookSearchService
    ): View {
        Gate::authorize('process-circulation');

        $rules = [
            'member_search' => ['nullable', 'string', 'max:150'],
            'book_search' => ['nullable', 'string', 'max:150'],
            'book_field' => [
                'nullable',
                'string',
                'in:title,author,isbn,genre,book_id',
            ],
            'selected_member_id' => ['nullable', 'integer', 'min:1'],
            'selected_book_id' => ['nullable', 'integer', 'min:1'],
            'member_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'book_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'hold_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];

        if ($request->query('book_field') === 'book_id') {
            $rules['book_search'][] = 'regex:/^[0-9]+$/';
        }

        $validator = Validator::make($request->query(), $rules, [
            'book_search.regex' => 'Enter a number when searching by Book ID.',
        ]);

        $searchIsValid = $validator->passes();
        $searchErrors = $validator->errors();
        $filters = $searchIsValid ? $validator->validated() : [];

        $memberSearch = trim($filters['member_search'] ?? '');
        $bookSearch = trim($filters['book_search'] ?? '');
        $bookField = $filters['book_field'] ?? 'title';

        $selectedMember = null;
        $selectedBook = null;

        if (! empty($filters['selected_member_id'])) {
            $selectedMember = Member::find($filters['selected_member_id']);

            if (! $selectedMember) {
                $searchErrors->add(
                    'selected_member_id',
                    'The selected member no longer exists. Select another member.'
                );
            }
        }

        if (! empty($filters['selected_book_id'])) {
            $selectedBook = Book::find($filters['selected_book_id']);

            if (! $selectedBook) {
                $searchErrors->add(
                    'selected_book_id',
                    'The selected book no longer exists. Select another book.'
                );
            }
        }

        $searchState = [
            'member_search' => $memberSearch,
            'book_search' => $bookSearch,
            'book_field' => $bookField,
            'selected_member_id' => $selectedMember?->member_id,
            'selected_book_id' => $selectedBook?->book_id,
            'member_page' => (int) ($filters['member_page'] ?? 1),
            'book_page' => (int) ($filters['book_page'] ?? 1),
            'hold_page' => (int) ($filters['hold_page'] ?? 1),
        ];

        $members = null;
        $books = null;

        if ($searchIsValid && $memberSearch !== '') {
            $members = $memberSearchService
                ->searchMembers($memberSearch)
                ->simplePaginate(
                    10,
                    ['*'],
                    'member_page',
                    $searchState['member_page']
                )
                ->appends($searchState);
        }

        if ($searchIsValid && $bookSearch !== '') {
            $books = $bookSearchService
                ->searchBookTitles($bookSearch, $bookField)
                ->simplePaginate(
                    10,
                    ['*'],
                    'book_page',
                    $searchState['book_page']
                )
                ->appends($searchState);
        }

        $holds = Hold::with(['member', 'book'])
            ->orderByDesc('hold_date')
            ->orderByDesc('hold_id')
            ->simplePaginate(
                20,
                ['*'],
                'hold_page',
                $searchState['hold_page']
            )
            ->appends($searchState);

        return view('holds.index', compact(
            'members',
            'books',
            'holds',
            'memberSearch',
            'bookSearch',
            'bookField',
            'selectedMember',
            'selectedBook',
            'searchState',
            'searchErrors'
        ));
    }

    // Save a new hold
    public function store(
        Request $request,
        HoldService $holdService
    ): RedirectResponse {
        Gate::authorize('process-circulation');

        $validated = $request->validate([
            'member_id' => [
                'required',
                'integer',
                'min:1',
                'exists:members,member_id',
            ],
            'book_id' => [
                'required',
                'integer',
                'min:1',
                'exists:books,book_id',
            ],
        ]);

        $holdService->placeHold(
            (int) $validated['member_id'],
            (int) $validated['book_id']
        );

        return redirect()
            ->route('holds.index')
            ->with('success', 'Book hold placed successfully.');
    }

    // Cancel an active hold
    public function cancel(
        HoldService $holdService,
        int $hold_id
    ): RedirectResponse {
        Gate::authorize('process-circulation');

        $holdService->cancelHold($hold_id);

        return redirect()
            ->route('holds.index')
            ->with('success', 'Book hold cancelled successfully.');
    }
}