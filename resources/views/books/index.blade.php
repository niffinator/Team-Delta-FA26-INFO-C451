
@extends('layouts.app')

@section('title', 'Books')

@section('content')

    <h2> Book Information </h2>

    <p>Here you can browse our book collection to assist an inquisitive member.</p>

    {{-- Display any validation errors from BookController --}}
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

    <h3>Find a Book Copy</h3>

    <form method="GET" action="{{ route('books.index') }}">

        <input type="hidden" name="book_search_action" value="1">

        {{-- Select the field to search by --}}
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

        {{-- Search input for the selected book field --}}
        <div>
            <label for="book_search">Book Search:</label>

            <input
                type="search"
                id="book_search"
                name="book_search"
                value="{{ $bookSearch }}"
                maxlength="150"
                placeholder="Enter search here..."
                required
            >
        </div>

        <div>
            <label for="copy_status">Copy Status:</label>

            <select id="copy_status" name="copy_status">
                <option value="" @selected($copyStatus === '')>Any Status</option>

                <option value="available" @selected($copyStatus === 'available')>Available</option>

                {{-- The controller converts checked_out to the database value 'checked out' --}}
                <option value="checked_out" @selected($copyStatus === 'checked_out')>Checked Out</option>
            </select>
        </div>

        <button type="submit">Search for Books</button>

    </form>

    {{-- Allow the user to clear their current search --}}
    <p>
        <a href="{{ route('books.index') }}">Clear Search</a>
    </p>

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
                    </tr>
                </thead>

                {{-- Display each matching physical copy separately --}}
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
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @endif

        {{-- Pagination & displays 10 book copies per page --}}
        @if ($bookCopies->previousPageUrl() || $bookCopies->hasMorePages())

            <nav aria-label="Book copy result pages">

                @if ($bookCopies->previousPageUrl())
                    <a href="{{ $bookCopies->previousPageUrl() }}">
                        Previous Copies
                    </a>
                @endif

                <span>Page {{ $bookCopies->currentPage() }}</span>

                @if ($bookCopies->hasMorePages())
                    <a href="{{ $bookCopies->nextPageUrl() }}">
                        Next Copies
                    </a>
                @endif

            </nav>

        @endif

    @endif

@endsection
