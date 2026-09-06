<?php

namespace App\Models;

use App\Enums\SeoOrderNoteType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoOrderNote extends Model
{
    protected $fillable = ['seo_order_id', 'user_id', 'type', 'body'];

    protected function casts(): array
    {
        return [
            'type' => SeoOrderNoteType::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SeoOrder::class, 'seo_order_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
