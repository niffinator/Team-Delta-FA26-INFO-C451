<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use App\Services\BookService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

// Roll back database changes after each test
uses(DatabaseTransactions::class);

// Create a librarian account for authorization tests
function createBookTestLibrarian(): User
{
    return User::query()->forceCreate([
        'first_name' => 'Test',
        'last_name' => 'Librarian',
        'username' => 'book_test_librarian',
        'password' => 'test-password',
        'role' => 'Librarian',
    ]);
}

// Create a circulation clerk account for authorization tests
function createBookTestClerk(): User
{
    return User::query()->forceCreate([
        'first_name' => 'Test',
        'last_name' => 'Clerk',
        'username' => 'book_test_clerk',
        'password' => 'test-password',
        'role' => 'Clerk',
    ]);
}

// Provide valid information for a new book
function validBookTestData(): array
{
    return [
        'ISBN' => '9780132350884',
        'title' => 'Clean Code',
        'author' => 'Robert C. Martin',
        'genre' => 'Programming',
        'call_number' => '005.1 MAR',
    ];
}

test('a librarian can open the add book form', function () {
    $librarian = createBookTestLibrarian();

    $this->actingAs($librarian, 'web')
        ->get(route('books.create'))
        ->assertOk()
        ->assertViewIs('books.create');
});

test('a circulation clerk cannot open the add book form', function () {
    $clerk = createBookTestClerk();

    $this->actingAs($clerk, 'web')
        ->get(route('books.create'))
        ->assertForbidden();
});

test('a circulation clerk cannot submit a new book', function () {
    $clerk = createBookTestClerk();

    $this->actingAs($clerk, 'web')
        ->post(route('books.store'), validBookTestData())
        ->assertForbidden();

    $this->assertDatabaseMissing('books', [
        'ISBN' => validBookTestData()['ISBN'],
    ]);
});

test('unauthenticated users cannot access book creation', function () {
    $this->get(route('books.create'))
        ->assertRedirect(route('login'));

    $this->post(route('books.store'), validBookTestData())
        ->assertRedirect(route('login'));
});

test('a librarian can create a book and its first physical copy', function () {
    $librarian = createBookTestLibrarian();
    $bookData = validBookTestData();

    $this->actingAs($librarian, 'web')
        ->post(route('books.store'), $bookData)
        ->assertRedirect(route('books.index'))
        ->assertSessionHas('success', 'Book added successfully.');

    // Verify that the bibliographic book record was created
    $this->assertDatabaseHas('books', [
        'ISBN' => $bookData['ISBN'],
        'title' => $bookData['title'],
        'author' => $bookData['author'],
        'genre' => $bookData['genre'],
    ]);

    $book = Book::where('ISBN', $bookData['ISBN'])->firstOrFail();

    // Verify that the first physical copy belongs to the book
    $this->assertDatabaseHas('book_copies', [
        'book_id' => $book->book_id,
        'call_number' => $bookData['call_number'],
        'status' => 'available',
    ]);

    expect($book->copies()->count())->toBe(1);
});

test('an existing ISBN cannot be added again', function () {
    $librarian = createBookTestLibrarian();
    $bookData = validBookTestData();

    // Create an existing book before attempting a duplicate
    Book::create([
        'ISBN' => $bookData['ISBN'],
        'title' => $bookData['title'],
        'author' => $bookData['author'],
        'genre' => $bookData['genre'],
    ]);

    $this->actingAs($librarian, 'web')
        ->from(route('books.create'))
        ->post(route('books.store'), $bookData)
        ->assertRedirect(route('books.create'))
        ->assertSessionHasErrors(['ISBN']);

    // Verify that the duplicate submission created no records
    expect(
        Book::where('ISBN', $bookData['ISBN'])->count()
    )->toBe(1);

    expect(BookCopy::count())->toBe(0);
});

test('required book fields cannot be empty', function () {
    $librarian = createBookTestLibrarian();

    $this->actingAs($librarian, 'web')
        ->from(route('books.create'))
        ->post(route('books.store'), [
            'ISBN' => '',
            'title' => '',
            'author' => '',
            'genre' => '',
            'call_number' => '',
        ])
        ->assertRedirect(route('books.create'))
        ->assertSessionHasErrors([
            'ISBN',
            'title',
            'author',
            'genre',
            'call_number',
        ]);

    expect(Book::count())->toBe(0);
    expect(BookCopy::count())->toBe(0);
});

test('invalid ISBN formats are rejected', function () {
    $librarian = createBookTestLibrarian();
    $bookData = validBookTestData();

    $bookData['ISBN'] = 'INVALID-ISBN';

    $this->actingAs($librarian, 'web')
        ->from(route('books.create'))
        ->post(route('books.store'), $bookData)
        ->assertRedirect(route('books.create'))
        ->assertSessionHasErrors(['ISBN']);

    expect(Book::count())->toBe(0);
    expect(BookCopy::count())->toBe(0);
});

test('books with the same title and different ISBNs are allowed', function () {
    $librarian = createBookTestLibrarian();
    $firstBook = validBookTestData();

    $secondBook = validBookTestData();
    $secondBook['ISBN'] = '9780137081073';

    $this->actingAs($librarian, 'web');

    $this->post(route('books.store'), $firstBook)
        ->assertRedirect(route('books.index'));

    $this->post(route('books.store'), $secondBook)
        ->assertRedirect(route('books.index'));

    // Both books may share a title but must have different ISBNs
    expect(
        Book::where('title', $firstBook['title'])->count()
    )->toBe(2);

    expect(BookCopy::count())->toBe(2);
});

test('book creation rolls back when physical copy creation fails', function () {
    $bookData = validBookTestData();

    // Use invalid service data to trigger a database constraint failure
    $bookData['call_number'] = null;

    try {
        app(BookService::class)->createBook($bookData);

        $this->fail('Expected physical copy creation to fail');
    } catch (QueryException $exception) {
        // Confirm the database rejected the invalid physical copy
    }

    // Verify that the failed transaction did not leave a book behind
    $this->assertDatabaseMissing('books', [
        'ISBN' => $bookData['ISBN'],
    ]);

    expect(BookCopy::count())->toBe(0);
});
