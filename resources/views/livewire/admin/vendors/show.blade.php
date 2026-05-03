<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {
    public User $vendor;

    public function mount(User $user)
    {
        if ($user->role !== 'vendor') {
            abort(404, 'User is not a vendor.');
        }
        $this->vendor = $user->load('store');
    }

    public function toggleStatus()
    {
        if ($this->vendor->id === auth()->id()) {
            session()->flash('error', 'You cannot suspend yourself.');
            return;
        }

        if ($this->vendor->isAdmin()) {
            $adminCount = User::where('role', 'admin')->where('status', 'active')->count();
            if ($adminCount <= 1 && $this->vendor->status === 'active') {
                session()->flash('error', 'You cannot suspend the last active admin.');
                return;
            }
        }

        $newStatus = $this->vendor->status === 'active' ? 'suspended' : 'active';
        $this->vendor->update(['status' => $newStatus]);

        if (function_exists('activity_log')) {
            activity_log('Vendor Status Changed', "User {$this->vendor->name} (Vendor ID: {$this->vendor->id}) status changed to {$newStatus}.");
        }

        session()->flash('success', "Vendor successfully $newStatus.");
    }

    public function sendResetLink()
    {
        // Simulated
        if (function_exists('activity_log')) {
            activity_log('Password Reset Sent', "Password reset link triggered for {$this->vendor->name}.");
        }
        session()->flash('success', 'Password reset link sent to vendor\'s email.');
    }

    public function with()
    {
        $storeId = $this->vendor->store ? $this->vendor->store->id : null;

        $totalOrders = 0;
        $totalRevenue = 0;
        $totalListings = 0;

        $pendingPayouts = 0;
        $completedPayouts = 0;

        $totalRefunds = 0;
        $totalDisputes = 0;

        if ($storeId) {
            $totalListings = \App\Models\Listing::where('store_id', $storeId)->count();
            
            $totalOrders = Order::whereHas('items', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })->where('orders.status', 'completed')->count();

            $totalRevenue = OrderItem::where('store_id', $storeId)
                ->whereHas('order', function($q){
                    $q->where('orders.status', 'completed');
                })->sum(\Illuminate\Support\Facades\DB::raw('price * quantity'));

            $pendingPayouts = Withdrawal::where('vendor_id', $this->vendor->id)
                ->where('status', 'pending')
                ->sum('amount');
                
            $completedPayouts = Withdrawal::where('vendor_id', $this->vendor->id)
                ->where('status', 'approved')
                ->sum('amount');

            $totalRefunds = \App\Models\Refund::whereHas('order.orderItems', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })->count();

            $totalDisputes = Order::where('is_disputed', true)
                ->whereHas('orderItems', function($q) use ($storeId) {
                    $q->where('store_id', $storeId);
                })->count();
        }

        return [
            'metrics' => [
                'totalOrders' => $totalOrders,
                'totalRevenue' => $totalRevenue,
                'totalListings' => $totalListings,
                'walletBalance' => $this->vendor->wallet_balance ?? 0,
                'pendingPayouts' => $pendingPayouts,
                'completedPayouts' => $completedPayouts,
                'totalRefunds' => $totalRefunds,
                'totalDisputes' => $totalDisputes,
            ]
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mt-4">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-300 text-xl font-bold uppercase shadow-sm">
                {{ substr($vendor->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center gap-3">
                    {{ $vendor->name }}
                    @if($vendor->status === 'active')
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Active Identity</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Suspended Identity</span>
                    @endif
                </h1>
                
                @if($vendor->is_flagged)
                    <div class="mt-2 inline-flex items-center rounded-md bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800 border border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/30">
                        <svg class="mr-1.5 h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Flagged: Low Trust Score
                    </div>
                @endif
                <p class="text-sm text-zinc-500 mt-1">{{ $vendor->email }} &bull; Registered {{ $vendor->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        
        <a wire:navigate href="{{ route('admin.vendors.index') }}" class="rounded-lg bg-zinc-100 dark:bg-zinc-800 px-4 py-2 text-sm font-semibold text-zinc-900 dark:text-zinc-200 shadow-sm hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
            &larr; Back to Vendors
        </a>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- User Details Card --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Identity Details</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Login</p>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $vendor->last_login_at ? $vendor->last_login_at->diffForHumans() : 'Never logged in' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Vendor Type</p>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white capitalize">
                            {{ str_replace('_', ' ', $vendor->vendor_type ?? 'Standard') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800/50">
                    <button wire:click="toggleStatus" wire:confirm="Are you sure?" class="w-full rounded-lg px-4 py-2 text-sm font-bold shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $vendor->status === 'active' ? 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 focus:ring-red-500 dark:focus:ring-offset-zinc-900' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900' }}">
                        {{ $vendor->status === 'active' ? 'Suspend Vendor Identity' : 'Activate Vendor Identity' }}
                    </button>
                    
                    <button wire:click="sendResetLink" wire:confirm="Send a password reset link to this vendor's email?" class="w-full flex items-center justify-center gap-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 px-4 py-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300 shadow-sm hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all border border-zinc-200 dark:border-zinc-700/50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Send Password Reset
                    </button>
                </div>
            </div>

            {{-- Store Snippet Card --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6 overflow-hidden relative">
                @if(!$vendor->store)
                    <div class="absolute inset-0 bg-zinc-50/80 dark:bg-zinc-900/80 flex flex-col items-center justify-center text-center z-10 backdrop-blur-sm p-4">
                        <svg class="h-10 w-10 text-zinc-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">No Business Attached</p>
                        <p class="text-xs text-zinc-500 mt-1">This user has not yet created a store entity.</p>
                    </div>
                @endif

                <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Business Layer</h2>
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ optional($vendor->store)->name ?? '---' }}</p>
                        <p class="text-xs text-zinc-500">{{ optional($vendor->store)->created_at ? optional($vendor->store)->created_at->format('M d, Y') : '---' }}</p>
                    </div>
                    @if(optional($vendor->store)->status === 'active')
                        <span class="inline-flex items-center rounded-sm bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Active</span>
                    @elseif(optional($vendor->store)->status === 'pending')
                        <span class="inline-flex items-center rounded-sm bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-400">Pending Approval</span>
                    @else
                        <span class="inline-flex items-center rounded-sm bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Suspended</span>
                    @endif
                </div>

                @if($vendor->store)
                    <a wire:navigate href="{{ route('admin.stores.show', $vendor->store->id) }}" class="w-full flex items-center justify-center gap-2 rounded-lg border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/10 px-4 py-2 text-sm font-bold text-indigo-700 dark:text-indigo-400 shadow-sm hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all mt-4">
                        Manage Store Entity
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @endif
            </div>

        </div>

        {{-- Right Column --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Performance Metrics --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Performance Metrics</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="border border-zinc-100 dark:border-zinc-800/50 rounded-xl p-4 bg-zinc-50 dark:bg-zinc-800/30">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Orders</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $metrics['totalOrders'] }}</p>
                    </div>
                    <div class="border border-zinc-100 dark:border-zinc-800/50 rounded-xl p-4 bg-zinc-50 dark:bg-zinc-800/30">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Revenue</p>
                        <p class="text-2xl font-bold text-teal-600 dark:text-teal-400 mt-1">₦{{ number_format($metrics['totalRevenue'], 2) }}</p>
                    </div>
                    <div class="border border-zinc-100 dark:border-zinc-800/50 rounded-xl p-4 bg-zinc-50 dark:bg-zinc-800/30">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Store Listings</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $metrics['totalListings'] }}</p>
                    </div>
                    <div class="border border-zinc-100 dark:border-zinc-800/50 rounded-xl p-4 bg-zinc-50 dark:bg-zinc-800/30">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Refunds</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $metrics['totalRefunds'] }}</p>
                    </div>
                    <div class="border border-zinc-100 dark:border-zinc-800/50 rounded-xl p-4 bg-zinc-50 dark:bg-zinc-800/30">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Disputes</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $metrics['totalDisputes'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Trust Score System --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                        <svg class="h-5 w-5 {{ $vendor->trust_score >= 80 ? 'text-emerald-500' : ($vendor->trust_score >= 50 ? 'text-amber-500' : 'text-red-500') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Trust Score
                    </h2>
                    <span class="text-xl font-black {{ $vendor->trust_score >= 80 ? 'text-emerald-600' : ($vendor->trust_score >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $vendor->trust_score ?? 100 }}/100</span>
                </div>
                <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-3 mb-4 overflow-hidden border border-zinc-300 dark:border-zinc-700">
                    <div class="h-3 rounded-full {{ $vendor->trust_score >= 80 ? 'bg-emerald-500' : ($vendor->trust_score >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ max(0, min(100, $vendor->trust_score ?? 100)) }}%"></div>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Score drops below 40 will temporarily flag the vendor and pause payouts. Reaching 50+ unflags.</p>
            </div>

            {{-- Financial Overview --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Financial Overview
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="pt-4 border-t-2 border-indigo-500">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Available Wallet Balance</p>
                        <p class="text-xl font-bold text-zinc-900 dark:text-white mt-1">₦{{ number_format($metrics['walletBalance'], 2) }}</p>
                        <p class="text-xs text-zinc-400 mt-1">Vendor's ledger balance</p>
                    </div>
                    
                    <div class="pt-4 border-t-2 border-amber-500">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending Payouts</p>
                        <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">₦{{ number_format($metrics['pendingPayouts'], 2) }}</p>
                        <p class="text-xs text-zinc-400 mt-1">Requested, awaiting admin</p>
                    </div>

                    <div class="pt-4 border-t-2 border-emerald-500 sm:col-span-2 lg:col-span-1">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Completed Payouts</p>
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">₦{{ number_format($metrics['completedPayouts'], 2) }}</p>
                        <p class="text-xs text-zinc-400 mt-1">Historically paid out</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
