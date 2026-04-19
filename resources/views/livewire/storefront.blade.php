<?php

use App\Models\Store;
use function Livewire\Volt\{state, mount, layout};

layout('components.layouts.app');

state(['store', 'listings']);

mount(function (Store $store) {
    $this->store = $store;
    $this->listings = $store->listings()->where('status', 'approved')->with('category')->latest()->get();
});
?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    {{-- Cover & Profile Image Section --}}
    <div class="relative w-full h-64 md:h-80 lg:h-96 bg-zinc-200 dark:bg-zinc-800 overflow-hidden group">
        @if($store->banner_path)
            <img src="{{ Storage::url($store->banner_path) }}" 
                 class="w-full h-full object-cover transition-transform duration-700 ease-in-out group-hover:scale-105" 
                 alt="Store Banner">
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-pink-500/20 animate-pulse"></div>
            <div class="absolute inset-0 flex items-center justify-center text-zinc-400">
                <svg class="h-24 w-24 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        @endif
        
        {{-- Overlay Gradient --}}
        <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-zinc-50 dark:from-zinc-950 to-transparent"></div>
    </div>

    {{-- Profile Info Section --}}
    <div class="max-w-7xl mx-auto px-6 relative z-10 -mt-24 sm:-mt-32">
        <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6 sm:gap-8">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full blur opacity-40 group-hover:opacity-75 transition duration-500"></div>
                <div class="relative h-32 w-32 sm:h-40 sm:w-40 rounded-full border-4 border-white dark:border-zinc-950 bg-white dark:bg-zinc-900 shadow-xl overflow-hidden flex items-center justify-center text-indigo-500 hover:scale-105 transition-transform duration-300">
                    @if($store->logo_path)
                        <img src="{{ Storage::url($store->logo_path) }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-5xl sm:text-6xl font-extrabold tracking-tight">{{ substr($store->name ?? 'S', 0, 1) }}</span>
                    @endif
                </div>
            </div>
            
            <div class="flex-grow text-center sm:text-left mb-2">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white flex items-center justify-center sm:justify-start gap-2">
                    {{ $store->name }}
                    <svg class="h-6 w-6 text-emerald-500 fill-current" viewBox="0 0 24 24"><path d="M9 16.17l-3.88-3.88a.996.996 0 10-1.41 1.41l4.59 4.59c.39.39 1.02.39 1.41 0l10.59-10.59a.996.996 0 10-1.41-1.41L9 16.17z"/></svg>
                </h1>
                <p class="text-zinc-500 dark:text-zinc-400 mt-1 font-medium">Joined {{ $store->created_at->format('F Y') }} &bull; {{ $listings->count() }} Listings</p>
                
                @if($store->address || $store->contact)
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                    @if($store->contact)
                    <span class="inline-flex items-center gap-1.5 font-medium">
                        <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> 
                        {{ $store->contact }}
                    </span>
                    @endif
                    @if($store->address)
                    <span class="inline-flex items-center gap-1.5 font-medium">
                        <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $store->address }}
                    </span>
                    @endif
                </div>
                @endif
            </div>
            
            <div class="mb-2">
                <button class="rounded-full bg-indigo-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Contact Store
                </button>
            </div>
        </div>
        
        @if($store->bio)
            <div class="mt-8 max-w-3xl mx-auto sm:mx-0">
                <p class="text-lg text-zinc-600 dark:text-zinc-300 leading-relaxed text-center sm:text-left">
                    {{ $store->bio }}
                </p>
            </div>
        @endif
    </div>

    {{-- Line Separator --}}
    <div class="max-w-7xl mx-auto px-6 mt-12 mb-12">
        <div class="h-px w-full bg-gradient-to-r from-transparent via-zinc-200 dark:via-zinc-800 to-transparent"></div>
    </div>

    {{-- Store Offerings --}}
    <div class="max-w-7xl mx-auto px-6 pb-24">
        <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white mb-8 flex items-center gap-2">
            <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            Available Offerings
        </h2>

        @if($listings->isEmpty())
            <div class="rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-sm">
                <div class="h-16 w-16 mx-auto mb-4 bg-zinc-50 dark:bg-zinc-800 rounded-full flex items-center justify-center text-zinc-400">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Nothing here yet</h3>
                <p class="text-zinc-500 mt-2">This store hasn't published any offerings at the moment.</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($listings as $listing)
                    <div class="group flex flex-col rounded-3xl bg-white dark:bg-zinc-900 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.1)] border border-zinc-100 dark:border-zinc-800 overflow-hidden hover:-translate-y-1 block transition-all duration-300">
                        <div class="aspect-[4/3] bg-zinc-100 dark:bg-zinc-800 relative overflow-hidden">
                            @if(!empty($listing->images))
                                <img src="{{ Storage::url($listing->images[0]) }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-700">
                                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            
                            {{-- Price Tag --}}
                            <div class="absolute bottom-4 left-4 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md rounded-full px-3 py-1 font-bold text-zinc-900 dark:text-white shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 group-hover:-translate-y-1 transition-transform duration-300">
                                ₦{{ number_format($listing->price, 2) }}
                            </div>
                        </div>
                        
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-bold px-2.5 py-0.5 rounded-md uppercase tracking-wide">
                                    {{ $listing->type === 'product' ? 'Product' : 'Service' }}
                                </span>
                                <span class="text-xs text-zinc-500 line-clamp-1">{{ $listing->category->name ?? 'Uncategorized' }}</span>
                            </div>
                            
                            <h3 class="font-bold text-zinc-900 dark:text-white text-lg line-clamp-1 mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $listing->title }}
                            </h3>
                            
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 mb-4 flex-grow">
                                {{ $listing->description }}
                            </p>

                            <a href="{{ route('listing.show', $listing->slug) }}" class="w-full mt-auto py-2.5 rounded-xl border-2 border-indigo-100 dark:border-zinc-800 text-indigo-600 dark:text-white font-bold text-sm bg-indigo-50/50 dark:bg-zinc-800/50 group-hover:border-indigo-600 dark:group-hover:border-indigo-500 group-hover:bg-indigo-600 dark:group-hover:text-white transition-all duration-300 text-center block">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
