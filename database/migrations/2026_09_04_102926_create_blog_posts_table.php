<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('title', 255);
            $table->string('excerpt', 320)->nullable();
            $table->string('category', 60)->nullable()->index();
            $table->string('cover_image_path', 255)->nullable();
            $table->longText('body')->nullable();
            $table->json('faqs')->nullable();
            $table->date('published_at')->nullable();
            $table->unsignedTinyInteger('reading_minutes')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
