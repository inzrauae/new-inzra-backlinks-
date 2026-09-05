<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('seo_title', 255)->nullable()->after('name');
            $table->string('seo_description', 320)->nullable()->after('meta_description');
            $table->string('canonical_url', 255)->nullable()->after('seo_description');
            $table->string('og_image', 255)->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'canonical_url', 'og_image']);
        });
    }
};
