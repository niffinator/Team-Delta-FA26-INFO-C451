<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\BookCopy;
use App\Models\Hold;
use App\Models\Transaction;
use App\Models\User;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function store(Request $request)
    {
        // Test user because authentication is not implemented yet.
        $user = User::where('username', 'big_bertha')->firstOrFail();

        // Validate the submitted form.
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'copy_id' => ['required', 'integer'],
        ]);

        // Find the library member.
        $member = Member::find($validated['member_id']);

        if (!$member) {
            return back()
                ->withErrors([
                    'member_id' => 'That person is not a member of our library.'
                ])
                ->withInput();
        }

        // Find the requested book copy.
        $bookCopy = BookCopy::find($validated['copy_id']);

        if (!$bookCopy) {
            return back()
                ->withErrors([
                    'copy_id' => 'We do not have a copy of that book in our collection.'
                ])
                ->withInput();
        }

        // Make sure the copy is available.
        if ($bookCopy->status !== 'available') {
            return back()
                ->withErrors([
                    'copy_id' => 'That copy is currently checked out.'
                ])
                ->withInput();
        }

        // Find the earliest active hold for this book.
        $activeHold = Hold::where('book_id', $bookCopy->book_id)
            ->where('status', 'active')
            ->orderBy('hold_date')
            ->orderBy('hold_id')
            ->first();

        // Do not allow another member to check out a held book.
        if (
            $activeHold &&
            (int) $activeHold->member_id !== (int) $member->member_id
        ) {
            return back()
                ->withErrors([
                    'copy_id' => 'This book is reserved for another member.'
                ])
                ->withInput();
        }

        // Create the checkout transaction only after validation passes.
        $transaction = new Transaction();

        $transaction->user_id = $user->user_id;
        $transaction->copy_id = $bookCopy->copy_id;
        $transaction->member_id = $member->member_id;
        $transaction->checkout_date = now();
        $transaction->due_date = now()->addDays(14);
        $transaction->return_date = null;
        $transaction->late_fee = 0;

        $transaction->save();

        // Update the physical copy's status.
        $bookCopy->status = 'checked out';
        $bookCopy->save();

        // Show the success message.
        return redirect()
            ->route('checkout.index')
            ->with('success', 'The book has been successfully checked out.');
    }
}
