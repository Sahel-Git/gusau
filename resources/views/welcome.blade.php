<?php

use Livewire\Volt\Component;
use App\Models\Category;
use App\Models\Listing;

new class extends Component {
    public function with()
    {
        return [
            'categories' => Category::latest()->take(6)->get(),
            'latestListings' => Listing::with(['store', 'category'])
                ->where('status', 'approved')
                ->latest()
                ->take(8)
                ->get(),
        ];
    }
}; ?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sahel DigiMart - Premium Goods & Services</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: { sans: ['Inter', 'sans-serif'] },
                    }
                }
            }
        </script>
    @endif
</head>
<body class="antialiased bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 font-sans selection:bg-indigo-500/30 selection:text-indigo-900">

    {{-- Navigation --}}
    <nav class="fixed top-0 w-full z-50 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 transition-all">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/30">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="font-extrabold text-xl tracking-tight hidden sm:block">Sahel<span class="text-indigo-600">-DigiMart</span></span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                <a href="#categories" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Categories</a>
                <a href="#featured" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Featured Items</a>
                <a href="#vendors" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Top Sellers</a>
            </div>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        @php
                            $dashboardUrl = route('dashboard');
                            if (auth()->user()->isAdmin()) $dashboardUrl = route('admin.dashboard');
                            elseif (auth()->user()->isVendor()) $dashboardUrl = route('vendor.dashboard');
                        @endphp
                        <a href="{{ $dashboardUrl }}" class="text-sm font-bold bg-zinc-100 dark:bg-zinc-800 px-4 py-2 rounded-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">My Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold hover:text-indigo-600 transition-colors">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-semibold bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 rounded-full hover:-translate-y-0.5 hover:shadow-lg transition-all">Sign up &rarr;</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <main class="relative pt-32 pb-16 sm:pt-40 sm:pb-24 overflow-hidden">
        {{-- High-end background gradients --}}
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-100 via-transparent to-transparent dark:from-indigo-900/10 pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 h-[500px] w-[500px] rounded-full bg-indigo-500/20 dark:bg-indigo-600/10 blur-[100px] -z-10 pointer-events-none"></div>
        <div class="absolute top-32 -left-24 h-96 w-96 rounded-full bg-purple-500/20 dark:bg-purple-600/10 blur-[80px] -z-10 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-6 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-sm font-semibold mb-6 ring-1 ring-inset ring-indigo-500/20">
                <span class="flex h-2 w-2 rounded-full bg-indigo-600 animate-pulse"></span>
                The Next Generation Marketplace
            </div>
            <h1 class="mx-auto max-w-5xl text-5xl font-extrabold tracking-tight sm:text-7xl text-balance">
                Discover exceptional <span class="bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500 bg-clip-text text-transparent bg-[length:200%_auto] animate-gradient">products and services</span> globally.
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400 text-balance leading-relaxed">
                Connect with thousands of verified independent vendors. Whether you're looking for unique handcrafted items or professional services, it's all here.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#featured" class="w-full sm:w-auto rounded-full bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition-all hover:-translate-y-1 hover:shadow-xl hover:bg-indigo-500">
                    Start Exploring
                </a>
                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="w-full sm:w-auto rounded-full bg-white dark:bg-zinc-900 px-8 py-3.5 text-sm font-bold text-zinc-900 dark:text-white shadow-sm ring-1 ring-inset ring-zinc-200 dark:ring-zinc-800 transition-all hover:bg-zinc-50 dark:hover:bg-zinc-800 flex items-center justify-center gap-2">
                    Become a Vendor
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </main>

    {{-- Dynamic Categories --}}
    <section id="categories" class="py-20 bg-white dark:bg-zinc-900 border-y border-zinc-100 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Trending Categories</h2>
                    <p class="text-zinc-500 mt-2">Explore the most popular sections based on shopper activity.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-6">
                @forelse($categories as $category)
                    <a href="#" class="group flex flex-col p-6 rounded-3xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 hover:shadow-lg transition-all duration-300 items-center justify-center text-center">
                        <div class="h-16 w-16 rounded-full bg-white dark:bg-zinc-900 shadow-sm flex items-center justify-center text-indigo-500 mb-4 group-hover:scale-110 transition-transform">
                            {{-- Placeholder generic icon since we don't have SVG per category in MVP --}}
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h3 class="font-bold text-zinc-900 dark:text-zinc-100 line-clamp-1">{{ $category->name }}</h3>
                        <p class="text-xs text-zinc-500 mt-1">Explore</p>
                    </a>
                @empty
                    {{-- Dummy Fallbacks if no DB entries exist --}}
                    @foreach(['Digital Art', 'Web Design', 'Handmade', 'Consulting', 'Electronics', 'Apparel'] as $mockTitle)
                     <div class="group flex flex-col p-6 rounded-3xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-100 dark:border-zinc-800/50 hover:border-indigo-200 dark:hover:border-indigo-500/30 hover:shadow-lg transition-all items-center text-center opacity-70">
                        <div class="h-14 w-14 rounded-full bg-white dark:bg-zinc-900 shadow-sm flex items-center justify-center text-zinc-400 mb-4">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="font-bold text-zinc-600 dark:text-zinc-300 text-sm">{{ $mockTitle }}</h3>
                    </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- Dynamic Featured Listings --}}
    <section id="featured" class="py-24 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-12 flex flex-col sm:flex-row justify-between items-end gap-4">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl text-zinc-900 dark:text-white">Newly Added Offerings</h2>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400">Discover hand-picked items and premium services from our trusted vendors.</p>
                </div>
                <a href="{{ route('register') }}" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center gap-1">
                    View Complete Catalog <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse($latestListings as $listing)
                    <div class="group flex flex-col rounded-3xl bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="aspect-[4/3] bg-zinc-100 dark:bg-zinc-800 relative overflow-hidden">
                            @if(!empty($listing->images))
                                <img src="{{ Storage::url($listing->images[0]) }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-700">
                                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            
                            {{-- Price Tag --}}
                            <div class="absolute bottom-4 left-4 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-sm rounded-full px-3 py-1 font-bold text-zinc-900 dark:text-white shadow-lg border border-zinc-200 dark:border-zinc-700">
                                ${{ number_format($listing->price, 2) }}
                            </div>
                        </div>
                        
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
                                    {{ $listing->type === 'product' ? 'Product' : 'Service' }}
                                </span>
                                <span class="text-xs text-zinc-500 truncate">{{ $listing->category->name ?? 'Uncategorized' }}</span>
                            </div>
                            
                            <h3 class="font-bold text-zinc-900 dark:text-white text-lg line-clamp-1 mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $listing->title }}
                            </h3>
                            
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 mb-4">
                                {{ $listing->description }}
                            </p>
                            
                            <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-800/50 flex items-center justify-between">
                                @if($listing->store && $listing->store->slug)
                                    <a href="{{ route('store.show', $listing->store->slug) }}" class="flex items-center gap-2 group/store hover:opacity-80 transition-opacity">
                                        <div class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold shadow-sm group-hover/store:bg-indigo-600 group-hover/store:text-white transition-colors">
                                            {{ substr($listing->store->name ?? 'S', 0, 1) }}
                                        </div>
                                        <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 truncate w-24 group-hover/store:text-indigo-600 dark:group-hover/store:text-indigo-400 transition-colors">{{ $listing->store->name ?? 'Store' }}</span>
                                    </a>
                                    <a href="{{ route('store.show', $listing->store->slug) }}" class="text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" title="Visit Store">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    </a>
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">S</div>
                                        <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 truncate w-24">Store</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Dummy Listing Fallbacks --}}
                    @for($i = 1; $i <= 4; $i++)
                        <div class="group flex flex-col rounded-3xl bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden opacity-60 pointer-events-none">
                            <div class="aspect-[4/3] bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-300">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="p-5 flex flex-col">
                                <h3 class="font-bold text-zinc-400 mb-2">Placeholder Listing {{ $i }}</h3>
                                <div class="h-2 w-full bg-zinc-200 dark:bg-zinc-800 rounded mb-2"></div>
                                <div class="h-2 w-2/3 bg-zinc-200 dark:bg-zinc-800 rounded mb-4"></div>
                                <div class="font-bold text-zinc-500">$99.00</div>
                            </div>
                        </div>
                    @endfor
                @endforelse
            </div>
            
            @if(count($latestListings) > 0)
            <div class="mt-12 text-center">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-zinc-900 dark:bg-white px-6 py-3 text-sm font-bold text-white dark:text-zinc-900 shadow-sm hover:-translate-y-0.5 transition-transform">
                    Sign up to see more
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200 dark:border-zinc-800 pt-16 pb-8 bg-white dark:bg-zinc-950 mt-auto">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span class="font-extrabold text-xl text-zinc-900 dark:text-zinc-100">Sahel<span class="text-indigo-500">-DigiMart</span></span>
            </div>
            
            <p class="text-zinc-500 dark:text-zinc-400 text-sm">&copy; {{ date('Y') }} Sahel-DigiMart. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>