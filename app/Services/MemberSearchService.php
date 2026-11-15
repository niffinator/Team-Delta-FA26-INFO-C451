<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;

class MemberSearchService
{
    public function searchMembers(
        string $memberSearch
    ): Builder {

        // Remove leading and trailing whitespace from the search term
        $memberSearch = trim($memberSearch);

        $memberQuery = Member::query();

        // Apply search conditions only when a search term exists
        if ($memberSearch !== '') {

            // Separate search terms for matching first and last names
            $nameParts = preg_split('/\s+/', $memberSearch);

            // Group search conditions to prevent unrelated query results
            $memberQuery->where(function ($query) use (
                $memberSearch,
                $nameParts
            ) {

                // Search by exact Member ID when the input is numeric
                if (ctype_digit($memberSearch)) {
                    $query->where('member_id', $memberSearch);
                }

                // Search by email address or phone number
                $query->orWhere(
                    'email',
                    'like',
                    '%' . $memberSearch . '%'
                );

                $query->orWhere(
                    'phone',
                    'like',
                    '%' . $memberSearch . '%'
                );

                // Search by first and last name components
                $query->orWhere(function ($nameQuery) use ($nameParts) {

                    foreach ($nameParts as $part) {
                        $nameQuery->where(function ($partQuery) use ($part) {
                            $partQuery
                                ->where(
                                    'first_name',
                                    'like',
                                    '%' . $part . '%'
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    '%' . $part . '%'
                                );
                        });
                    }

                });
            });
        }

        // Maintain consistent alphabetical ordering
        $memberQuery
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('member_id');

        // Return the query builder for pagination or further filtering
        return $memberQuery;
    }
}
