<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
