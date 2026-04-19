<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public bool $isEditing = false;
    
    public string $bank_name = '';
    public string $account_number = '';
    public string $account_name = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $store = Auth::user()->store;
        if ($store) {
            $this->bank_name = $store->bank_name ?? '';
            $this->account_number = $store->account_number ?? '';
            $this->account_name = $store->account_name ?? '';
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
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $store = $user->store ?? $user->store()->make();

        $store->bank_name = $this->bank_name;
        $store->account_number = $this->account_number;
        $store->account_name = $this->account_name;
        $store->save();
        
        $user->load('store');
        $this->loadData();
        
        session()->flash('success_payout', 'Payout settings updated successfully.');
        $this->isEditing = false;
    }
}; ?>

<div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden mb-6">
    <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Bank Details</h2>
            <p class="text-sm text-zinc-500 mt-1">Configure your bank details to receive payments.</p>
        </div>
        <button wire:click="toggleEdit" class="text-sm font-semibold text-teal-600 hover:text-teal-500 transition-colors">
            {{ $isEditing ? 'Cancel' : 'Edit' }}
        </button>
    </div>

    @if (session()->has('success_payout'))
        <div class="px-6 pt-4">
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                {{ session('success_payout') }}
            </div>
        </div>
    @endif

    <div class="p-6">
        @if(!$isEditing)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Bank Name</span>
                    <span class="text-zinc-900 dark:text-white font-medium">{{ $bank_name ?: 'Not set' }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Account Number</span>
                    <span class="text-zinc-900 dark:text-white font-medium">{{ $account_number ?: 'Not set' }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Account Name</span>
                    <span class="text-zinc-900 dark:text-white font-medium">{{ $account_name ?: 'Not set' }}</span>
                </div>
            </div>
        @else
            <form wire:submit="save" class="flex flex-col gap-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Bank Name</label>
                    <input type="text" wire:model="bank_name" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent" placeholder="e.g. Guarantee Trust Bank">
                    @error('bank_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Account Number</label>
                    <input type="text" wire:model="account_number" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent" placeholder="10 digit account number">
                    @error('account_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Account Name</label>
                    <input type="text" wire:model="account_name" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent" placeholder="Exact name on the account">
                    @error('account_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="mt-4 flex justify-end gap-3">
                    <button type="button" wire:click="toggleEdit" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-lg bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 transition-all">
                        Save Details
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
