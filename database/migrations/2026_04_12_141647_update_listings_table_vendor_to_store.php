<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add store_id
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete()->after('id');
        });

        // Loop through all users with role 'vendor' and create a store for them
        // Then update their listings to use the new store_id
        $vendors = DB::table('users')->where('role', 'vendor')->get();
        foreach ($vendors as $vendor) {
            $storeId = DB::table('stores')->insertGetId([
                'user_id' => $vendor->id,
                'name' => $vendor->name . "'s Store",
                'slug' => \Illuminate\Support\Str::slug($vendor->name . '-store-' . uniqid()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('listings')->where('vendor_id', $vendor->id)->update([
                'store_id' => $storeId,
            ]);
        }

        // Now that data is migrated, drop vendor_id
        // (SQLite doesn't easily support dropping foreign keys in a single alter statement in older versions, 
        // but Laravel handles it reasonably well usually. If issues arise, we can suppress foreign key checks).
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
        
        // Make store_id non-nullable if we want
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->constrained('users')->cascadeOnDelete()->after('id');
        });

        // Revert data
        $listings = DB::table('listings')->get();
        foreach ($listings as $listing) {
            if ($listing->store_id) {
                $store = DB::table('stores')->where('id', $listing->store_id)->first();
                if ($store) {
                    DB::table('listings')->where('id', $listing->id)->update([
                        'vendor_id' => $store->user_id,
                    ]);
                }
            }
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
