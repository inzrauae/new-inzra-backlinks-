<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_order_id')->unique()->constrained('seo_orders')->cascadeOnDelete();
            $table->string('status', 20)->default('preparing');
            $table->string('pdf_path', 255)->nullable();
            $table->string('csv_path', 255)->nullable();
            $table->unsignedInteger('publication_count')->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_reports');
    }
};
