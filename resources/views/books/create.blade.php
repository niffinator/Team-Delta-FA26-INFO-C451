@extends('layouts.app')

@section('title', 'Add a Book')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-4">Add a Book</h2>

        <p>Enter the information below to add a new book and its first physical copy to the library inventory</p>

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Unable to add book</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('books.store') }}" method="POST">
            @csrf

            {{-- ISBN identifies the book and must be unique --}}
            <div class="mb-3">
                <label for="ISBN" class="form-label">
                    ISBN
                </label>

                <input
                    type="text"
                    class="form-control @error('ISBN') is-invalid @enderror"
                    id="ISBN"
                    name="ISBN"
                    value="{{ old('ISBN') }}"
                    maxlength="13"
                    placeholder="Enter ISBN-10 or ISBN-13"
                    aria-describedby="isbnHelp"
                    required
                >

                <div id="isbnHelp" class="form-text">
                    Enter 10 or 13 characters without spaces or hyphens
                </div>

                @error('ISBN')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Enter the title of the book --}}
            <div class="mb-3">
                <label for="title" class="form-label">
                    Title
                </label>

                <input
                    type="text"
                    class="form-control @error('title') is-invalid @enderror"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    maxlength="255"
                    required
                >

                @error('title')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Enter the author credited for the book --}}
            <div class="mb-3">
                <label for="author" class="form-label">
                    Author
                </label>

                <input
                    type="text"
                    class="form-control @error('author') is-invalid @enderror"
                    id="author"
                    name="author"
                    value="{{ old('author') }}"
                    maxlength="255"
                    required
                >

                @error('author')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Enter the book's genre --}}
            <div class="mb-3">
                <label for="genre" class="form-label">
                    Genre
                </label>

                <input
                    type="text"
                    class="form-control @error('genre') is-invalid @enderror"
                    id="genre"
                    name="genre"
                    value="{{ old('genre') }}"
                    maxlength="255"
                    required
                >

                @error('genre')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Enter the call number for the first physical copy --}}
            <div class="mb-3">
                <label for="call_number" class="form-label">
                    Call Number
                </label>

                <input
                    type="text"
                    class="form-control @error('call_number') is-invalid @enderror"
                    id="call_number"
                    name="call_number"
                    value="{{ old('call_number') }}"
                    maxlength="255"
                    required
                >

                @error('call_number')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Submit the new book or return to the Books page --}}
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    Add Book
                </button>

                <a href="{{ route('books.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>

@endsection
