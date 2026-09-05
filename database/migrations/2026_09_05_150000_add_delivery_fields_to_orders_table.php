<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_url', 500)->nullable()->after('admin_note');
            $table->string('delivery_file_path', 255)->nullable()->after('delivery_url');
            $table->string('delivery_file_name', 255)->nullable()->after('delivery_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_url', 'delivery_file_path', 'delivery_file_name']);
        });
    }
};
