<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Book WRMS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    
    <div class="container" role="heading" text-align="center">

        
        <header>
            <h1>Book WRMS: Web-Based Records Management System</h1>

            {{-- Display authentication status and navigation links --}}
            @auth('web')
                <p>
                    Signed in as
                    <strong>{{ Auth::user()->username }}</strong>
                    ({{{ auth('web')->user()->role }}})
                </p>
                
                {{-- Display navigation links based on user permissions --}}
                @can('access-library')
                    <nav aria-label="Main Navigation" role="navigation" class="nav" id="main-navigation">
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

    </div>

</body>

</html>
