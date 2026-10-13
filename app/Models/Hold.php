<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    protected $table = 'holds';

    protected $primaryKey = 'hold_id';

    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'book_id',
        'hold_date',
        'status',
    ];
}
