@extends('layouts.app')

@section('title', 'Home')

@section('content')

    <h2>Home</h2>

    <p>Welcome to Book WRMS</p>
    <pre>
        Web-based
            Records
                Management
                    System
    </pre>

    <h3>Library Operations</h3>

    <p>
        This is a system built to operate within a small library operation. We currently support:
    </p>
            <ul>Extensive catalog search via the Books page</ul>
            <ul>A Members search page with no content</ul>
            <ul>A functioning and error-handling Check Out page</ul>
            <ul>A functioning and error-handling Holds page</ul>
            <ul>A Reports page with no content</ul>

@endsection