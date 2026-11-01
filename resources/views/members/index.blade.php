@extends('layouts.app')

@section('title', 'Members')

@section('content')
    <h2>Member Information</h2>

    <form action="{{ route('members.index') }}" method="GET">
        <label for="search">Search members</label>

        <input
            type="text"
            id="search"
            name="search"
            value="{{ $search }}"
            placeholder="Name, email, or phone"
        >

        <button type="submit">Search</button>

        @if ($search !== '')
            <a href="{{ route('members.index') }}">Clear search</a>
        @endif
    </form>

    <p>
        @if ($search !== '')
            Search results for "{{ $search }}"
        @else
            All members
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Member ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($members as $member)
                <tr>
                    <td>{{ $member->member_id }}</td>
                    <td>{{ $member->first_name }}</td>
                    <td>{{ $member->last_name }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->phone }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No members found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        {{ $members->links() }}
    </div>
@endsection
