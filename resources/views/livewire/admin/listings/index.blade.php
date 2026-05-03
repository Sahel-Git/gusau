<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Listing;

new #[Layout('admin.layouts.app')] class extends Component {
    use WithPagination;

    public $search = '';
    public $tab = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function approve($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['status' => 'approved']);
    }

    public function reject($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['status' => 'rejected']);
    }

    public function delete($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->delete();
    }

    public function with(): array
    {
        $query = Listing::with('store')->latest();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('listings.title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('store', function($storeQuery) {
                      $storeQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        switch ($this->tab) {
            case 'products':
                $query->where('listings.type', 'product');
                break;
            case 'services':
                $query->where('listings.type', 'service');
                break;
            case 'pending':
                $query->where('listings.status', 'pending');
                break;
            case 'approved':
                $query->where('listings.status', 'approved');
                break;
        }

        return [
            'listings' => $query->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Listings Management</h1>
    </div>

    <!-- Filters & Tabs -->
    <div class="bg-white rounded-2xl p-6 shadow-sm flex flex-col md:flex-row gap-6 justify-between items-center">
        <div class="flex space-x-2">
            @foreach(['all' => 'All', 'products' => 'Products', 'services' => 'Services', 'pending' => 'Pending', 'approved' => 'Approved'] as $key => $label)
                <button 
                    wire:click="setTab('{{ $key }}')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $tab === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="w-full md:w-1/3">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search by name or store..." 
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            >
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock / Duration</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($listings as $listing)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $listing->title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $listing->type === 'product' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($listing->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $listing->store->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₦{{ number_format($listing->price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($listing->type === 'product')
                                    Stock: {{ $listing->stock ?? 0 }}
                                @else
                                    {{ $listing->duration ? $listing->duration . ' mins' : 'N/A' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $listing->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $listing->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $listing->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($listing->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $listing->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.listings.show', $listing) }}" class="text-blue-600 hover:text-blue-900 font-medium">View</a>
                                
                                @if($listing->status !== 'approved')
                                    <button wire:click="approve({{ $listing->id }})" class="text-green-600 hover:text-green-900 font-medium">Approve</button>
                                @endif
                                
                                @if($listing->status !== 'rejected')
                                    <button wire:click="reject({{ $listing->id }})" class="text-yellow-600 hover:text-yellow-900 font-medium whitespace-nowrap">Reject</button>
                                @endif

                                <button 
                                    wire:click="delete({{ $listing->id }})" 
                                    wire:confirm="Are you sure you want to delete this listing safely?" 
                                    class="text-red-600 hover:text-red-900 font-medium"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No listings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $listings->links() }}
        </div>
    </div>
</div>
