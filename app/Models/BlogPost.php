<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'slug',
        'title',
        'excerpt',
        'category',
        'cover_image_path',
        'body',
        'faqs',
        'published_at',
        'reading_minutes',
    ];

    protected function casts(): array
    {
        return [
            'faqs' => 'array',
            'published_at' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
