<?php

namespace App\Services;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Hold;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{


    public function checkout(
        User $user,
        int $memberId,
        int $copyId
    ): Transaction {
        try {
            return DB::transaction(function () use ($user, $memberId, $copyId) {
                $member = Member::find($memberId);

                // Check if the member exists
                if (!$member) {
                    throw ValidationException::withMessages([
                        'member_id' => 'That member does not exist.'
                    ]);
                }

                $requestedCopy = BookCopy::find($copyId);

                // Check if the book copy exists
                if (!$requestedCopy) {
                    throw ValidationException::withMessages([
                        'copy_id' => 'We do not have a copy of that book in our library.'
                    ]);
                }

                Book::where('book_id', $requestedCopy->book_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check if the book copy is available
                $bookCopy = BookCopy::where('copy_id', $copyId)
                    ->where('book_id', $requestedCopy->book_id)
                    ->lockForUpdate()
                    ->first();

                if (!$bookCopy) {
                    throw ValidationException::withMessages([
                        'copy_id' => 'The information for that book copy has changed. Please try again.'
                    ]);
                }

                if ($bookCopy->status !== 'available') {
                    throw ValidationException::withMessages([
                        'copy_id' => 'That book copy is not available for checkout.'
                    ]);
                }

                // Count and lock the available physical book copies for this book
                $availableCopies = BookCopy::where('book_id', $bookCopy->book_id)
                ->where('status', 'available')
                ->orderBy('copy_id')
                ->lockForUpdate()
                ->pluck('copy_id')
                ->count();

                // Get the active holds in queue order
                $activeHolds = Hold::where('book_id', $bookCopy->book_id)
                    ->where('status', 'active')
                    ->orderBy('hold_date')
                    ->orderBy('hold_id')
                    ->lockForUpdate()
                    ->get();

                $holdCount = $activeHolds->count();
                $memberPosition = 0;
                $memberHold = null;

                foreach ($activeHolds as $index => $hold) {
                    if ((int) $hold->member_id === (int) $member->member_id) {
                        $memberPosition = $index + 1;
                        $memberHold = $hold;
                        break;
                    }
                }

                // A member with a hold needs enough copies to cover their position in the queue
                // Someone without a hold needs there to be enough copies available beyond the number of active holds
                $blockedByHolds = $memberHold
                    ? $availableCopies < $memberPosition
                    : $availableCopies <= $holdCount;

                if($blockedByHolds) {
                    throw ValidationException::withMessages([
                        'copy_id' => 'There are not enough available copies to check this book out. ' .
                        'We must honor our active holds.'
                    ]);
                }

                // Create a new transaction for the checkout
                $checkoutDate = now();

                $transaction = new Transaction;

                $transaction->user_id = $user->user_id;
                $transaction->copy_id = $bookCopy->copy_id;
                $transaction->member_id = $member->member_id;
                $transaction->checkout_date = $checkoutDate;
                $transaction->due_date = $checkoutDate->copy()->addDays(14);
                $transaction->return_date = null;
                $transaction->late_fee = 0;
                $transaction->save();

                // Update the book copy status (physical book)
                $bookCopy->status = 'checked out';
                $bookCopy->save();

                if ($memberHold) {
                    $memberHold->status = 'fulfilled';
                    $memberHold->save();        
                }

                return $transaction;
            }, 3);
            
        // Handle unique constraint violation for open transactions
        } catch (UniqueConstraintViolationException $exception) {
            if (!str_contains(
                $exception->getMessage(),
                'transactions_one_open_per_copy'
            )) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'copy_id' => 'That book is already checked out and cannot be checked out again right now.'
            ]);
        }   
    }
}