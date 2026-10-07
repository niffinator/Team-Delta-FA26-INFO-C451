<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $primaryKey = 'book_id';

    public $timestamps = false;

    public function copies()
    {
        return $this->hasMany(BookCopy::class, "book_id");
    }

    public function holds()
    {
        return $this->hasMany(Hold::class, "book_id");
    }
}
