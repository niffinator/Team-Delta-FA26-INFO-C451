
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display the members page and handle member search.
     */
    public function index(Request $request)
    {
        // Member search will be implemented after we confirm
        // the existing Member model and database column names.

        return view('members.index');
    }
}
