<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('guests cannot access library operations', function () {
    foreach (['/books', '/checkout', '/holds'] as $path) {
        $this->get($path)->assertRedirect(route('login'));
    }
});

test('only librarians can manage book and member records', function () {
    $clerk = new User();
    $clerk->role = 'Clerk';

    $librarian = new User();
    $librarian->role = 'Librarian';

    $this->assertFalse(Gate::forUser($clerk)->allows('manage-books'));
    $this->assertFalse(Gate::forUser($clerk)->allows('manage-members'));

    $this->assertTrue(Gate::forUser($librarian)->allows('manage-books'));
    $this->assertTrue(Gate::forUser($librarian)->allows('manage-members'));
});