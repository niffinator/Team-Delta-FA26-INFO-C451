@extends('layouts.app')

@section('title', 'Add a Member')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-4">Add a Member</h2>

        <p>Enter the information below to register a new library member</p>

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Unable to add member</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('members.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="first_name" class="form-label">
                    First Name
                </label>

                <input
                    type="text"
                    class="form-control @error('first_name') is-invalid @enderror"
                    id="first_name"
                    name="first_name"
                    value="{{ old('first_name') }}"
                    maxlength="255"
                    required
                >

                @error('first_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">
                    Last Name
                </label>

                <input
                    type="text"
                    class="form-control @error('last_name') is-invalid @enderror"
                    id="last_name"
                    name="last_name"
                    value="{{ old('last_name') }}"
                    maxlength="255"
                    required
                >

                @error('last_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">
                    Email Address
                </label>

                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    maxlength="255"
                    required
                >

                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">
                    Phone Number
                </label>

                <input
                    type="tel"
                    class="form-control @error('phone') is-invalid @enderror"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    maxlength="255"
                    required
                >

                @error('phone')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    Add Member
                </button>

                <a href="{{ route('members.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>

@endsection
