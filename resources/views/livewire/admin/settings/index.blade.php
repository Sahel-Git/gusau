<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {

    public $commission_percentage;
    public $delivery_fee_per_km;
    public $min_withdrawal;
    public $payout_delay_hours;
    public $cod_enabled;

    public function mount()
    {
        $this->commission_percentage = setting('commission_percentage', 10);
        $this->delivery_fee_per_km = setting('delivery_fee_per_km', 100);
        $this->min_withdrawal = setting('min_withdrawal', 5000);
        $this->payout_delay_hours = setting('payout_delay_hours', 48);
        $this->cod_enabled = setting('cod_enabled', true);
    }

    public function save()
    {
        $settings = [
            'commission_percentage' => $this->commission_percentage,
            'delivery_fee_per_km' => $this->delivery_fee_per_km,
            'min_withdrawal' => $this->min_withdrawal,
            'payout_delay_hours' => $this->payout_delay_hours,
            'cod_enabled' => $this->cod_enabled ? 'true' : 'false',
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            \Illuminate\Support\Facades\Cache::forget("setting_{$key}"); // Clear cache
        }
        
        // Log the change
        if (function_exists('activity_log')) {
            activity_log('Settings Updated', 'Business settings were updated by admin.');
        }

        session()->flash('success', 'Settings saved successfully.');
    }

}; ?>

<div class="space-y-6 max-w-4xl mx-auto">

    <h1 class="text-2xl font-bold">Business Settings</h1>

    @if(session('success'))
        <div class="p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm">

        <!-- Commission -->
        <div>
            <label class="block text-sm font-medium mb-1">Commission (%)</label>
            <input type="number" wire:model="commission_percentage"
                class="w-full border rounded-lg p-2" />
        </div>

        <!-- Delivery Fee -->
        <div>
            <label class="block text-sm font-medium mb-1">Delivery Fee (per km)</label>
            <input type="number" wire:model="delivery_fee_per_km"
                class="w-full border rounded-lg p-2" />
        </div>

        <!-- Min Withdrawal -->
        <div>
            <label class="block text-sm font-medium mb-1">Minimum Withdrawal</label>
            <input type="number" wire:model="min_withdrawal"
                class="w-full border rounded-lg p-2" />
        </div>

        <!-- Payout Delay -->
        <div>
            <label class="block text-sm font-medium mb-1">Payout Delay (hours)</label>
            <input type="number" wire:model="payout_delay_hours"
                class="w-full border rounded-lg p-2" />
        </div>

        <!-- COD Toggle -->
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model="cod_enabled" />
            <span>Enable Cash on Delivery</span>
        </div>

        <!-- Save -->
        <button wire:click="save"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg">
            Save Settings
        </button>

    </div>
</div>