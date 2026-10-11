<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    protected $primaryKey = 'hold_id';

    public $timestamps = false;

    // Use date and time for hold_date for hold-queuing purpose and eventual resolution
    protected function casts(): array
    {
        return [
            'hold_date' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
