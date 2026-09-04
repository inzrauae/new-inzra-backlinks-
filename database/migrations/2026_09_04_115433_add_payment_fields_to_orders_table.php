<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('whatsapp')->after('payment_status');
            $table->string('paypal_order_id')->nullable()->unique()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('paypal_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'paypal_order_id', 'paid_at']);
        });
    }
};
