<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Store;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {
    use WithPagination;

    public $search = '';
    public $filter = 'all'; // all, pending, active, suspended

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function setFilter($status)
    {
        $this->filter = $status;
        $this->resetPage();
    }

    public function approveStore($id)
    {
        $store = Store::findOrFail($id);
        
        if ($store->status === 'active') {
            session()->flash('error', 'Store is already active.');
            return;
        }

        $store->update(['status' => 'active']);

        if (function_exists('activity_log')) {
            activity_log('Store Approved', "Store {$store->name} (ID: {$store->id}) was approved and activated.");
        }

        session()->flash('success', "Store '{$store->name}' successfully approved.");
    }

    public function suspendStore($id)
    {
        $store = Store::findOrFail($id);
        
        if ($store->status === 'suspended') {
            session()->flash('error', 'Store is already suspended.');
            return;
        }

        $store->update(['status' => 'suspended']);

        if (function_exists('activity_log')) {
            activity_log('Store Suspended', "Store {$store->name} (ID: {$store->id}) was suspended.");
        }

        session()->flash('success', "Store '{$store->name}' successfully suspended.");
    }

    public function with()
    {
        $query = Store::with(['user']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
        }

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $stores = $query->latest()->paginate(10);
        
        // Append metrics for UI
        foreach($stores as $store) {
             $store->total_orders = \App\Models\Order::whereHas('items', function($q) use ($store) {
                 $q->where('store_id', $store->id);
             })->where('orders.status', 'completed')->count();
             
             $store->total_revenue = \App\Models\OrderItem::where('store_id', $store->id)
                 ->whereHas('order', function($q){
                     $q->where('orders.status', 'completed');
                 })->sum(\Illuminate\Support\Facades\DB::raw('price * quantity'));
        }

        return [
            'stores' => $stores,
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mt-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Store Governance</h1>
            <p class="text-sm text-zinc-500 mt-1">Manage vendor business entities and listings exposure globally.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full rounded-lg border-zinc-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm" placeholder="Search stores (name/owner)...">
            </div>
        </div>
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

    <div class="flex gap-2 flex-wrap">
        <button wire:click="setFilter('all')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'all' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">All Stores</button>
        <button wire:click="setFilter('pending')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'pending' ? 'bg-amber-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">Pending Review</button>
        <button wire:click="setFilter('active')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'active' ? 'bg-emerald-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">Active</button>
        <button wire:click="setFilter('suspended')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'suspended' ? 'bg-red-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">Suspended</button>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden relative">
        <div wire:loading wire:target="search, setFilter" class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="h-6 w-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Store Name</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Owner</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Status</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Orders</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Revenue</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Appealed</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                    @forelse($stores as $store)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        @if($store->logo_path)
                                            <img src="{{ Storage::url($store->logo_path) }}" class="h-10 w-10 rounded-lg object-cover bg-zinc-100 dark:bg-zinc-800" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold uppercase shadow-sm">
                                                {{ substr($store->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $store->name }}</div>
                                        <div class="flex items-center gap-1 text-xs text-zinc-500">
                                            <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="hover:text-indigo-600 transition-colors break-all">/store/{{ $store->slug }}</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                <div class="font-medium text-zinc-900 dark:text-zinc-200">{{ optional($store->user)->name ?? 'Deleted User' }}</div>
                                <div class="text-xs">{{ optional($store->user)->email ?? '' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($store->status === 'active')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Active</span>
                                @elseif($store->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-400 animate-pulse">Pending Review</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Suspended</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $store->total_orders }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-teal-600 dark:text-teal-400">
                                ₦{{ number_format($store->total_revenue, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $store->created_at->format('M d, Y') }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a wire:navigate href="{{ route('admin.stores.show', $store->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">Manage</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                                No stores found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stores->hasPages())
            <div class="border-t border-zinc-200 dark:border-zinc-800 px-6 py-4">
                {{ $stores->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
</div>
