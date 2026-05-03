<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {
    use WithPagination;

    public $search = '';
    public $filter = 'all'; // all, active, suspended

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

    public function toggleStatus($id)
    {
        $vendor = User::findOrFail($id);
        
        if ($vendor->id === auth()->id()) {
            session()->flash('error', 'You cannot suspend yourself.');
            return;
        }

        if ($vendor->isAdmin()) {
            $adminCount = User::where('role', 'admin')->where('status', 'active')->count();
            if ($adminCount <= 1 && $vendor->status === 'active') {
                session()->flash('error', 'You cannot suspend the last active admin.');
                return;
            }
            if ($vendor->id !== auth()->id()) {
               // Technically modifying another admin, which is fine unless it's the last one.
            }
        }

        $newStatus = $vendor->status === 'active' ? 'suspended' : 'active';
        $vendor->update(['status' => $newStatus]);

        if (function_exists('activity_log')) {
            activity_log('Vendor Status Changed', "User {$vendor->name} (Vendor ID: {$vendor->id}) status changed to {$newStatus}.");
        }

        session()->flash('success', "Vendor successfully $newStatus.");
    }

    public function with()
    {
        $query = User::with(['store'])->where('role', 'vendor');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter === 'active') {
            $query->where('status', 'active');
        } elseif ($this->filter === 'suspended') {
            $query->where('status', 'suspended');
        }

        $vendors = $query->latest()->paginate(10);
        
        // Prepare some aggregations if needed
        foreach($vendors as $vendor) {
             if ($vendor->store) {
                 // For performance in a real app this would be a subquery, but we append basic counts for the UI list
                 $vendor->total_orders = \App\Models\Order::whereHas('items', function($q) use ($vendor) {
                     $q->where('store_id', $vendor->store->id);
                 })->where('orders.status', 'completed')->count();
                 
                 $vendor->total_revenue = \App\Models\OrderItem::where('store_id', $vendor->store->id)
                     ->whereHas('order', function($q){
                         $q->where('orders.status', 'completed');
                     })->sum(\Illuminate\Support\Facades\DB::raw('price * quantity'));
             } else {
                 $vendor->total_orders = 0;
                 $vendor->total_revenue = 0;
             }
        }

        return [
            'vendors' => $vendors,
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mt-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Vendor Management</h1>
            <p class="text-sm text-zinc-500 mt-1">Control marketplace sellers and their platform access.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full rounded-lg border-zinc-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm" placeholder="Search vendors (name/email)...">
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

    <div class="flex gap-2">
        <button wire:click="setFilter('all')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'all' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">All</button>
        <button wire:click="setFilter('active')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'active' ? 'bg-emerald-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">Active Users</button>
        <button wire:click="setFilter('suspended')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $filter === 'suspended' ? 'bg-red-600 text-white' : 'bg-white text-zinc-700 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700' }} transition-colors">Suspended Users</button>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden relative">
        <div wire:loading wire:target="search, setFilter" class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="h-6 w-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Vendor / Identity</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Status</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Store Context</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Orders</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Revenue Generated</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                    @forelse($vendors as $vendor)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold uppercase">
                                            {{ substr($vendor->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $vendor->name }}</div>
                                        <div class="text-xs text-zinc-500">{{ $vendor->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($vendor->status === 'active')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Suspended</span>
                                @endif
                                <div class="text-[10px] text-zinc-400 mt-1">Identity Layer</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($vendor->store)
                                    <div class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $vendor->store->name }}</div>
                                    @if($vendor->store->status === 'active')
                                        <span class="inline-flex items-center rounded-sm bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Active</span>
                                    @elseif($vendor->store->status === 'pending')
                                        <span class="inline-flex items-center rounded-sm bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-400">Pending Approval</span>
                                    @else
                                        <span class="inline-flex items-center rounded-sm bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Suspended</span>
                                    @endif
                                @else
                                    <span class="text-xs italic text-zinc-400">No Store Configured</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $vendor->total_orders }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-zinc-900 dark:text-zinc-300">
                                ₦{{ number_format($vendor->total_revenue, 2) }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    @if($vendor->store)
                                        <a wire:navigate href="{{ route('admin.stores.show', $vendor->store->id) }}" class="text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200" title="View Store">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>    
                                        </a>
                                    @endif
                                    
                                    <a wire:navigate href="{{ route('admin.vendors.show', $vendor->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Profile</a>
                                    
                                    @if($vendor->status === 'active')
                                        <button wire:click="toggleStatus({{ $vendor->id }})" wire:confirm="Are you sure you want to suspend this user? This will instantly revoke their login capability." class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Suspend</button>
                                    @else
                                        <button wire:click="toggleStatus({{ $vendor->id }})" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300">Activate</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                                No vendors found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
            <div class="border-t border-zinc-200 dark:border-zinc-800 px-6 py-4">
                {{ $vendors->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
</div>
