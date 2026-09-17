<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSeoCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'host',
        'overall_score',
        'engine_scores',
        'findings',
        'access_token',
        'payer_email',
        'currency',
        'amount',
        'payment_status',
        'paypal_order_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'engine_scores' => 'array',
            'findings' => 'array',
            'amount' => 'decimal:2',
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
