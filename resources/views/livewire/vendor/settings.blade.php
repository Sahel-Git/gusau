<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.vendor')] class extends Component {
    public bool $isEditing = false;

    public bool $is_active = true;
    public bool $notifications_enabled = true;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $store = Auth::user()->store;
        if ($store) {
            $this->is_active = (bool)($store->is_active ?? true);
            $this->notifications_enabled = (bool)($store->notifications_enabled ?? true);
        }
    }

    public function toggleEdit()
    {
        if ($this->isEditing) {
            $this->loadData();
        }
        $this->isEditing = !$this->isEditing;
    }

    public function save()
    {
        $this->validate([
            'is_active' => 'boolean',
            'notifications_enabled' => 'boolean',
        ]);

        $user = Auth::user();
        $store = $user->store ?? $user->store()->make();
        
        $store->is_active = $this->is_active;
        $store->notifications_enabled = $this->notifications_enabled;
        $store->save();
        
        $user->load('store');
        $this->loadData();

        session()->flash('success_controls', 'Store controls updated successfully.');
        $this->isEditing = false;
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mt-4 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Store Settings</h1>
            <p class="text-sm text-zinc-500 mt-1">Manage your centralized configuration here.</p>
        </div>
        <a wire:navigate href="{{ route('vendor.dashboard') }}" class="text-sm font-medium text-teal-600 hover:text-teal-500 whitespace-nowrap">&larr; Back to Dashboard</a>
    </div>

    <div class="flex flex-col gap-6">
        {{-- Section 1: Store Profile --}}
        <livewire:vendor.store-profile />

        {{-- Section 2: Bank Details --}}
        <livewire:vendor.payout-settings />

        {{-- Section 3: Store Controls --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden mb-6">
            <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Store Controls</h2>
                    <p class="text-sm text-zinc-500 mt-1">Manage your store's behavior and operational status.</p>
                </div>
                <button wire:click="toggleEdit" class="text-sm font-semibold text-teal-600 hover:text-teal-500 transition-colors">
                    {{ $isEditing ? 'Cancel' : 'Edit' }}
                </button>
            </div>

            @if (session()->has('success_controls'))
                <div class="px-6 pt-4">
                    <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                        {{ session('success_controls') }}
                    </div>
                </div>
            @endif

            <div class="p-6">
                @if(!$isEditing)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Store Status</span>
                            <span class="text-zinc-900 dark:text-white font-medium">
                                @if($is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Order Notifications</span>
                            <span class="text-zinc-900 dark:text-white font-medium">
                                {{ $notifications_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                @else
                    <form wire:submit="save" class="flex flex-col gap-6">
                        <!-- Store Status Toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-white">Store Status</h3>
                                <p class="text-sm text-zinc-500 mt-1">Turn off if you're taking a break. Products won't be shown.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-teal-300 dark:peer-focus:ring-teal-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-teal-600"></div>
                            </label>
                        </div>

                        <div class="w-full h-px bg-zinc-200 dark:bg-zinc-800/50"></div>

                        <!-- Notifications Toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-white">Order Notifications</h3>
                                <p class="text-sm text-zinc-500 mt-1">Receive alerts when new orders are placed.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="notifications_enabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-teal-300 dark:peer-focus:ring-teal-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-teal-600"></div>
                            </label>
                        </div>

                        <div class="mt-4 flex justify-end gap-3">
                            <button type="button" wire:click="toggleEdit" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="rounded-lg bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 transition-all">
                                Save Controls
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
