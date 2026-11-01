<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $primaryKey = 'member_id';

    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'member_id');
    }

    public function holds()
    {
        return $this->hasMany(Hold::class, 'member_id');
    }
}
