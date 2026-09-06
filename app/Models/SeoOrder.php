<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PublicationStatus;
use App\Enums\SeoOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SeoOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'seo_service_id',
        'service_name',
        'target_url',
        'country_id',
        'article',
        'instructions',
        'quantity',
        'unit_price',
        'subtotal',
        'tax',
        'total',
        'currency',
        'payment_method',
        'payment_status',
        'order_status',
        'paypal_order_id',
        'terms_accepted_at',
        'terms_version',
        'estimated_completion_at',
        'paid_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_status' => PaymentStatus::class,
            'order_status' => SeoOrderStatus::class,
            'unit_price' => 'decimal:4',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'terms_accepted_at' => 'datetime',
            'estimated_completion_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'SEO-'.now()->format('Ymd').'-';

        do {
            $number = $prefix.Str::padLeft((string) random_int(0, 9999), 4, '0');
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SeoService::class, 'seo_service_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(SeoOrderKeyword::class)->orderBy('position');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(SeoPublication::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(SeoOrderStatusHistory::class)->latest('created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SeoOrderNote::class)->latest();
    }

    public function report(): HasOne
    {
        return $this->hasOne(SeoReport::class);
    }

    public function completedCount(): int
    {
        return $this->publications()->where('status', PublicationStatus::Verified->value)->count();
    }

    public function progressPercent(): int
    {
        if ($this->quantity < 1) {
            return 0;
        }

        return (int) min(100, round($this->completedCount() / $this->quantity * 100));
    }

    public function remainingCount(): int
    {
        return max(0, $this->quantity - $this->completedCount());
    }

    public function estimatedCompletionLabel(): ?string
    {
        if (! $this->estimated_completion_at instanceof Carbon) {
            return null;
        }

        return $this->estimated_completion_at->format('j F Y, g:ia');
    }
}
