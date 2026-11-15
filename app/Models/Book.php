<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $primaryKey = 'book_id';

    public $timestamps = false;

    // Allow assignment of validated book information
    protected $fillable = [
        'ISBN',
        'title',
        'author',
        'genre',
    ];

    public function copies()
    {
        return $this->hasMany(BookCopy::class, "book_id");
    }

    public function holds()
    {
        return $this->hasMany(Hold::class, "book_id");
    }
}

