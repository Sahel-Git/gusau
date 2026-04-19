<?php

use App\Models\Listing;
use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

new #[Layout('components.layouts.user')] class extends Component {

    public array $cartItems = [];
    public bool $orderPlaced = false;
    public int $ordersCount = 0;
    public int $savedItemsCount = 0;
    public $totalSpent = 0;
    public $recentOrders = [];
    public $recommendedListings = [];

    public function mount()
    {
        $this->cartItems = session('cart', []);
        $this->ordersCount = 0;
        $this->savedItemsCount = 0;
        $this->orderPlaced = false;
    }

    public function addToCart($id)
    {
        $listing = Listing::findOrFail($id);
        $cart = session()->get('cart', []);
        
        $exists = false;
        foreach($cart as $key => $item) {
            if ($item['id'] == $id) {
                $cart[$key]['quantity']++;
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $cart[] = [
                'id' => $listing->id,
                'title' => $listing->title,
                'price' => $listing->price,
                'quantity' => 1
            ];
        }
        
        session()->put('cart', $cart);
    }

    public function removeFromCart($index)
    {
        $cart = session()->get('cart', []);
        if(isset($cart[$index])) {
            unset($cart[$index]);
            session()->put('cart', array_values($cart));
        }
    }

    public function getCartTotalProperty()
    {
        $total = 0;
        foreach (session('cart', []) as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function proceedToCheckout()
    {
        // Phase 6 Scalability: Hand over to real checkout logic
        session()->forget('cart');
        return back();
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    
    {{-- Buyer Welcome Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-500 via-purple-600 to-indigo-700 p-8 text-white shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">
                    Welcome back, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="mt-3 max-w-2xl text-indigo-100 text-lg">
                    Discover new items, track your recent orders, and manage your account all in one secure place.
                </p>
                <div class="mt-6 flex items-center gap-4">
                    <a href="{{ route('home') }}" class="rounded-full bg-white px-6 py-2.5 text-sm font-bold text-indigo-600 shadow-sm transition-all hover:bg-indigo-50 hover:scale-105">
                        Browse Marketplace
                    </a>
                </div>
            </div>
            
            {{-- Floating Cart Widget --}}
            <div class="mt-6 md:mt-0 bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 min-w-[280px]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Your Cart
                    </h3>
                    <span class="bg-indigo-600 rounded-full px-2.5 py-0.5 text-xs">{{ count($cartItems ?? []) }} items</span>
                </div>
                
                @if(count($cartItems ?? []) > 0)
                    <div class="space-y-3 max-h-32 overflow-y-auto mb-4 custom-scrollbar">
                        @foreach($cartItems ?? [] as $index => $item)
                            <div class="flex justify-between items-center text-sm border-b border-white/10 pb-2">
                                <div class="truncate pr-2 w-32">{{ $item['title'] }}</div>
                                <div class="flex gap-2">
                                    <span>${{ number_format($item['price'], 2) }} x{{ $item['quantity'] }}</span>
                                    <button wire:click="removeFromCart({{ $index }})" class="text-red-300 hover:text-red-200">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center font-bold border-t border-white/20 pt-3">
                        <span>Total:</span>
                        <span>${{ number_format($this->cartTotal, 2) }}</span>
                    </div>
                    <button wire:click="proceedToCheckout" class="w-full mt-4 bg-white text-indigo-600 py-2 rounded-lg font-bold hover:bg-indigo-50 transition-colors">
                        Checkout Now
                    </button>
                @else
                    <div class="text-center py-4 text-indigo-100 text-sm">
                        Your cart is empty. Start exploring!
                    </div>
                @endif
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

    {{-- Recommended Listings to test Cart --}}
    <div class="mt-8 mb-4">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Recommended for You</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($recommendedListings ?? [] as $listing)
                <div class="bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-lg transition-all duration-300 group">
                    <div class="h-48 bg-zinc-100 dark:bg-zinc-800 relative overflow-hidden">
                        @if(!empty($listing->images))
                            <img src="{{ Storage::url($listing->images[0]) }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-700">
                                <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        <div class="absolute top-3 right-3 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm rounded-full px-3 py-1 text-xs font-bold text-zinc-900 dark:text-white shadow-sm">
                            ${{ number_format($listing->price, 2) }}
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mb-1">{{ $listing->category->name ?? 'Uncategorized' }}</div>
                        <h3 class="font-bold text-zinc-900 dark:text-white text-lg line-clamp-1 mb-1">{{ $listing->title }}</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 mb-4">{{ $listing->description }}</p>
                        
                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-xs text-zinc-500">By {{ $listing->vendor->name }}</span>
                            <button wire:click="addToCart({{ $listing->id }})" class="bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 rounded-lg text-sm font-semibold hover:scale-105 transition-transform shadow-sm">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-zinc-500 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 border-dashed">
                    No recommended items right now. Check back later!
                </div>
            @endforelse
        </div>
    </div>
</div>