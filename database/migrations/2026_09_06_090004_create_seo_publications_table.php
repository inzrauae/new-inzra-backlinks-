<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_order_id')->constrained('seo_orders')->cascadeOnDelete();
            $table->string('publisher_name', 255)->nullable();
            $table->string('publisher_url', 500)->nullable();
            $table->string('published_url', 500)->nullable();
            $table->string('target_url', 500)->nullable();
            $table->string('anchor_text', 255)->nullable();
            $table->string('country', 100)->nullable();
            $table->date('publication_date')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('import_batch', 40)->nullable()->index();
            $table->timestamps();

            $table->index(['seo_order_id', 'status']);
            $table->index(['seo_order_id', 'published_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_publications');
    }
};
