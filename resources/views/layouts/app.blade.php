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

        @auth('web')
            <p>
                Signed in as
                <strong>{{ Auth::user()->username }}</strong>
                ({{{ auth('web')->user()->role }}})
            </p>
            
            @can('access-library')
                <nav>
                    <a href="{{ url('/') }}">Home</a> |
                    <a href="{{ route('books.index') }}">Books</a> |
                    <a href="{{ route('members.index')}}">Members</a>

                    @can('process-circulation')
                       | <a href="{{ route('checkout.index') }}">Check Out</a> |
                    @endcan

                    @can('run-reports')
                        | <a href="{{ route('reports.index') }}">Reports</a>
                    @endcan
                </nav>
            @endcan

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit">Sign Out</button>
            </form>
        @endauth
        
        @guest('web')
            <nav aria-label="Main Navigation">
                <a href="{{ route('login') }}">Sign In</a>
            </nav>
        @endguest

        <hr>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
