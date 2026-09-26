@extends('layouts.app')

@section('title', 'Checkout Book')

@section('content')

    <h2>Check Out a Book</h2>

    <p>Enter the member and the copy of the book being checked out below.</p>

    <form>

        <h3>Member Information</h3>

        <div>
            <label for="member_id">Member ID:</label>
            <input type="number" id="member_id" name="member_id" required>
        </div>

        <br>

        <h3>Book Information</h3>
        
            <div>
                <label for="copy_id">Book Copy: ID</label>
                <input type="number" id="copy_id" name="copy_id" required>
            </div> 


        <br>

        <h3> Checkout Information</h3>

        <div>
            <label for="checkout_date">Checkout Date:</label>
            <input type="date" id="checkout_date" name="checkout_date" value="{{ date('Y-m-d') }} readonly">
        </div>

        <br>

        <div>
            <label for="due_date">Return Date:</label>
            <input type="date" id="due_date" name="due_date" value="{{date ('Y-m-d', strtotime('+14 days')) }}" readonly>
        </div>

        <br>

        <button type="button">
            Checkout Book
        </button>

    </form>

@endsection
                
