<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'user_id',
        'payer',
        'positive',
        'negative',
        'card',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payerUser()
    {
        return $this->belongsTo(User::class, 'payer');
    }
}
