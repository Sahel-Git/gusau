<?php

use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('user.layouts.app')] class extends Component {

    public int $ordersCount = 0;
    public int $savedItemsCount = 0;
    public $totalSpent = 0;
    public $recentOrders = [];

    public function mount()
    {
        $this->ordersCount = 0;
        $this->savedItemsCount = 0;
        // In a real app we'd fetch this from the authenticated user's relationships
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    
    {{-- Buyer Welcome Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-500 via-purple-600 to-indigo-700 p-8 text-white shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">
                    Account Overview
                </h1>
                <p class="mt-3 max-w-2xl text-indigo-100 text-lg">
                    Manage your settings, track recent orders, and view your account summary securely.
                </p>
                <div class="mt-6 flex items-center gap-4">
                    <a href="{{ route('home') }}" class="rounded-full bg-white px-6 py-2.5 text-sm font-bold text-indigo-600 shadow-sm transition-all hover:bg-indigo-50 hover:scale-105">
                        Back to Marketplace Homepage
                    </a>
                </div>
            </div>
        </div>
        
        <div class="absolute -right-20 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 left-20 h-72 w-72 rounded-full bg-purple-400/20 blur-3xl pointer-events-none"></div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid gap-6 sm:grid-cols-3 mt-2">
        <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Orders</h3>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $ordersCount ?? 0 }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Saved Items</h3>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $savedItemsCount ?? 0 }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0-2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Spent</h3>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">${{ number_format($totalSpent ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Recent Orders Grid --}}
    <div class="mt-8 mb-4">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Recent Orders</h2>
        @if(count($recentOrders ?? []) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recentOrders ?? [] as $order)
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-zinc-900 dark:text-white">Order #{{ $order->id }}</span>
                            <span class="text-xs px-2.5 py-1 rounded-full @if($order->status == 'completed') bg-green-100 text-green-700 @elseif($order->status == 'pending') bg-yellow-100 text-yellow-700 @else bg-zinc-100 text-zinc-700 @endif font-semibold uppercase">
                                {{ $order->status }}
                            </span>
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">{{ $order->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="flex justify-between items-end border-t border-zinc-100 dark:border-zinc-800 pt-3">
                        <span class="text-sm text-zinc-500">Total Amount</span>
                        <span class="font-bold text-lg text-indigo-600 dark:text-indigo-400">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center text-zinc-500 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 border-dashed">
                You haven't placed any orders yet.
            </div>
        @endif
    </div>
</div>