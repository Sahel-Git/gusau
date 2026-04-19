<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Listing;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    public function with()
    {
        return [
            'totalUsers' => User::where('role', 'user')->count(),
            'totalVendors' => User::where('role', 'vendor')->count(),
            'totalCategories' => Category::count(),
            'totalListings' => Listing::where('status', 'approved')->count(),
            'pendingApprovals' => Listing::where('status', 'pending')->count(),
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Admin Dashboard</h1>
            <p class="text-sm text-zinc-500 mt-1">Market overview and statistics.</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mt-4">
        {{-- Stat 1 --}}
        <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                <flux:icon.users class="h-6 w-6" />
            </div>
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Users</h3>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalUsers }}</p>
            </div>
        </div>

        {{-- Stat 2 --}}
        <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400">
                <flux:icon.building-storefront class="h-6 w-6" />
            </div>
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Vendors</h3>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalVendors }}</p>
            </div>
        </div>

        {{-- Stat 3 --}}
        <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                <flux:icon.tag class="h-6 w-6" />
            </div>
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Categories</h3>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalCategories }}</p>
            </div>
        </div>

        {{-- Stat 4 --}}
        <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                <flux:icon.document-check class="h-6 w-6" />
            </div>
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Active Listings</h3>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalListings }}</p>
            </div>
        </div>
    </div>

    {{-- Pending Approvals alert --}}
    @if($pendingApprovals > 0)
        <div class="mt-4 rounded-2xl bg-amber-50 p-6 shadow-sm ring-1 ring-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 flex flex-col sm:flex-row items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                    <flux:icon.exclamation-triangle class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="font-bold text-amber-900 dark:text-amber-200">Pending Approvals Action Required</h3>
                    <p class="text-sm text-amber-700 dark:text-amber-400/80">There are {{ $pendingApprovals }} listings waiting for review.</p>
                </div>
            </div>
            <a href="{{ route('admin.approvals') }}" class="mt-4 sm:mt-0 rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-colors">
                Review Now
            </a>
        </div>
    @endif
</div>
