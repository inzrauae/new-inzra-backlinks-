<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'provider',
        'enabled',
        'mode',
        'client_id',
        'client_secret',
        'webhook_id',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'client_secret' => 'encrypted',
        ];
    }

    public static function paypal(): self
    {
        return self::firstOrCreate(['provider' => 'paypal'], [
            'enabled' => false,
            'mode' => 'sandbox',
        ]);
    }

    public function isLive(): bool
    {
        return $this->mode === 'live';
    }
}
