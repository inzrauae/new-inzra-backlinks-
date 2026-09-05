<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id',
        'sku',
        'ebay_item_number',
        'slug',
        'name',
        'seo_title',
        'meta_description',
        'seo_description',
        'canonical_url',
        'price',
        'currency',
        'quantity_available',
        'quantity_sold',
        'image_path',
        'og_image',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn () => rtrim(rtrim(number_format((float) $this->price, 2), '0'), '.'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function blogPost(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
