<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        // Temporary test user until authentication is implemented.
        $user = User::where('username', 'big_bertha')->firstOrFail();

        // Validate the form.
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'copy_id' => ['required', 'integer'],
        ]);

        // Find the member.
        $member = Member::find($validated['member_id']);

        if (!$member) {
            return back()
                ->withErrors([
                    'member_id' => 'That person is not a member of our library.'
                ])
                ->withInput();
        }

        // Find the physical book copy.
        $bookCopy = BookCopy::find($validated['copy_id']);

        if (!$bookCopy) {
            return back()
                ->withErrors([
                    'copy_id' => 'We do not have a copy of that book in our collection.'
                ])
                ->withInput();
        }

        // Process checkout and related changes together.
        $result = DB::transaction(function () use ($bookCopy, $member, $user) {

            // Lock the copy while checking and updating its status.
            $copy = BookCopy::where('copy_id', $bookCopy->copy_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($copy->status !== 'available') {
                return 'unavailable';
            }

            // Find the earliest active hold for this book.
            $activeHold = Hold::where('book_id', $copy->book_id)
                ->where('status', 'active')
                ->orderBy('hold_date')
                ->orderBy('hold_id')
                ->lockForUpdate()
                ->first();

            // Reject checkout if the earliest hold belongs to someone else.
            if (
                $activeHold &&
                (int) $activeHold->member_id !== (int) $member->member_id
            ) {
                return 'held';
            }

            // Create the checkout transaction.
            $transaction = new Transaction();

            $transaction->user_id = $user->user_id;
            $transaction->copy_id = $copy->copy_id;
            $transaction->member_id = $member->member_id;
            $transaction->checkout_date = now();
            $transaction->due_date = now()->addDays(14);
            $transaction->return_date = null;
            $transaction->late_fee = 0;
            $transaction->save();

            // Update the copy's status.
            $copy->status = 'checked out';
            $copy->save();

            // Fulfill the hold if this member owns the earliest active hold.
            if ($activeHold) {
                $activeHold->status = 'fulfilled';
                $activeHold->save();
            }

            return 'success';
        });

        // Show the appropriate result.
        if ($result === 'unavailable') {
            return back()
                ->withErrors([
                    'copy_id' => 'That copy is currently checked out.'
                ])
                ->withInput();
        }

        if ($result === 'held') {
            return back()
                ->withErrors([
                    'copy_id' => 'This book is reserved for another member.'
                ])
                ->withInput();
        }

        return redirect()
            ->route('checkout.index')
            ->with('success', 'The book has been successfully checked out.');
    }
}
