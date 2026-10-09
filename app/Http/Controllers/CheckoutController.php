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
        // test user due to no authentication yet
        $user = User::where('username', 'big_bertha')->firstOrFail();

        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'copy_id' => ['required', 'integer'],
        ]);

        $member = Member::find($validated['member_id']);

        if (!$member) {
            return back()
                ->withErrors([
                    'member_id' => 'That person is not a member of our library.'
                ])
                ->withInput();
        }

        $bookCopy = BookCopy::find($validated['copy_id']);

        if(!$bookCopy) {
            return back()
                ->withErrors([
                    'copy_id' => 'We do not have a copy of that book in our collection.'
                ])
                ->withInput();
        }

        if ($bookCopy->status !== 'available') {
            return back()
                ->withErrors([
                    'copy_id' => 'That copy is currently checked out.'
                ])
                ->withInput();
        }

        $activeHold = Hold::where('book_id', $bookCopy->book_id)
    ->where('status', 'active')
    ->orderBy('hold_date')
    ->first();

if ($activeHold && (int) $activeHold->member_id !== (int) $member->member_id) {
    return back()
        ->withErrors([
            'copy_id' => 'This book is reserved for another member.'
        ])
        ->withInput();
}

        $transaction = new Transaction;

        $transaction->user_id = $user->user_id;
        $transaction->copy_id = $bookCopy->copy_id;
        $transaction->member_id = $member->member_id;
        $transaction->checkout_date = now();
        $transaction->due_date = now()->addDays(14);
        $transaction->return_date = null;
        $transaction->late_fee = 0;
        $transaction->save();

        $bookCopy-> status = 'checked out';
        $bookCopy->save();

        return redirect()->route('checkout.index')
            ->with('success', 'The book has been successfully checked out.');
    }
}
