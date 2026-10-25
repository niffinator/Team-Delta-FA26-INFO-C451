<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Hold;
use App\Models\Member;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HoldService
{
    public function placeHold(int $memberId, int $bookId): Hold
    {
        try {
            return DB::transaction(function () use ($memberId, $bookId): Hold {
                // Use the same book lock as CheckoutService
                $book = Book::where('book_id', $bookId)
                    ->lockForUpdate()
                    ->first();

                // Book doesn't exist, let them know
                if (! $book) {
                    throw ValidationException::withMessages([
                        'book_id' => 'That book does not exist.',
                    ]);
                }

                $member = Member::where('member_id', $memberId)
                    ->sharedLock()
                    ->first();

                // Member doesn't exit, let them know
                if (! $member) {
                    throw ValidationException::withMessages([
                        'member_id' => 'That member does not exist.',
                    ]);
                }

                // Members cannot place the same book on more than one active hold
                // Find if the member already has a book on hold
                $alreadyHeld = Hold::where('member_id', $memberId)
                    ->where('book_id', $bookId)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();
                // And if they do, let them know about it
                if ($alreadyHeld) {
                    throw ValidationException::withMessages([
                        'book_id' => 'This member already has an active hold for this book.',
                    ]);
                }

                // Create the new Hold
                $hold = new Hold;

                $hold->member_id = $memberId;
                $hold->book_id = $bookId;
                $hold->hold_date = now();
                $hold->status = 'active';

                $hold->save();

                return $hold;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains(
                $exception->getMessage(),
                'holds_one_active_book_per_member_book'
            )) {
                throw $exception;
            }
            throw ValidationException::withMessages([
                'book_id' => 'This member already has an active hold for this book.',
            ]);
        }
    }

    public function cancelHold(int $holdId): Hold
    {
        return DB::transaction(function () use ($holdId): Hold {
            $requestedHold = Hold::findOrFail($holdId);

            // Coordinate cancellation with checkout before locking the hold
            Book::where('book_id', $requestedHold->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check current status after obtaining the book lock
            $hold = Hold::where('hold_id', $holdId)
                ->where('book_id', $requestedHold->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($hold->status !== 'active') {
                throw ValidationException::withMessages([
                    'hold_id' => 'Only an active hold can be cancelled.',
                ]);
            }

            $hold->status = 'cancelled';
            $hold->save();

            return $hold;
        }, 3);
    }
}