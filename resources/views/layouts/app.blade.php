<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Book WRMS')</title>
</head>

<body>

    <header>
        <h1>Book WRMS</h1>

        <nav>
            <a href="{{ url('/') }}">Home</a> |
            <a href="{{ route('books.index') }}">Books</a> |
            <a href="{{ route('members.index')}}">Members</a> |
            <a href="{{ route('checkout.index') }}">Check Out</a> |
            <a href="{{ route('reports.index') }}">Reports</a>
        </nav>

        <hr>
    </header>

    <main>
        @yield('content')
    </main>

</body>
</html>
