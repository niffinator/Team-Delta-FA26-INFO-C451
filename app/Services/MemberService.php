<?php

namespace App\Services;

use App\Models\Member;

class MemberService
{
    // Create a new member using validated information
    public function createMember(array $memberData): Member
    {
        return Member::create($memberData);
    }
}