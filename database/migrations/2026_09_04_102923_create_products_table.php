<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('sku', 30)->unique();
            $table->string('ebay_item_number', 20)->nullable();
            $table->string('slug', 180)->unique();
            $table->string('name', 255);
            $table->string('meta_description', 320)->nullable();
            $table->decimal('price', 8, 2);
            $table->char('currency', 3)->default('USD');
            $table->unsignedInteger('quantity_available')->default(0);
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->string('image_path', 255)->nullable();
            $table->longText('body')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
