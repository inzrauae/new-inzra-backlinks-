<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoOrderKeyword extends Model
{
    public $timestamps = false;

    protected $fillable = ['seo_order_id', 'position', 'keyword'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SeoOrder::class, 'seo_order_id');
    }
}
