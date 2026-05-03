<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use App\Models\OrderItem;

new #[Layout('vendor.layouts.app')] class extends Component {
    public function with()
    {
        $user = auth()->user();
        $store = \App\Models\Store::where('user_id', $user->id)->first();
        
        $totalRevenue = 0.00;
        $totalSales = 0;
        $recentSales = collect();

        if ($store) {
            $orderItems = OrderItem::where('store_id', $store->id)
                ->whereHas('order', function ($query) {
                    $query->where('orders.status', 'completed');
                })
                ->get();
            
            $totalRevenue = $orderItems->sum(function($item) {
                return $item->price * $item->quantity;
            });

            $totalSales = Order::whereHas('items', function ($query) use ($store) {
                $query->where('store_id', $store->id);
            })->where('orders.status', 'completed')->count();

            $recentSales = OrderItem::with(['order.user', 'listing'])
                ->where('store_id', $store->id)
                ->latest()
                ->take(5)
                ->get();
        }

        return [
            'store' => $store,
            'activeProducts' => $user->listings()->where('listings.status', 'approved')->count(),
            'totalRevenue' => $totalRevenue,
            'totalSales' => $totalSales,
            'storeViews' => 0, // Placeholder until views tracking exists
            'recentSales' => $recentSales,
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4">

        @if($store && $store->isPending())
            <div class="rounded-xl bg-amber-50 p-4 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 flex gap-3">
                <svg class="h-6 w-6 text-amber-600 dark:text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h3 class="text-sm font-bold text-amber-800 dark:text-amber-400">Store Pending Approval</h3>
                    <p class="text-sm text-amber-700 dark:text-amber-500 mt-1">Your store is currently under review by our team. You cannot publish listings or receive orders until approved.</p>
                </div>
            </div>
        @elseif($store && $store->isSuspended())
            <div class="rounded-xl bg-red-50 p-4 border border-red-200 dark:bg-red-500/10 dark:border-red-500/20 flex gap-3">
                <svg class="h-6 w-6 text-red-600 dark:text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h3 class="text-sm font-bold text-red-800 dark:text-red-400">Store Suspended</h3>
                    <p class="text-sm text-red-700 dark:text-red-500 mt-1">Your store has been suspended. Listings are hidden from the marketplace and orders cannot be placed. Please contact support.</p>
                </div>
            </div>
        @endif
        
        {{-- Store Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800/50 p-6 shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-teal-500/10 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="h-16 w-16 rounded-xl bg-teal-500 text-white flex items-center justify-center text-2xl font-bold shadow-teal-500/30 shadow-lg">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        vendor Store
                        <span class="inline-flex ml-2 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border 
                            @if($store && $store->isActive()) bg-teal-100 text-teal-800 dark:bg-teal-500/20 dark:text-teal-400 border-teal-200 dark:border-teal-500/30
                            @elseif($store && $store->isSuspended()) bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 border-red-200 dark:border-red-500/30
                            @else bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400 border-amber-200 dark:border-amber-500/30 @endif">
                            {{ $store ? ucfirst($store->status) : 'Unknown' }} Seller
                        </span>
                    </h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Here is what is happening with your store today.</p>
                </div>
            </div>
            <div class="relative z-10 flex gap-3 w-full sm:w-auto">
                <a wire:navigate href="{{ route('vendor.settings') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200 shadow-sm ring-1 ring-inset ring-zinc-300 dark:ring-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-all">
                    Store Settings
                </a>
                <a wire:navigate href="{{ route('vendor.listings') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 hover:shadow-teal-500/30 transition-all">
                    + Add Product
                </a>
            </div>
        </div>

        {{-- Vendor Metrics Grid --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-500/10 transition-transform group-hover:scale-150 duration-500 ease-out"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Revenue</h3>
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-3xl font-bold text-zinc-900 dark:text-white">₦{{ number_format($totalRevenue, 2) }}</p>
                <div class="mt-2 text-xs text-zinc-500"><span class="text-emerald-500 font-medium">+0%</span> from last month</div>
            </div>

            <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-500/10 transition-transform group-hover:scale-150 duration-500 ease-out"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Active Products</h3>
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $activeProducts }}</p>
                <div class="mt-2 text-xs text-zinc-500">Limits: 0 / 100 listings</div>
            </div>

            <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-orange-500/10 transition-transform group-hover:scale-150 duration-500 ease-out"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Sales</h3>
                    <svg class="h-5 w-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalSales }}</p>
                <div class="mt-2 text-xs text-zinc-500"><span class="text-zinc-400 font-medium">0</span> orders this week</div>
            </div>

            <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-pink-500/10 transition-transform group-hover:scale-150 duration-500 ease-out"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Store Views</h3>
                    <svg class="h-5 w-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $storeViews }}</p>
                <div class="mt-2 text-xs text-zinc-500">Last 30 days</div>
            </div>
        </div>

        {{-- Main Sections --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Recent Sales Table --}}
            <div class="lg:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-900/50">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Recent Sales</h2>
                    <a href="#" class="text-sm font-semibold text-teal-600 hover:text-teal-500 transition-colors">View all &rarr;</a>
                </div>
                
                @if($recentSales->isEmpty())
                <div class="p-12 flex flex-col items-center justify-center text-center">
                    <div class="h-16 w-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4 shadow-inner">
                        <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No sales completed yet.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Order & Item</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Customer</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Amount</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Order Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Payout Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                            @foreach($recentSales as $sale)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-150">
                                    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                                        <div class="font-medium text-zinc-900 dark:text-zinc-200">#{{ str_pad($sale->order_id, 4, '0', STR_PAD_LEFT) }}</div>
                                        <div class="text-zinc-500">{{ $sale->listing->title ?? 'Deleted Item' }} (x{{ $sale->quantity }})</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ optional($sale->order->user)->name ?? 'Guest' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-zinc-900 dark:text-zinc-200">
                                        ₦{{ number_format($sale->price * $sale->quantity, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if(optional($sale->order)->status === 'completed')
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Completed</span>
                                        @elseif(optional($sale->order)->status === 'pending')
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-400">Pending</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">{{ ucfirst(optional($sale->order)->status ?? 'Unknown') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if($sale->payout_status === 'paid')
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Paid</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Quick Actions & Notifications --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-900/50">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Under Review</h2>
                </div>
                
                {{-- SOMETHING WAA TAKING/REMOVED FROM HERE --}}

            </div>
        </div>

    </div>
    </div>
