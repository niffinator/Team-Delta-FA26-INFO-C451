<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;

class MemberSearchService
{
    public function searchMembers(
        string $memberSearch,

    ): Builder { 

        $memberSearch = trim($memberSearch);

        $memberQuery = Member::query();

        // Search members by an exact Member ID, if the search is numeric
        if ($memberSearch !== '') {
                if (ctype_digit($memberSearch)) {
                    $memberQuery->where('member_id', $memberSearch);
                // Else conduct search by first and last name; initially removes whitespace characters with preg split
                } else {
                    // \s+ separates a full name that may have multiple whitespace characters
                    $nameParts = preg_split('/\s+/', $memberSearch);

                    foreach ($nameParts as $part) {
                        $memberQuery->where(function ($query) use ($part) {
                            $query->where('first_name', 'like', '%' . $part . '%')
                                ->orWhere('last_name', 'like', '%' . $part . '%');
                        });
                    }
                }
            }

        $memberQuery
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('member_id');
        
        return $memberQuery;
    }


}
