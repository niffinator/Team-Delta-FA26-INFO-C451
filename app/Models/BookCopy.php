<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    protected $primaryKey = 'copy_id';

    public $timestamps = false;

    // Allow assignment of validated physical book copy information
    protected $fillable = [
        'book_id',
        'call_number',
        'status',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, "copy_id");
    }
}
