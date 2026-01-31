<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrBankConnect extends Model {
    protected $table = 'qr_bank_connect';
    protected $fillable = [
        'user_id',
        'bin_bank',
        'number_account',
        'amount',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}