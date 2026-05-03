<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function toggleSuspend($id)
    {
        $user = User::findOrFail($id);
        if ($user->isAdmin()) return;

        $user->status = $user->status === 'active' ? 'suspended' : 'active';
        $user->save();
        
        if (function_exists('activity_log')) {
            activity_log('User Status Changed', "Status of {$user->email} changed to {$user->status}");
        }

        session()->flash('status', 'User status updated successfully.');
    }

    public function with(): array
    {
        $query = User::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            });

        if ($this->filter === 'buyers') {
            $query->where('role', 'user');
        } elseif ($this->filter === 'vendors') {
            $query->where('role', 'vendor')->where(function($q) {
                $q->whereNull('vendor_type')->orWhere('vendor_type', 'product_seller');
            });
        } elseif ($this->filter === 'providers') {
            $query->where('role', 'vendor')->where('vendor_type', 'service_provider');
        }

        return [
            'users' => $query->latest()->paginate(10)
        ];
    }
}; ?>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm ring-1 ring-zinc-200 dark:border dark:border-zinc-800/50 p-6 mb-6 mt-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">User Management</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Manage buyers, vendors, and service providers.</p>
            </div>
        </div>

        @if (session()->has('status'))
            <div class="mt-4 p-4 rounded-lg bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 border border-green-200 dark:border-green-500/20">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filters & Search --}}
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex space-x-2 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
                <button wire:click="$set('filter', 'all')" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">All Users</button>
                <button wire:click="$set('filter', 'buyers')" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'buyers' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">Buyers</button>
                <button wire:click="$set('filter', 'vendors')" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'vendors' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">Vendors</button>
                <button wire:click="$set('filter', 'providers')" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'providers' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">Providers</button>
            </div>

            <div class="w-full sm:w-64">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search users..." />
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm ring-1 ring-zinc-200 dark:border dark:border-zinc-800/50 mb-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400">
                    <tr>
                        <th class="px-6 py-4 font-medium">Name</th>
                        <th class="px-6 py-4 font-medium">Email</th>
                        <th class="px-6 py-4 font-medium">Role</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium">Joined</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">Admin</span>
                                @elseif($user->role === 'vendor' && $user->vendor_type === 'service_provider')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-400">Provider</span>
                                @elseif($user->role === 'vendor')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400">Vendor</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-800 dark:bg-zinc-700/50 dark:text-zinc-300">Buyer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                        Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 flex justify-end">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium" wire:navigate>View</a>
                                
                                @if(!$user->isAdmin())
                                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                    <button wire:click="toggleSuspend({{ $user->id }})" wire:confirm="Are you sure you want to change this user's status?" class="text-sm font-medium {{ $user->status === 'active' ? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300' : 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300' }}">
                                        {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.users class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                    <p class="text-base font-medium">No users found.</p>
                                    <p class="text-sm mt-1">Try adjusting your search or filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800/50">
            {{ $users->links() }}
        </div>
    </div>
</div>
