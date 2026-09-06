<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seo_service_id')->constrained('seo_services')->restrictOnDelete();
            $table->string('service_name', 150);
            $table->string('target_url', 500);
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->longText('article')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 4);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('payment_method', 20)->default('paypal');
            $table->string('payment_status', 20)->default('unpaid')->index();
            $table->string('order_status', 30)->default('pending_payment')->index();
            $table->string('paypal_order_id')->nullable()->unique();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_version', 20)->nullable();
            $table->timestamp('estimated_completion_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'order_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_orders');
    }
};
