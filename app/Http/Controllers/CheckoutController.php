<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $checkoutTransaction = null;

        $user = $request->user();

        $transactionId = $request->session()->get('checkout_transaction_id');

        if ($user && $transactionId) {
            $checkoutTransaction = Transaction::with([
                'member',
                'bookCopy.book',
                'user',
            ])
                ->where('user_id', $user->user_id)
                ->find($transactionId);
        }

        return view('checkout.index', [
            'checkoutTransaction' => $checkoutTransaction,
        ]);
    }

    public function store(
        Request $request,
        CheckoutService $checkoutService
    ) {
        $user = $request->user();

        abort_unless(
            $user,
            403,
            'You must be signed in to check out a book.'
        );

        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'min:1'],
            'copy_id' => ['required', 'integer', 'min:1'],
        ]);

        $transaction = $checkoutService->checkout(
            $user,
            (int) $validated['member_id'],
            (int) $validated['copy_id']
        );

        return redirect()
            ->route('checkout.index')
            ->with('success', 'The book has been successfully checked out.')
            ->with('checkout_transaction_id', $transaction->transaction_id);
    }
}