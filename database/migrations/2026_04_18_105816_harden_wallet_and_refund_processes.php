<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('order_item_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->unique(['order_id', 'order_item_id']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unique('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'order_item_id']);
            $table->dropForeign(['order_item_id']);
            $table->dropColumn('order_item_id');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['reference_id']);
        });
    }
};
