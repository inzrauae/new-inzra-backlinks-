<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_seo_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('url', 2048);
            $table->string('host')->nullable()->index();
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->json('engine_scores');
            $table->json('findings');
            $table->string('access_token', 64)->unique();
            $table->string('payer_email')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->decimal('amount', 8, 2)->default(30.00);
            $table->string('payment_status', 20)->default('unpaid')->index();
            $table->string('paypal_order_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_seo_checks');
    }
};
