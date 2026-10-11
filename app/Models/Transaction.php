<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $primaryKey = 'transaction_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'member_id',
        'copy_id',
        'checkout_date',
        'due_date',
        'return_date',
        'late_fee',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class, 'copy_id');
    }
}
