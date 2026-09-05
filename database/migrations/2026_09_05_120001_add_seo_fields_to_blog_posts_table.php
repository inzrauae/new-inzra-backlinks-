<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('seo_title', 255)->nullable()->after('title');
            $table->string('seo_description', 320)->nullable()->after('excerpt');
            $table->string('canonical_url', 255)->nullable()->after('seo_description');
            $table->string('og_image', 255)->nullable()->after('cover_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'canonical_url', 'og_image']);
        });
    }
};
