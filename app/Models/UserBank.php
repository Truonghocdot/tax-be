<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    protected $table = "user_banks";

    protected $fillable = [
        'user_id',
        'account_number',
        'account_name',
        'bank_id',
        'password',
        'type',
        'tag_number',
        'number_account',
        'CVV',
        'expired_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Banks::class);
    }
}
