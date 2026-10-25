@extends('layouts.app')

@section('title', 'Book Holds')

@section('content')
    <div class="text-start py-3">
        <h2>Book Holds</h2>

        {{-- Display confirmation messages. --}}
        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        {{-- Display placement or cancellation errors. --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>We could not complete your request:</strong>

                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Display errors supplied by the controller's search validation. --}}
        @if ($searchErrors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following search errors:</strong>

                <ul class="mb-0">
                    @foreach ($searchErrors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Search for members and book titles. --}}
        <section class="card mb-4" aria-labelledby="hold-search-heading">
            <div class="card-body">
                <h3 id="hold-search-heading" class="h5">
                    Find a Member and Book
                </h3>

                <p>
                    Search for a member, a book, or both.
                    Then select the results you want to use.
                </p>

                <form method="GET" action="{{ route('holds.index') }}">
                    <input
                        type="hidden"
                        name="selected_member_id"
                        value="{{ $selectedMember?->member_id }}"
                    >

                    <input
                        type="hidden"
                        name="selected_book_id"
                        value="{{ $selectedBook?->book_id }}"
                    >

                    <div class="mb-3">
                        <label for="member_search" class="form-label">
                            Member Name or ID
                        </label>

                        <input
                            type="search"
                            id="member_search"
                            name="member_search"
                            class="form-control"
                            value="{{ $memberSearch }}"
                            maxlength="150"
                        >
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="book_field" class="form-label">
                                Search Books By
                            </label>

                            <select
                                id="book_field"
                                name="book_field"
                                class="form-select"
                            >
                                <option
                                    value="title"
                                    @selected($bookField === 'title')
                                >
                                    Title
                                </option>

                                <option
                                    value="author"
                                    @selected($bookField === 'author')
                                >
                                    Author
                                </option>

                                <option
                                    value="isbn"
                                    @selected($bookField === 'isbn')
                                >
                                    ISBN
                                </option>

                                <option
                                    value="genre"
                                    @selected($bookField === 'genre')
                                >
                                    Genre
                                </option>

                                <option
                                    value="book_id"
                                    @selected($bookField === 'book_id')
                                >
                                    Book ID
                                </option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label for="book_search" class="form-label">
                                Book Search
                            </label>

                            <input
                                type="search"
                                id="book_search"
                                name="book_search"
                                class="form-control"
                                value="{{ $bookSearch }}"
                                maxlength="150"
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>

                    <a
                        href="{{ route('holds.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Clear Searches and Selections
                    </a>
                </form>
            </div>
        </section>

        {{-- Display member search results. --}}
        @if ($members !== null)
            <section class="mb-4" aria-labelledby="member-results-heading">
                <h3 id="member-results-heading" class="h5">
                    Member Results
                </h3>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Member ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($members as $member)
                                <tr>
                                    <td>{{ $member->member_id }}</td>

                                    <td>
                                        {{ $member->first_name }}
                                        {{ $member->last_name }}
                                    </td>

                                    <td>
                                        @if (
                                            (int) $selectedMember?->member_id ===
                                            (int) $member->member_id
                                        )
                                            <span class="badge bg-success">
                                                Selected
                                            </span>
                                        @else
                                            <a
                                                class="btn btn-outline-primary btn-sm"
                                                href="{{ route('holds.index', array_merge(
                                                    $searchState,
                                                    ['selected_member_id' => $member->member_id]
                                                )) }}"
                                            >
                                                Select Member

                                                <span class="visually-hidden">
                                                    {{ $member->first_name }}
                                                    {{ $member->last_name }}
                                                    (ID: {{ $member->member_id }})
                                                </span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        No matching members found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $members->links('pagination::simple-bootstrap-5') }}
            </section>
        @endif

        {{-- Display book-title search results. --}}
        @if ($books !== null)
            <section class="mb-4" aria-labelledby="book-results-heading">
                <h3 id="book-results-heading" class="h5">
                    Book Results
                </h3>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Book ID</th>
                                <th scope="col">Title</th>
                                <th scope="col">Author</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($books as $book)
                                <tr>
                                    <td>{{ $book->book_id }}</td>
                                    <td>{{ $book->title }}</td>
                                    <td>{{ $book->author }}</td>

                                    <td>
                                        @if (
                                            (int) $selectedBook?->book_id ===
                                            (int) $book->book_id
                                        )
                                            <span class="badge bg-success">
                                                Selected
                                            </span>
                                        @else
                                            <a
                                                class="btn btn-outline-primary btn-sm"
                                                href="{{ route('holds.index', array_merge(
                                                    $searchState,
                                                    ['selected_book_id' => $book->book_id]
                                                )) }}"
                                            >
                                                Select Book

                                                <span class="visually-hidden">
                                                    {{ $book->title }}
                                                    (ID: {{ $book->book_id }})
                                                </span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        No matching books found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $books->links('pagination::simple-bootstrap-5') }}
            </section>
        @endif

        {{-- Review the selections before placing a hold. --}}
        <section class="card mb-4" aria-labelledby="place-hold-heading">
            <div class="card-body">
                <h3 id="place-hold-heading" class="h5">
                    Place a Hold
                </h3>

                <p>
                    A hold applies to the book title rather than a specific
                    physical copy.
                </p>

                <dl>
                    <dt>Selected Member</dt>
                    <dd>
                        @if ($selectedMember)
                            {{ $selectedMember->first_name }}
                            {{ $selectedMember->last_name }}
                            (ID: {{ $selectedMember->member_id }})
                        @else
                            No member selected.
                        @endif
                    </dd>

                    <dt>Selected Book</dt>
                    <dd>
                        @if ($selectedBook)
                            {{ $selectedBook->title }}
                            — {{ $selectedBook->author }}
                            (ID: {{ $selectedBook->book_id }})
                        @else
                            No book selected.
                        @endif
                    </dd>
                </dl>

                @if ($selectedMember && $selectedBook)
                    <form method="POST" action="{{ route('holds.store') }}">
                        @csrf

                        <input
                            type="hidden"
                            name="member_id"
                            value="{{ $selectedMember->member_id }}"
                        >

                        <input
                            type="hidden"
                            name="book_id"
                            value="{{ $selectedBook->book_id }}"
                        >

                        <button type="submit" class="btn btn-primary">
                            Place Hold
                        </button>
                    </form>
                @else
                    <p class="text-muted mb-0">
                        Select a member and a book before placing a hold.
                    </p>
                @endif
            </div>
        </section>

        {{-- Display active holds and previous hold records. --}}
        <section aria-labelledby="hold-history-heading">
            <h3 id="hold-history-heading" class="h5">
                Hold History
            </h3>

            <p>
                Newest holds appear first.
                Only active holds can be removed.
            </p>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <caption>
                        Active, fulfilled, and cancelled book holds.
                    </caption>

                    <thead>
                        <tr>
                            <th scope="col">Hold ID</th>
                            <th scope="col">Member</th>
                            <th scope="col">Book</th>
                            <th scope="col">Placed</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($holds as $hold)
                            <tr>
                                <th scope="row">
                                    {{ $hold->hold_id }}
                                </th>

                                <td>
                                    @if ($hold->member)
                                        {{ $hold->member->first_name }}
                                        {{ $hold->member->last_name }}
                                    @else
                                        Member unavailable
                                    @endif

                                    <div class="small text-muted">
                                        ID: {{ $hold->member_id }}
                                    </div>
                                </td>

                                <td>
                                    {{ $hold->book?->title ?? 'Book unavailable' }}

                                    <div class="small text-muted">
                                        ID: {{ $hold->book_id }}
                                    </div>
                                </td>

                                <td>
                                    {{ $hold->hold_date->format('M j, Y g:i A') }}
                                </td>

                                <td>
                                    {{ ucfirst($hold->status) }}
                                </td>

                                <td>
                                    @if ($hold->status === 'active')
                                        <form
                                            method="POST"
                                            action="{{ route('holds.cancel', [
                                                'hold_id' => $hold->hold_id,
                                            ]) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger btn-sm"
                                                aria-label="Remove hold {{ $hold->hold_id }}"
                                            >
                                                Remove hold
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">
                                            No action available
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    No holds to display.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $holds->links('pagination::simple-bootstrap-5') }}
        </section>
    </div>
@endsection