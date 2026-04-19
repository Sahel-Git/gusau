<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.vendor')] class extends Component {
    public $filter = 'all';

    public function getOrdersProperty()
    {
        $storeId = Auth::user()->store->id;

        $query = OrderItem::with(['order.user', 'listing'])
            ->where('store_id', $storeId);

        if ($this->filter !== 'all') {
            $query->whereHas('order', function ($q) {
                $q->where('status', $this->filter);
            });
        }

        // Fetching items, grouped by order
        $items = $query->latest()->get();

        $grouped = $items->groupBy('order_id')->map(function ($orderItems, $orderId) {
            $firstItem = $orderItems->first();
            $order = $firstItem->order;
            
            return (object) [
                'id'         => $order->id,
                'customer_name' => optional($order->user)->name ?? 'Guest',
                'status'     => $order->status,
                'created_at' => $order->created_at,
                'vendor_total' => $orderItems->sum(function($item) {
                    return $item->price * $item->quantity;
                }),
                'items_count' => $orderItems->sum('quantity'),
            ];
        });

        return $grouped;
    }
    
    public function setFilter($status)
    {
        $this->filter = $status;
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mt-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Order Management
            </h1>
            <p class="text-sm text-zinc-500 mt-1">Manage orders containing your products.</p>
        </div>
        
        <div class="flex gap-2">
            <button wire:click="setFilter('all')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'all' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">
                All
            </button>
            <button wire:click="setFilter('pending')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'pending' ? 'bg-amber-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">
                Pending
            </button>
            <button wire:click="setFilter('processing')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'processing' ? 'bg-indigo-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">
                Processing
            </button>
            <button wire:click="setFilter('completed')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">
                Completed
            </button>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden relative">
        <div wire:loading wire:target="setFilter" class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="h-6 w-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200 sm:pl-6">Order ID</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Customer</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Items</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Your Total</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Status</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Date</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                    @forelse($this->orders as $order)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-150">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-zinc-900 dark:text-zinc-200 sm:pl-6">
                                #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $order->customer_name }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $order->items_count }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-zinc-900 dark:text-zinc-200">
                                ₦{{ number_format($order->vendor_total, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($order->status === 'completed')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Completed</span>
                                @elseif($order->status === 'processing')
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-400">Processing</span>
                                @elseif($order->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-400">Pending</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Cancelled</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <a wire:navigate href="{{ route('vendor.orders.show', $order->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View Details<span class="sr-only">, #{{ $order->id }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4 shadow-inner">
                                        <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <p>No orders found {{ $filter !== 'all' ? "with status '$filter'" : 'yet' }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
