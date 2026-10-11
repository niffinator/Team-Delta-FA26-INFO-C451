@extends('layouts.app')

@section('title', 'Checkout Book')

@section('content')

    <h2>Check Out a Book</h2>

    @if (session('success'))
        <div role="status">
            <p>
                <strong>{{ session('success') }}</strong>
            </p>

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
        <a href="{{route('checkout.index')}}">New Checkout Transaction</a>
    </p>

    <h3>Member Search</h3>
    <form method="GET" action="{{ route('checkout.index') }}">
        <div>
            <label for="member_search">Member Name or ID:</label>
            <input
                type="search"
                id="member_search"
                name="member_search"
                value="{{ $memberSearch }}"
                maxlength = "150"
                placeholder="Enter member info..."
            >
        </div>

        <input type="hidden" name="book_search" value="{{ $bookSearch }}">
        <input type="hidden" name="book_field" value="{{ $bookField }}">
        <input type="hidden" name="copy_status" value="{{ $copyStatus }}">
        <input type="hidden" name="selected_member_id" value="{{ $selectedMember?->member_id }}">
        <input type="hidden" name="selected_copy_id" value="{{ $selectedCopy?->copy_id }}">
        
        <button type="submit">Search Members</button>
    </form>

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
                                    <a href="{{ route('checkout.index', array_merge($searchState, ['selected_member_id' => $member->member_id]))}}">
                                        Select {{ $member->first_name }} {{ $member->last_name }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($members->previousPageURL() || $members->hasMorePages())
            <nav aria-label="Member results">
                @if ($members->previousPageURL())
                    <a href="{{ $members->previousPageURL() }}">Previous</a>
                @endif

                <span>Page {{ $members->currentPage() }} </span>

                @if ($members->hasMorePages())
                    <a href="{{ $members->nextPageURL() }}">Next</a>
                @endif
            </nav>
        @endif
    @endif

    <hr>

    <h3> Find a Book Copy</h3>

    <form method="GET" action="{{ route('checkout.index') }}">
        <div>
            <label for="book_field">Search by:</label>

            <select id="book_field" name="book_field">
                <option value="title" @Selected($bookField === 'title')>Title</option>
                <option value="author" @Selected($bookField === 'author')>Author</option>
                <option value="isbn" @Selected($bookField === 'isbn')>ISBN</option>
                <option value="genre" @Selected($bookField === 'genre')>Genre</option>
                <option value="book_id" @Selected($bookField === 'book_id')>Book ID</option>
                <option value="copy_id" @Selected($bookField === 'copy_id')>Copy ID</option>
                <option value="call_number" @Selected($bookField === 'call_number')>Call Number</option>
            </select>
        </div>
        
        <div>
            <label for="book_search">Search Text:</label>
            <input
                type="search"
                id="book_search"
                name="book_search"
                value="{{ $bookSearch }}"
                maxlength="150"
                placeholder="Enter search here..."
            >
        </div>
        
        <div>
            <label for="copy_status">Copy Status:</label>
            <select id="copy_status" name="copy_status">
                <option value="" @selected($copyStatus === '')>Any Status</option>
                <option value="available" @selected($copyStatus === 'available')>Available</option>
                <option value="checked_out" @selected($copyStatus === 'checked out')>Checked Out</option>
            </select>
        </div>

        <input type="hidden" name="member_search" value="{{ $memberSearch }}">
        <input type="hidden" name="selected_member_id" value="{{ $selectedMember?->member_id}}">
        <input type="hidden" name="selected_copy_id" value="{{ $selectedCopy?->copy_id }}">
        <button type="submit">Search for Books</button>
    </form>

    @if ($bookCopies !== null)
        @if ($bookCopies->isEmpty())
            <p role="status">
                No book copies were found on this results page.
                Try another search or availability filter.
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

    <h3>3. Review and Check Out</h3>

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