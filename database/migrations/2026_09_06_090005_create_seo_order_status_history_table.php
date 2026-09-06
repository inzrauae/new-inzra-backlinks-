<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_order_id')->constrained('seo_orders')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 500)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['seo_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_order_status_history');
    }
};
