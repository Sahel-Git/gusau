<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

new #[Layout('vendor.layouts.app')] class extends Component {
    public $order;
    public $vendorItems;
    public $vendorTotal;

    public function mount($id)
    {
        $storeId = Auth::user()->store->id;

        $this->order = Order::with('user')->findOrFail($id);
        
        $this->vendorItems = OrderItem::with('listing')
            ->where('order_id', $id)
            ->where('store_id', $storeId)
            ->get();

        if ($this->vendorItems->isEmpty()) {
            abort(403, 'Unauthorized. You have no items in this order.');
        }

        $this->vendorTotal = $this->vendorItems->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }

    public function updateOrderStatus($status)
    {
        if (in_array($status, ['processing', 'completed', 'cancelled'])) {
            $this->order->update(['status' => $status]);
            session()->flash('success', 'Order status updated successfully.');
        }
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-4 mt-4">
        <a wire:navigate href="{{ route('vendor.orders') }}" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
            </h1>
            <p class="text-sm text-zinc-500 mt-1">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
        </div>
        
        @if(session()->has('success'))
            <div class="ml-auto text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-500/20">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left side: Order Items --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Items in your store</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Product</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-zinc-900 dark:text-zinc-200">Price</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-zinc-900 dark:text-zinc-200">Qty</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-zinc-900 dark:text-zinc-200 pr-6">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                            @foreach($vendorItems as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-zinc-900 dark:text-zinc-200">
                                        {{ optional($item->listing)->title ?? 'Unknown Item' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-zinc-500 dark:text-zinc-400">
                                        ₦{{ number_format($item->price, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-zinc-500 dark:text-zinc-400">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-right text-zinc-900 dark:text-zinc-200 pr-6">
                                        ₦{{ number_format($item->price * $item->quantity, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-900/50">
                            <tr>
                                <th scope="row" colspan="3" class="pl-6 pr-3 py-4 text-right text-sm font-bold text-zinc-900 dark:text-white sm:pr-6">
                                    Your Store's Vendor Subtotal
                                </th>
                                <td class="pl-3 pr-6 py-4 text-right text-lg font-bold text-teal-600 dark:text-teal-400">
                                    ₦{{ number_format($vendorTotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden p-6 text-sm text-zinc-500 dark:text-zinc-400">
                <p class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <strong>Note:</strong> You only see items and subtotals belonging to your store. The customer may have purchased items from other vendors in this global order.
                </p>
            </div>
        </div>

        {{-- Right side: Status and Customer Info --}}
        <div class="flex flex-col gap-6">
            
            {{-- Status Update Control --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-900/50">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Order Status</h2>
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
                </div>
                
                <div class="p-6 flex flex-col gap-3">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">Update status (Affects global MVP order):</p>
                    
                    @if($order->status !== 'processing' && $order->status !== 'completed' && $order->status !== 'cancelled')
                        <button wire:click="updateOrderStatus('processing')" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Mark as Processing
                        </button>
                    @endif
                    
                    @if($order->status !== 'completed' && $order->status !== 'cancelled')
                        <button wire:click="updateOrderStatus('completed')" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                            Mark as Completed
                        </button>
                    @endif

                    @if($order->status !== 'cancelled' && $order->status !== 'completed')
                        <button wire:click="updateOrderStatus('cancelled')" wire:confirm="Are you sure you want to cancel this order?" class="w-full mt-2 rounded-lg bg-white dark:bg-zinc-800 px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 shadow-sm ring-1 ring-inset ring-red-300 dark:ring-red-900/50 hover:bg-red-50 dark:hover:bg-red-900/10">
                            Cancel Order
                        </button>
                    @endif
                </div>
            </div>

            {{-- Customer Information --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Customer Details</h2>
                </div>
                <div class="p-6 text-sm text-zinc-700 dark:text-zinc-300">
                    <p class="font-medium text-lg mb-2 text-zinc-900 dark:text-white">{{ optional($order->user)->name ?? 'Guest User' }}</p>
                    <p class="mb-4 text-zinc-500">{{ optional($order->user)->email ?? 'No email provided' }}</p>
                    
                    <h3 class="font-bold text-zinc-900 dark:text-white mt-6 mb-2">Delivery Information</h3>
                    <p class="text-zinc-500">Contact customer for exact shipping/delivery arrangements if outside standard system.</p>
                </div>
            </div>

        </div>
    </div>
</div>
