<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    // Preserve support for returning one physical book copy
    public function returnBook(int $copyId): Transaction
    {
        // Identify the authenticated employee processing the return
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        // Locate the active checkout for the physical copy
        $transaction = Transaction::where('copy_id', $copyId)
            ->whereNull('return_date')
            ->first();

        if (! $transaction) {
            throw ValidationException::withMessages([
                'copy_id' => 'That book copy is not currently checked out.',
            ]);
        }

        // Use the same return rules as the multiple-book workflow
        $returnedTransactions = $this->returnBooks(
            $user,
            (int) $transaction->member_id,
            [(int) $transaction->transaction_id]
        );

        return $returnedTransactions->first();
    }

    // Process one or multiple returns for the selected member
    public function returnBooks(
        User $user,
        int $memberId,
        array $transactionIds
    ): Collection {

        // Require at least one transaction to be selected
        if (empty($transactionIds)) {
            throw ValidationException::withMessages([
                'transaction_ids' => 'Select at least one book to return.',
            ]);
        }

        // Validate the selected transaction identifiers
        foreach ($transactionIds as $transactionId) {
            if (
                ! is_int($transactionId) ||
                $transactionId < 1
            ) {
                throw ValidationException::withMessages([
                    'transaction_ids' => 'An invalid return selection was submitted.',
                ]);
            }
        }

        // Prevent duplicate transaction selections
        $transactionIds = array_values(
            array_unique($transactionIds)
        );

        sort($transactionIds, SORT_NUMERIC);

        return DB::transaction(function () use (
            $user,
            $memberId,
            $transactionIds
        ): Collection {

            // Confirm the selected member exists
            $member = Member::find($memberId);

            if (! $member) {
                throw ValidationException::withMessages([
                    'member_id' => 'That member does not exist.',
                ]);
            }

            // Retrieve the checkout records selected by the employee
            $requestedTransactions = Transaction::whereIn(
                'transaction_id',
                $transactionIds
            )->get();

            // Every selected transaction must exist
            if ($requestedTransactions->count() !== count($transactionIds)) {
                throw ValidationException::withMessages([
                    'transaction_ids' => 'One or more selected transactions could not be found.',
                ]);
            }

            // Confirm that all selected transactions belong to this member
            foreach ($requestedTransactions as $requestedTransaction) {
                if ((int) $requestedTransaction->member_id !== $memberId) {
                    throw ValidationException::withMessages([
                        'transaction_ids' => 'One or more selected books do not belong to this member.',
                    ]);
                }
            }

            // Retrieve the physical copies associated with the transactions
            $copyIds = $requestedTransactions
                ->pluck('copy_id')
                ->unique()
                ->sort()
                ->values()
                ->all();

            $requestedCopies = BookCopy::whereIn(
                'copy_id',
                $copyIds
            )->get();

            if ($requestedCopies->count() !== count($copyIds)) {
                throw ValidationException::withMessages([
                    'transaction_ids' => 'One or more selected book copies could not be found.',
                ]);
            }

            // Lock the parent books in a consistent order
            // This coordinates returns with checkout and hold operations
            $bookIds = $requestedCopies
                ->pluck('book_id')
                ->unique()
                ->sort()
                ->values()
                ->all();

            $lockedBooks = Book::whereIn('book_id', $bookIds)
                ->orderBy('book_id')
                ->lockForUpdate()
                ->get();

            if ($lockedBooks->count() !== count($bookIds)) {
                throw ValidationException::withMessages([
                    'transaction_ids' => 'One or more selected books could not be found.',
                ]);
            }

            // Lock all physical copies before updating availability
            $lockedCopies = BookCopy::whereIn('copy_id', $copyIds)
                ->orderBy('copy_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('copy_id');

            if ($lockedCopies->count() !== count($copyIds)) {
                throw ValidationException::withMessages([
                    'transaction_ids' => 'The selected book information has changed. Please try again.',
                ]);
            }

            // Lock the checkout transactions before modifying them
            $transactions = Transaction::whereIn(
                'transaction_id',
                $transactionIds
            )
                ->orderBy('transaction_id')
                ->lockForUpdate()
                ->get();

            if ($transactions->count() !== count($transactionIds)) {
                throw ValidationException::withMessages([
                    'transaction_ids' => 'The selected transactions have changed. Please try again.',
                ]);
            }

            // Validate every selected return before changing any records
            foreach ($transactions as $transaction) {

                // Confirm the transaction still belongs to the selected member
                if ((int) $transaction->member_id !== $memberId) {
                    throw ValidationException::withMessages([
                        'transaction_ids' => 'A selected transaction no longer belongs to this member.',
                    ]);
                }

                // Prevent a transaction from being returned more than once
                if ($transaction->return_date !== null) {
                    throw ValidationException::withMessages([
                        'transaction_ids' => 'One or more selected books have already been returned.',
                    ]);
                }

                $bookCopy = $lockedCopies->get($transaction->copy_id);

                // Confirm the associated physical copy is checked out
                if (
                    ! $bookCopy ||
                    $bookCopy->status !== 'checked out'
                ) {
                    throw ValidationException::withMessages([
                        'transaction_ids' => 'One or more selected book copies are not currently checked out.',
                    ]);
                }
            }

            // Use one return timestamp for the entire batch
            $returnDate = now();

            // Update each transaction and its physical book copy
            foreach ($transactions as $transaction) {

                $bookCopy = $lockedCopies->get($transaction->copy_id);

                // Compare calendar dates to determine overdue days
                $dueDate = $transaction->due_date
                    ->copy()
                    ->startOfDay();

                $returnedOn = $returnDate
                    ->copy()
                    ->startOfDay();

                $overdueDays = max(
                    0,
                    (int) $dueDate->diffInDays($returnedOn, false)
                );

                // Calculate late fees at five cents per overdue day
                $lateFeeCents = $overdueDays * 5;

                $lateFee = number_format(
                    $lateFeeCents / 100,
                    2,
                    '.',
                    ''
                );

                // Close the existing checkout transaction
                $transaction->return_date = $returnDate;
                $transaction->returned_by_user_id = $user->user_id;
                $transaction->late_fee = $lateFee;
                $transaction->save();

                // Restore physical copy availability
                $bookCopy->status = 'available';
                $bookCopy->save();

                // Existing active holds remain in place
                // CheckoutService will enforce hold priority on future checkouts
            }

            // Return the updated transactions for confirmation
            return $transactions;

        }, 3);
    }
}
