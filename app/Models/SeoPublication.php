<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'seo_order_id',
        'publisher_name',
        'publisher_url',
        'published_url',
        'target_url',
        'anchor_text',
        'country',
        'publication_date',
        'status',
        'notes',
        'added_by',
        'import_batch',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'publication_date' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SeoOrder::class, 'seo_order_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
