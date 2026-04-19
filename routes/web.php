<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome', [
        'categories' => \App\Models\Category::latest()->take(6)->get(),
        'latestListings' => \App\Models\Listing::with(['store', 'category'])
            ->where('status', 'approved')
            ->latest()
            ->take(8)
            ->get(),
    ]);
})->name('home');

Volt::route('store/{store:slug}', 'storefront')->name('store.show');
Volt::route('listing/{listing:slug}', 'listing-details')->name('listing.show');

// Central Dashboard Route Resolver
Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'vendor' => redirect()->route('vendor.dashboard'),
        default => redirect()->route('user.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// User Routes
Route::prefix('user')
    ->name('user.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Volt::route('dashboard', 'user.dashboard')->name('dashboard');
    });

// Vendor Routes
Route::prefix('vendor')
    ->name('vendor.')
    ->middleware(['auth', 'verified', 'role:vendor'])
    ->group(function () {
        Volt::route('dashboard', 'vendor.dashboard')->name('dashboard');
        Volt::route('listings', 'vendor.listing-manager')->name('listings');
        Volt::route('orders', 'vendor.orders.index')->name('orders');
        Volt::route('orders/{id}', 'vendor.orders.show')->name('orders.show');
        Volt::route('wallet', 'vendor.wallet')->name('wallet');
        Volt::route('analytics', 'vendor.analytics')->name('analytics');
        
        Volt::route('payout-settings', 'vendor.payout-settings')->name('payout.settings');
        Volt::route('store-profile', 'vendor.store-profile')->name('store.profile');
        Volt::route('settings', 'vendor.settings')->name('settings');
    });

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// Admin Routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->group(function () {
        Volt::route('dashboard', 'admin.dashboard')->name('dashboard');
        Volt::route('categories', 'admin.category-manager')->name('categories');
        Volt::route('approvals', 'admin.approval-queue')->name('approvals');
    });

require __DIR__.'/auth.php';
