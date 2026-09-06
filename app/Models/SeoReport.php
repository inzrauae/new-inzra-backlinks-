<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoReport extends Model
{
    protected $fillable = [
        'seo_order_id',
        'status',
        'pdf_path',
        'csv_path',
        'publication_count',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SeoOrder::class, 'seo_order_id');
    }
}
