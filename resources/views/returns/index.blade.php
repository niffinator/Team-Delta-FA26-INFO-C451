@extends('layouts.app')

@section('title', 'Return Books')

@section('content')

    <div class="container text-start mt-4">

        <h2 class="mb-4">Return Books</h2>

        <p>
            Search for a library member, review their currently checked-out
            books, and select the books being returned
        </p>

        {{-- Display successful return confirmation --}}
        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        {{-- Display return processing and validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Unable to process return</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Display member search validation errors --}}
        @if ($searchErrors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the member search</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($searchErrors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- MEMBER SEARCH --}}
        <section class="card mb-4" aria-labelledby="member-search-heading">

            <div class="card-body">

                <h3 class="h5 card-title" id="member-search-heading">
                    Search for Member
                </h3>

                <form method="GET" action="{{ route('returns.index') }}">

                    <div class="mb-3">
                        <label for="member_search" class="form-label">
                            Member Information
                        </label>

                        <input
                            type="search"
                            class="form-control"
                            id="member_search"
                            name="member_search"
                            value="{{ $memberSearch }}"
                            maxlength="150"
                            placeholder="Enter Member ID, name, email, or phone"
                            aria-describedby="memberSearchHelp"
                            required
                        >

                        <div id="memberSearchHelp" class="form-text">
                            Search using a member's ID, name,
                            email address, or phone number
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            Search Members
                        </button>

                        <a
                            href="{{ route('returns.index') }}"
                            class="btn btn-secondary"
                        >
                            Clear Search
                        </a>
                    </div>

                </form>

            </div>

        </section>

        {{-- MEMBER SEARCH RESULTS --}}
        @if ($members !== null)

            <section
                class="card mb-4"
                aria-labelledby="member-results-heading"
            >

                <div class="card-body">

                    <h3 class="h5 card-title" id="member-results-heading">
                        Member Search Results
                    </h3>

                    @if ($members->isEmpty())

                        <p class="alert alert-info mb-0" role="status">
                            No members were found matching your search
                        </p>

                    @else

                        <div class="table-responsive">

                            <table class="table table-striped table-hover">

                                <caption>
                                    Members matching the search criteria
                                </caption>

                                <thead>
                                    <tr>
                                        <th scope="col">Member ID</th>
                                        <th scope="col">First Name</th>
                                        <th scope="col">Last Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($members as $member)

                                        <tr>
                                            <td>{{ $member->member_id }}</td>

                                            <td>{{ $member->first_name }}</td>

                                            <td>{{ $member->last_name }}</td>

                                            <td>{{ $member->email }}</td>

                                            <td>{{ $member->phone }}</td>

                                            <td>

                                                @if (
                                                    $selectedMember?->member_id
                                                    === $member->member_id
                                                )

                                                    <span class="badge bg-success">
                                                        Selected
                                                    </span>

                                                @else

                                                    <a
                                                        href="{{ route('returns.index', array_merge($searchState, ['selected_member_id' => $member->member_id])) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                    >
                                                        Select Member
                                                    </a>

                                                @endif

                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        {{-- Preserve member search pagination --}}
                        @if (
                            $members->previousPageUrl()
                            || $members->hasMorePages()
                        )

                            <nav
                                aria-label="Member search results pagination"
                                class="d-flex gap-3 align-items-center"
                            >

                                @if ($members->previousPageUrl())
                                    <a href="{{ $members->previousPageUrl() }}">
                                        Previous
                                    </a>
                                @endif

                                <span>
                                    Page {{ $members->currentPage() }}
                                </span>

                                @if ($members->hasMorePages())
                                    <a href="{{ $members->nextPageUrl() }}">
                                        Next
                                    </a>
                                @endif

                            </nav>

                        @endif

                    @endif

                </div>

            </section>

        @endif

        {{-- SELECTED MEMBER AND ACTIVE CHECKOUTS --}}
        @if ($selectedMember)

            <section
                class="card mb-4"
                aria-labelledby="selected-member-heading"
            >

                <div class="card-header">

                    <h3 class="h5 mb-0" id="selected-member-heading">
                        Selected Member
                    </h3>

                </div>

                <div class="card-body">

                    <dl class="row mb-0">

                        <dt class="col-sm-3">Member ID</dt>
                        <dd class="col-sm-9">
                            {{ $selectedMember->member_id }}
                        </dd>

                        <dt class="col-sm-3">Member Name</dt>
                        <dd class="col-sm-9">
                            {{ $selectedMember->first_name }}
                            {{ $selectedMember->last_name }}
                        </dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">
                            {{ $selectedMember->email }}
                        </dd>

                        <dt class="col-sm-3">Phone</dt>
                        <dd class="col-sm-9">
                            {{ $selectedMember->phone }}
                        </dd>

                    </dl>

                </div>

            </section>

            {{-- CURRENTLY CHECKED OUT BOOKS --}}
            <section
                class="card mb-4"
                aria-labelledby="checked-out-heading"
            >

                <div class="card-body">

                    <h3 class="h5 card-title" id="checked-out-heading">
                        Currently Checked Out Books
                    </h3>

                    @if (
                        $checkedOutTransactions === null
                        || $checkedOutTransactions->isEmpty()
                    )

                        <div class="alert alert-info" role="status">
                            This member currently has no books checked out
                        </div>

                    @else

                        <p>
                            Select the books being returned, then confirm
                            the return transaction
                        </p>

                        <form
                            method="POST"
                            action="{{ route('returns.store') }}"
                            onsubmit="return confirm('Return the selected books for this member?')"
                        >

                            @csrf

                            {{-- Preserve the member associated with the returns --}}
                            <input
                                type="hidden"
                                name="member_id"
                                value="{{ $selectedMember->member_id }}"
                            >

                            <div class="table-responsive">

                                <table
                                    class="table table-striped table-hover align-middle"
                                >

                                    <caption>
                                        Active checkout transactions for
                                        {{ $selectedMember->first_name }}
                                        {{ $selectedMember->last_name }}
                                    </caption>

                                    <thead>

                                        <tr>
                                            <th scope="col">Return</th>
                                            <th scope="col">Book Information</th>
                                            <th scope="col">Copy Information</th>
                                            <th scope="col">Checkout Date</th>
                                            <th scope="col">Due Date</th>
                                            <th scope="col">Checkout Employee</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        @foreach (
                                            $checkedOutTransactions
                                            as $transaction
                                        )

                                            <tr>

                                                <td>

                                                    <div class="form-check">

                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            id="transaction_{{ $transaction->transaction_id }}"
                                                            name="transaction_ids[]"
                                                            value="{{ $transaction->transaction_id }}"
                                                            @checked(
                                                                in_array(
                                                                    $transaction->transaction_id,
                                                                    old('transaction_ids', [])
                                                                )
                                                            )
                                                        >

                                                        <label
                                                            class="form-check-label"
                                                            for="transaction_{{ $transaction->transaction_id }}"
                                                        >
                                                            Select
                                                        </label>

                                                    </div>

                                                </td>

                                                <td>

                                                    <strong>
                                                        {{ $transaction->bookCopy->book->title }}
                                                    </strong>

                                                    <div class="small">
                                                        Author:
                                                        {{ $transaction->bookCopy->book->author }}
                                                    </div>

                                                    <div class="small">
                                                        ISBN:
                                                        {{ $transaction->bookCopy->book->ISBN }}
                                                    </div>

                                                    <div class="small">
                                                        Genre:
                                                        {{ $transaction->bookCopy->book->genre }}
                                                    </div>

                                                    <div class="small">
                                                        Book ID:
                                                        {{ $transaction->bookCopy->book->book_id }}
                                                    </div>

                                                </td>

                                                <td>

                                                    <div>
                                                        Copy ID:
                                                        {{ $transaction->copy_id }}
                                                    </div>

                                                    <div>
                                                        Call Number:
                                                        {{ $transaction->bookCopy->call_number }}
                                                    </div>

                                                    <div>
                                                        Status:
                                                        {{ ucfirst($transaction->bookCopy->status) }}
                                                    </div>

                                                    <div class="small text-muted">
                                                        Transaction:
                                                        {{ $transaction->transaction_id }}
                                                    </div>

                                                </td>

                                                <td>
                                                    {{ $transaction->checkout_date->format('M j, Y') }}
                                                </td>

                                                <td>

                                                    {{ $transaction->due_date->format('M j, Y') }}

                                                    @if (
                                                        $transaction->due_date->startOfDay()->lt(
                                                            now()->startOfDay()
                                                        )
                                                    )

                                                        <div class="mt-1">
                                                            <span class="badge bg-warning text-dark">
                                                                Overdue
                                                            </span>
                                                        </div>

                                                    @endif

                                                </td>

                                                <td>
                                                    {{ $transaction->user?->username ?? 'Unknown' }}
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                            {{-- Return confirmation controls --}}
                            <div class="mt-3">

                                <p class="text-muted small">
                                    Late fees are calculated automatically
                                    at $0.05 per overdue calendar day
                                </p>

                                <div class="d-flex flex-wrap gap-2">

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        Confirm Selected Returns
                                    </button>

                                    <a
                                        href="{{ route('returns.index', ['selected_member_id' => $selectedMember->member_id]) }}"
                                        class="btn btn-secondary"
                                    >
                                        Clear Selection
                                    </a>

                                </div>

                            </div>

                        </form>

                    @endif

                </div>

            </section>

        @endif

        {{-- COMPLETED RETURN TRANSACTIONS --}}
        @if ($returnedTransactions->isNotEmpty())

            <section
                class="card mb-4"
                aria-labelledby="completed-returns-heading"
            >

                <div class="card-header">

                    <h3 class="h5 mb-0" id="completed-returns-heading">
                        Completed Returns
                    </h3>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-striped">

                            <caption>
                                Books returned in the most recent transaction
                            </caption>

                            <thead>

                                <tr>
                                    <th scope="col">Transaction ID</th>
                                    <th scope="col">Book</th>
                                    <th scope="col">Copy ID</th>
                                    <th scope="col">Member</th>
                                    <th scope="col">Return Date</th>
                                    <th scope="col">Late Fee</th>
                                    <th scope="col">Returned By</th>
                                </tr>

                            </thead>

                            <tbody>

                                @foreach (
                                    $returnedTransactions
                                    as $transaction
                                )

                                    <tr>

                                        <td>
                                            {{ $transaction->transaction_id }}
                                        </td>

                                        <td>
                                            {{ $transaction->bookCopy->book->title }}
                                        </td>

                                        <td>
                                            {{ $transaction->copy_id }}
                                        </td>

                                        <td>
                                            {{ $transaction->member->first_name }}
                                            {{ $transaction->member->last_name }}
                                        </td>

                                        <td>
                                            {{ $transaction->return_date->format('M j, Y') }}
                                        </td>

                                        <td>
                                            ${{ number_format((float) $transaction->late_fee, 2) }}
                                        </td>

                                        <td>
                                            {{ $transaction->returnedBy?->username ?? 'Unknown' }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                            <tfoot>

                                <tr>

                                    <th colspan="5" scope="row">
                                        Total Late Fees
                                    </th>

                                    <td colspan="2">
                                        <strong>
                                            ${{ number_format(
                                                $returnedTransactions->sum('late_fee'),
                                                2
                                            ) }}
                                        </strong>
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                    {{-- Outstanding holds are preserved following returns --}}
                    <div class="alert alert-info mb-0" role="status">
                        Any outstanding book holds remain active
                        and will be enforced during future checkouts
                    </div>

                </div>

            </section>

        @endif

        {{-- NAVIGATION --}}
        <div class="d-flex flex-wrap gap-2 mb-4">

            <a
                href="{{ route('checkout.index') }}"
                class="btn btn-outline-primary"
            >
                Check Out a Book
            </a>

            <a
                href="{{ route('holds.index') }}"
                class="btn btn-outline-secondary"
            >
                Book Holds
            </a>

            <a
                href="{{ route('home') }}"
                class="btn btn-outline-secondary"
            >
                Home
            </a>

        </div>

    </div>

@endsection
