<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_order_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_order_id')->constrained('seo_orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('keyword', 255);

            $table->unique(['seo_order_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_order_keywords');
    }
};
