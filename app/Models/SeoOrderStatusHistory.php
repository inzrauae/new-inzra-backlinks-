<?php

namespace App\Models;

use App\Enums\SeoOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoOrderStatusHistory extends Model
{
    protected $table = 'seo_order_status_history';

    public $timestamps = false;

    protected $fillable = ['seo_order_id', 'from_status', 'to_status', 'changed_by', 'note', 'created_at'];

    protected function casts(): array
    {
        return [
            'from_status' => SeoOrderStatus::class,
            'to_status' => SeoOrderStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SeoOrder::class, 'seo_order_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
