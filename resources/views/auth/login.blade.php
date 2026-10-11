@extends('layouts.app')

@section('title', 'Sign In')

@section('content')

    <h2>Sign In</h2>

    <p>Sign in with your library staff account.</p>

    
    @if (session('success'))
        <div role="status">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    
    @if ($errors->any())
        <div role="alert">
            <strong>We could not sign you in because:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- the new Login Form --}}
    <form method="POST" action="{{ route('login.store') }}">
        
        @csrf

        <div>
            <label for="username">Username:</label>

            <input
                type="text"
                id="username"
                name="username"
                value="{{ old('username') }}"
                maxlength="255"
                autocomplete="username"
                required
                autofocus
            >
        </div>

        <br>

        <div>
            <label for="password">Password:</label>

            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >
        </div>

        <br>

        <button type="submit">Sign In</button>
    </form>

@endsection