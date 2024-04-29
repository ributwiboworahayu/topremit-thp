<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'sender_id',
        'recipient_id',
        'transaction_code',
        'from_currency',
        'to_currency',
        'amount',
        'exchange_amount',
        'exchange_rate',
        'fee',
        'amount_type',
        'description',
    ];
}
