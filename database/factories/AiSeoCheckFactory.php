<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\AiSeoCheck;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AiSeoCheckFactory extends Factory
{
    protected $model = AiSeoCheck::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'url' => 'https://example.com',
            'host' => 'example.com',
            'overall_score' => 62,
            'engine_scores' => [
                ['key' => 'chatgpt', 'name' => 'ChatGPT (OpenAI)', 'icon' => 'fa-solid fa-comment-dots', 'score' => 60, 'crawler_allowed' => true],
                ['key' => 'google_ai', 'name' => 'Google AI Overviews (Gemini)', 'icon' => 'fa-brands fa-google', 'score' => 65, 'crawler_allowed' => true],
            ],
            'findings' => [
                ['severity' => 'high', 'title' => 'No structured data (JSON-LD) found', 'detail' => 'Detail text.', 'fix' => 'Fix text.'],
            ],
            'access_token' => Str::random(40),
            'payer_email' => null,
            'currency' => 'USD',
            'amount' => 30.00,
            'payment_status' => PaymentStatus::Unpaid,
            'paypal_order_id' => null,
            'paid_at' => null,
        ];
    }
}
