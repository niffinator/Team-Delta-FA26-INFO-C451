<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class MemberService
{
    // Create a new member using validated information
    public function createMember(array $memberData): Member
    {
        try {
            return Member::create($memberData);
        } catch (QueryException $exception) {
            $errorCode = $exception->errorInfo[1] ?? null;

            if (
                (int) $errorCode === 1062 &&
                str_contains($exception->getMessage(), 'members_email_unique')
            ) {
                throw ValidationException::withMessages([
                    'email' => 'The email has already been taken.',
                ]);
            }

            throw $exception;
        }
    }
}