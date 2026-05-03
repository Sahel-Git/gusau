<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Store;
use App\Models\Listing;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = User::firstOrCreate(
            ['email' => 'vendor@sahel.com'],
            [
                'name' => 'Sample Vendor',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'vendor_type' => 'product_seller',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $category = Category::firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Electronics', 'description' => 'Electronic items']
        );

        $store = Store::firstOrCreate(
            ['user_id' => $vendor->id],
            [
                'name' => 'Sample Store',
                'slug' => 'sample-store',
                'status' => 'approved',
            ]
        );

        Listing::firstOrCreate(
            ['slug' => 'sample-laptop'],
            [
                'store_id' => $store->id,
                'category_id' => $category->id,
                'type' => 'product',
                'title' => 'Sample Laptop',
                'description' => 'A very fast sample laptop',
                'price' => 250000.00,
                'stock' => 10,
                'status' => 'approved',
            ]
        );
    }
}
