@extends('layouts.app')

@section('title', 'Checkout Book')

@section('content')

    <h2>Check Out a Book</h2>

    @if (session('success'))
        <div role="status">
            <p>
                <strong>{{ session('success') }}</strong>
            </p>

            @if ($checkoutTransaction)
                <h3>Completed Checkout</h3>

                <dl>
                    <dt>Transaction ID</dt>
                    <dd>{{ $checkoutTransaction->transaction_id }}</dd>

                    <dt>Member</dt>
                    <dd>
                        {{ $checkoutTransaction->member->first_name }}
                        {{ $checkoutTransaction->member->last_name }}
                        (ID: {{ $checkoutTransaction->member_id }})
                    </dd>

                    <dt>Book</dt>
                    <dd>
                        {{ $checkoutTransaction->bookCopy->book->title }}
                    </dd>

                    <dt>Book Copy ID</dt>
                    <dd>{{ $checkoutTransaction->copy_id }}</dd>

                    <dt>Processed By</dt>
                    <dd>{{ $checkoutTransaction->user->username }}</dd>

                    <dt>Checkout Date</dt>
                    <dd>
                        {{ $checkoutTransaction->checkout_date->format('F j, Y') }}
                    </dd>

                    <dt>Due Date</dt>
                    <dd>
                        {{ $checkoutTransaction->due_date->format('F j, Y') }}
                    </dd>
                </dl>
            @endif
        </div>
    @endif

    @if ($errors->any())
        <div role="alert">
            <strong>We could not complete your checkout:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p>
        Enter the member and the copy of the book being checked out below.
        Checkout and due dates are assigned when checkout is completed.
    </p>

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf

        <h3>Member Information</h3>

        <div>
            <label for="member_id">Member ID:</label>
            <input
                type="number"
                id="member_id"
                name="member_id"
                value="{{ old('member_id') }}"
                min="1"
                step="1"
                required
            >
        </div>

        <h3>Book Information</h3>

        <div>
            <label for="copy_id">Book Copy ID:</label>
            <input
                type="number"
                id="copy_id"
                name="copy_id"
                value="{{ old('copy_id') }}"
                min="1"
                step="1"
                required
            >
        </div>

        <br>

        <button type="submit">Check Out Book</button>
    </form>

@endsection