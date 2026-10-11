
@extends('layouts.app')

@section('title', 'Checkout Book')

@section('content')

    <h2>Check Out a Book</h2>

    {{-- Display success message after a successful checkout --}}
    @if (session('success'))
        <div role="status">
            <p>
                <strong>{{ session('success') }}</strong>
            </p>

            {{-- Display the completed checkout information --}}
            @if ($checkoutTransaction)
                <h3>Completed Checkout</h3>

                <dl>
                    <dt>Transaction ID</dt>
                    <dd>{{ $checkoutTransaction->transaction_id }}</dd>

                    <dt>Member</dt>
                    <dd>
                        {{ $checkoutTransaction->member->first_name }}
                        {{ $checkoutTransaction->member->last_name }}
                        (ID: {{ $checkoutTransaction->member_id }})
                    </dd>

                    <dt>Book</dt>
                    <dd>
                        {{ $checkoutTransaction->bookCopy->book->title }}
                    </dd>

                    <dt>Book Copy ID</dt>
                    <dd>{{ $checkoutTransaction->copy_id }}</dd>

                    <dt>Processed By</dt>
                    <dd>{{ $checkoutTransaction->user->username }}</dd>

                    <dt>Checkout Date</dt>
                    <dd>
                        {{ $checkoutTransaction->checkout_date->format('F j, Y') }}
                    </dd>

                    <dt>Due Date</dt>
                    <dd>
                        {{ $checkoutTransaction->due_date->format('F j, Y') }}
                    </dd>
                </dl>
            @endif
        </div>
    @endif

    {{-- Display validation or checkout errors --}}
    @if ($errors->any())
        <div role="alert">
            <strong>We could not complete your checkout:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Display search errors --}}
    {{-- Search errors are supplied by CheckoutController's index method --}}
    @if ($searchErrors->any())
        <div role="alert">
            <strong>Please correct the following errors:</strong>

            <ul>
                @foreach ($searchErrors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p>
        Search for and select the member and book copy to be checked out.
        Checkout and due dates are assigned when checkout is completed.
    </p>

    <p>
        <a href="{{ route('checkout.index') }}">New Checkout Transaction</a>
    </p>

    {{-- MEMBER SEARCH --}}
    <h3>Member Search</h3>

    <form method="GET" action="{{ route('checkout.index') }}">
        <div>
            <label for="member_search">Member Name or ID:</label>

            <input
                type="search"
                id="member_search"
                name="member_search"
                value="{{ $memberSearch }}"
                maxlength="150"
                placeholder="Member info"
                required
            >
        </div>

        {{-- Preserve existing book searches and selected records --}}
        <input type="hidden" name="book_search" value="{{ $bookSearch }}">
        <input type="hidden" name="book_field" value="{{ $bookField }}">
        <input type="hidden" name="copy_status" value="{{ $copyStatus }}">
        <input type="hidden" name="selected_member_id" value="{{ $selectedMember?->member_id }}">
        <input type="hidden" name="selected_copy_id" value="{{ $selectedCopy?->copy_id }}">

        <button type="submit">Search Members</button>
    </form>

    {{-- MEMBER SEARCH RESULTS --}}
    @if ($members !== null)
        @if ($members->isEmpty())
            <p role="status">
                No members could be found.
                Try another name or ID.
            </p>
        @else
            <table>
                <caption>Member search results</caption>

                <thead>
                    <tr>
                        <th scope="col">Member ID</th>
                        <th scope="col">Member Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Selected</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td>{{ $member->member_id }}</td>

                            <td>
                                {{ $member->first_name }}
                                {{ $member->last_name }}
                            </td>

                            <td>{{ $member->email }}</td>
                            <td>{{ $member->phone }}</td>

                            <td>
                                @if ($selectedMember?->member_id === $member->member_id)
                                    Selected
                                @else
                                    <a href="{{ route('checkout.index', array_merge($searchState, ['selected_member_id' => $member->member_id])) }}">
                                        Select {{ $member->first_name }} {{ $member->last_name }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Member result pagination --}}
        @if ($members->previousPageUrl() || $members->hasMorePages())
            <nav aria-label="Member results">
                @if ($members->previousPageUrl())
                    <a href="{{ $members->previousPageUrl() }}">Previous</a>
                @endif

                <span>Page {{ $members->currentPage() }}</span>

                @if ($members->hasMorePages())
                    <a href="{{ $members->nextPageUrl() }}">Next</a>
                @endif
            </nav>
        @endif
    @endif

    <hr>

    {{-- BOOK SEARCH --}}
    <h3>Find a Book Copy</h3>

    <form method="GET" action="{{ route('checkout.index') }}">

        <input type="hidden" name="book_search_action" value="1">

        <div>
            <label for="book_field">Search by:</label>

            <select id="book_field" name="book_field">
                <option value="title" @selected($bookField === 'title')>Title</option>
                <option value="author" @selected($bookField === 'author')>Author</option>
                <option value="isbn" @selected($bookField === 'isbn')>ISBN</option>
                <option value="genre" @selected($bookField === 'genre')>Genre</option>
                <option value="book_id" @selected($bookField === 'book_id')>Book ID</option>
                <option value="copy_id" @selected($bookField === 'copy_id')>Copy ID</option>
                <option value="call_number" @selected($bookField === 'call_number')>Call Number</option>
            </select>
        </div>

        <div>
            <label for="book_search">Book Search:</label>

            <input
                type="search"
                id="book_search"
                name="book_search"
                value="{{ $bookSearch }}"
                maxlength="150"
                placeholder="Book info"
                required
            >
        </div>

        {{-- BOOK STATUS FILTER --}}
        <div>
            <label for="copy_status">Copy Status:</label>

            <select id="copy_status" name="copy_status">
                <option value="" @selected($copyStatus === '')>Any Status</option>

                <option value="available" @selected($copyStatus === 'available')>Available</option>

                {{-- The controller uses checked_out, translates it to the database's status --}}
                <option value="checked_out" @selected($copyStatus === 'checked_out')>Checked Out</option>
            </select>
        </div>

        {{-- Preserve the existing member search and selections --}}
        <input type="hidden" name="member_search" value="{{ $memberSearch }}">
        <input type="hidden" name="selected_member_id" value="{{ $selectedMember?->member_id }}">
        <input type="hidden" name="selected_copy_id" value="{{ $selectedCopy?->copy_id }}">

        <button type="submit">Search for Books</button>
    </form>

    {{-- BOOK COPY SEARCH RESULTS --}}
    @if ($bookCopies !== null)
        @if ($bookCopies->isEmpty())

            <p role="status">
                No matching book copies were found.
                Try another search term or change the status filter.
            </p>

        @else
            <table>
                <caption>Book copy search results</caption>

                <thead>
                    <tr>
                        <th scope="col">Book ID</th>
                        <th scope="col">Copy ID</th>
                        <th scope="col">Title</th>
                        <th scope="col">Author</th>
                        <th scope="col">ISBN</th>
                        <th scope="col">Call Number</th>
                        <th scope="col">Availability</th>
                        <th scope="col">Selection</th>
                    </tr>
                </thead>

                {{-- Display the matching physical book copies --}}
                <tbody>
                    @foreach ($bookCopies as $copy)
                        <tr>
                            <td>{{ $copy->book_id }}</td>
                            <td>{{ $copy->copy_id }}</td>
                            <td>{{ $copy->book->title }}</td>
                            <td>{{ $copy->book->author }}</td>
                            <td>{{ $copy->book->ISBN }}</td>
                            <td>{{ $copy->call_number }}</td>
                            <td>{{ $copy->status }}</td>

                            <td>
                                {{-- Unavailable copies cannot be selected --}}
                                @if ($copy->status !== 'available')
                                    Unavailable
                                @elseif ($selectedCopy?->copy_id === $copy->copy_id)
                                    Selected
                                @else
                                    <a href="{{ route('checkout.index', array_merge($searchState, ['selected_copy_id' => $copy->copy_id])) }}">
                                        Select Copy {{ $copy->copy_id }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- BOOK COPY RESULT PAGES --}}
        @if ($bookCopies->previousPageUrl() || $bookCopies->hasMorePages())
            <nav aria-label="Book copy result pages">
                @if ($bookCopies->previousPageUrl())
                    <a href="{{ $bookCopies->previousPageUrl() }}">Previous Copies</a>
                @endif

                <span>Page {{ $bookCopies->currentPage() }}</span>

                @if ($bookCopies->hasMorePages())
                    <a href="{{ $bookCopies->nextPageUrl() }}">Next Copies</a>
                @endif
            </nav>
        @endif
    @endif

    <hr>

    {{-- REVIEW AND CHECK OUT --}}
    <h3>3. Review and Check Out</h3>

    {{-- Display the member selected for checkout --}}
    @if ($selectedMember)
        <p>
            <strong>Selected Member:</strong>
            {{ $selectedMember->first_name }}
            {{ $selectedMember->last_name }}
            (Member ID: {{ $selectedMember->member_id }})
        </p>
    @else
        <p>Select a member from the search results.</p>
    @endif

    {{-- Display the physical book copy selected for checkout --}}
    @if ($selectedCopy)
        <p>
            <strong>Selected Book:</strong>
            {{ $selectedCopy->book->title }}
            — {{ $selectedCopy->book->author }}
        </p>

        <p>
            Copy ID: {{ $selectedCopy->copy_id }} |
            Call Number: {{ $selectedCopy->call_number }} |
            Availability: {{ $selectedCopy->status }}
        </p>
    @else
        <p>Select an available book copy from the search results.</p>
    @endif

    {{-- REVIEW AND CHECK OUT --}}
    @if ($selectedMember && $selectedCopy)
        @if ($selectedCopy->status === 'available')
            <p>
                Availability and hold priority will be checked again
                when you submit the checkout.
            </p>

            <form method="POST" action="{{ route('checkout.store') }}">
                @csrf

                <input
                    type="hidden"
                    name="member_id"
                    value="{{ $selectedMember->member_id }}"
                >

                <input
                    type="hidden"
                    name="copy_id"
                    value="{{ $selectedCopy->copy_id }}"
                >

                <button type="submit">Check Out Book</button>
            </form>
        @else
            <p role="alert">
                The selected copy is no longer available.
                Select another available copy.
            </p>
        @endif
    @endif

@endsection
