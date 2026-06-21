<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'type', 'amount', 'due',
        'academic_year', 'status', 'paid_at', 'payment_method', 'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due' => 'date',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
