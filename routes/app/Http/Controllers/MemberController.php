<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $members = Member::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return view('members.index', compact('members', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('members', 'email'),
            ],
            'phone' => ['required', 'string', 'max:255'],
        ]);

        Member::create($validated);

        return redirect()
            ->route('members.index')
            ->with('success', 'Member added successfully.');
    }

    public function destroy(Member $member)
    {
        if (
            $member->transactions()->exists()
            || $member->holds()->exists()
        ) {
            return redirect()
                ->route('members.index')
                ->with(
                    'error',
                    'This member cannot be removed because they have transaction or hold records.'
                );
        }

        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success', 'Member removed successfully.');
    }
}
