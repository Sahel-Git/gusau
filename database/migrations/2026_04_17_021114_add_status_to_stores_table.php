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
        Schema::table('stores', function (Blueprint $table) {
            $table->string('status')->default('pending')->index()->after('is_active');
        });

        // Data migration using DB::table as requested
        \Illuminate\Support\Facades\DB::table('stores')->where('is_active', true)->update(['status' => 'active']);
        \Illuminate\Support\Facades\DB::table('stores')->where('is_active', false)->update(['status' => 'suspended']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
