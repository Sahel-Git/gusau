<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\WelcomeController;

Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/', [HomeController::class, 'index'])->name('home');

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
        Route::get('dashboard', function () {
            return view('user.dashboard');
        })->name('dashboard');

        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
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
        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
        Volt::route('payments', 'vendor.payments.index')->name('payments.index');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('settings', function() {
        $role = auth()->user()->role ?? 'user';
        return redirect()->route("{$role}.settings.profile");
    });
    
    Route::get('settings/profile', function() {
        $role = auth()->user()->role ?? 'user';
        return redirect()->route("{$role}.settings.profile");
    });
});

// Admin Routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->group(function () {
        Volt::route('dashboard', 'admin.dashboard')->name('dashboard');
        Volt::route('analytics', 'admin.analytics.index')->name('analytics.index');
        Volt::route('categories', 'admin.category-manager')->name('categories');
        Volt::route('approvals', 'admin.approval-queue')->name('approvals');
        Volt::route('listings', 'admin.listings.index')->name('listings.index');
        Volt::route('listings/{listing}', 'admin.listings.show')->name('listings.show');
        Volt::route('users', 'admin.users.index')->name('users.index');
        Volt::route('users/{user}', 'admin.users.show')->name('users.show');
        Volt::route('payments', 'admin.payments.index')->name('payments.index');
        
        Volt::route('orders', 'admin.orders.index')->name('orders.index');
        Volt::route('orders/{order}', 'admin.orders.show')->name('orders.show');

        Volt::route('vendors', 'admin.vendors.index')->name('vendors.index');
        Volt::route('vendors/{user}', 'admin.vendors.show')->name('vendors.show');
        
        Volt::route('stores', 'admin.stores.index')->name('stores.index');
        Volt::route('stores/{store}', 'admin.stores.show')->name('stores.show');

        Volt::route('settings', 'admin.settings.index')->name('settings.index');
        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
        Volt::route('logs', 'admin.logs.index')->name('logs.index');
    });

use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\DealController;

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/deals', [DealController::class, 'index'])->name('deals.index');

Route::get('/services', function () {
    return view('user.services');
})->name('services.index');

Route::get('/contact', function () {
    return view('user.contact');
})->name('contact.index');

Route::get('/test-home', function () {
    return 'TEST ROUTE WORKING';
});

Route::get('/test-view', function () {
    return view('user.home');
});

Route::post('/cart/add', function (Illuminate\Http\Request $request) {
    $listing = \App\Models\Listing::with('store')->find($request->listing_id);

    if (!$listing || !$listing->store) {
        return back()->with('error', 'Invalid product');
    }

    if ($listing->isProduct() && $listing->stock !== null && $listing->stock <= 0) {
        return back()->with('error', 'Out of stock');
    }

    $cart = session()->get('cart', []);

    $storeId = $listing->store->id;

    $cart[$storeId]['store_name'] = $listing->store->name;

    $cart[$storeId]['items'][$listing->id] = [
        'title' => $listing->title,
        'price' => $listing->price,
        'quantity' => ($cart[$storeId]['items'][$listing->id]['quantity'] ?? 0) + 1,
        'image' => $listing->images[0] ?? $listing->image ?? 'fallback.png',
    ];

    session()->put('cart', $cart);

    return back()->with('success', 'Added to cart');
})->name('cart.add');

Route::get('/cart', function () {
    $cart = session()->get('cart', []);
    
    // SAFE PRICE RECHECK (LIGHT) - Prepare for future price re-calculation
    // foreach ($cart as $storeId => $store) {
    //     foreach ($store['items'] as $listingId => $item) {
    //         $currentPrice = \App\Models\Listing::find($listingId)?->price;
    //     }
    // }
    
    return view('user.cart', ['cart' => $cart]);
})->name('cart.index');

Route::post('/cart/increase', function (Illuminate\Http\Request $request) {
    $cart = session()->get('cart', []);
    if (isset($cart[$request->store_id]['items'][$request->listing_id])) {
        if ($cart[$request->store_id]['items'][$request->listing_id]['quantity'] >= 10) {
            return back()->with('error', 'Max quantity reached');
        }
        $cart[$request->store_id]['items'][$request->listing_id]['quantity']++;
        
        foreach ($cart as $storeId => $store) {
            if (empty($store['items'])) {
                unset($cart[$storeId]);
            }
        }
        
        session()->put('cart', $cart);
    }
    return back();
})->name('cart.increase');

Route::post('/cart/decrease', function (Illuminate\Http\Request $request) {
    $cart = session()->get('cart', []);
    if (isset($cart[$request->store_id]['items'][$request->listing_id])) {
        if ($cart[$request->store_id]['items'][$request->listing_id]['quantity'] > 1) {
            $cart[$request->store_id]['items'][$request->listing_id]['quantity']--;
        } else {
            unset($cart[$request->store_id]['items'][$request->listing_id]);
        }
        
        foreach ($cart as $storeId => $store) {
            if (empty($store['items'])) {
                unset($cart[$storeId]);
            }
        }
        
        session()->put('cart', $cart);
    }
    return back();
})->name('cart.decrease');

Route::post('/cart/remove', function (Illuminate\Http\Request $request) {
    $cart = session()->get('cart', []);
    if (isset($cart[$request->store_id]['items'][$request->listing_id])) {
        unset($cart[$request->store_id]['items'][$request->listing_id]);
        
        foreach ($cart as $storeId => $store) {
            if (empty($store['items'])) {
                unset($cart[$storeId]);
            }
        }
        
        session()->put('cart', $cart);
    }
    return back();
})->name('cart.remove');

Route::post('/cart/pay', [\App\Http\Controllers\CheckoutController::class, 'pay'])
    ->middleware('auth')
    ->name('cart.pay');

Route::get('/checkout/verify', [\App\Http\Controllers\CheckoutController::class, 'verify'])
    ->name('checkout.verify');

require __DIR__.'/auth.php';
