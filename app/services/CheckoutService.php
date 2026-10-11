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

                if (!$member) {
                    throw ValidationException::withMessages([
                        'member_id' => 'That member does not exist.'
                    ]);
                }

                $requestedCopy = BookCopy::find($copyId);

                if (!$requestedCopy) {
                    throw ValidationException::withMessages([
                        'copy_id' => 'We do not have a copy of that book in our library.'
                    ]);
                }

                Book::where('book_id', $requestedCopy->book_id)
                    ->lockForUpdate()
                    ->firstOrFail();

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

                $firstHold = Hold::where('book_id', $bookCopy->book_id)
                    ->where('status', 'active')
                    ->orderBy('hold_date')
                    ->orderBy('hold_id')
                    ->lockForUpdate()
                    ->first();

                if (
                    $firstHold &&
                    (int) $firstHold->member_id !== (int) $member->member_id
                ) {
                    throw ValidationException::withMessages([
                        'copy_id' => 'Another member has reserved this book and is ahead in the queue.'
                    ]);
                }


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

                $bookCopy->status = 'checked out';
                $bookCopy->save();

                if ($firstHold) {
                    $firstHold->status = 'fulfilled';
                    $firstHold->save();
                }

                return $transaction;
            }, 3);
            
        } catch (UniqueConstraintViolationException $exception) {
            if (!str_contains(
                $exception->getMessage(),
                'transactions_one_open_per_copy'
            )) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'copy_id' => 'That book is already checked out and cannot be checked out again at this time.'
            ]);
        }   
    }
}