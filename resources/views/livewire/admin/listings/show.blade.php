<?php

use Livewire\Volt\Component;
use App\Models\Listing;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {
    public Listing $listing;

    public function mount(Listing $listing)
    {
        $this->listing = $listing->load('store', 'category');
    }
}; ?>

<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Listing Details</h1>
        <a href="{{ route('admin.listings.index') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">&larr; Back to Listings</a>
    </div>

    <!-- Details Card -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 pb-6 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $listing->title }}</h2>
                <div class="mt-2 flex items-center space-x-4">
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $listing->type === 'product' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ ucfirst($listing->type) }}
                    </span>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $listing->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $listing->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $listing->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                    ">
                        {{ ucfirst($listing->status) }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Price</div>
                <div class="text-2xl font-bold text-gray-900">₦{{ number_format($listing->price, 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Store Information</h3>
                <div class="bg-gray-50 p-4 rounded-xl">
                    <p class="font-medium text-gray-900">{{ $listing->store->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500 mt-1">Category: {{ $listing->category->name ?? 'N/A' }}</p>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Inventory Details</h3>
                <div class="bg-gray-50 p-4 rounded-xl">
                    @if($listing->type === 'product')
                        <p class="font-medium text-gray-900">Stock Available: <span class="text-blue-600">{{ $listing->stock ?? 0 }}</span></p>
                    @else
                        <p class="font-medium text-gray-900">Duration: <span class="text-purple-600">{{ $listing->duration ? $listing->duration . ' mins' : 'Not specified' }}</span></p>
                    @endif
                    <p class="text-sm text-gray-500 mt-1">Created: {{ $listing->created_at->format('F j, Y, g:i a') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-100 pt-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Description</h3>
            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($listing->description)) !!}
            </div>
        </div>
    </div>
</div>
