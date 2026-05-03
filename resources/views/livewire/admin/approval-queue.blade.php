<?php

use App\Models\Listing;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component
{
    public function with()
    {
        return [
            'listings' => Listing::with(['vendor', 'category'])
                                 ->where('listings.status', 'pending')
                                 ->latest()
                                 ->get(),
        ];
    }

    public function approve($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['status' => 'approved']);
        
        if (function_exists('activity_log')) {
            activity_log('Listing Approved', "Listing {$listing->title} (ID: {$listing->id}) was approved.");
        }

        session()->flash('success', 'Listing approved successfully.');
    }

    public function reject($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['status' => 'rejected']);
        
        if (function_exists('activity_log')) {
            activity_log('Listing Rejected', "Listing {$listing->title} (ID: {$listing->id}) was rejected.");
        }

        session()->flash('success', 'Listing rejected successfully.');
    }
}; ?>

    <div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center justify-between mt-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Admin Approval Queue</h1>
                <p class="text-sm text-zinc-500 mt-1">Review and approve or reject pending vendor listings.</p>
            </div>
            
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-zinc-100 dark:bg-zinc-800 px-4 py-2 text-sm font-semibold text-zinc-900 dark:text-zinc-200 shadow-sm hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
                Back to Dashboard
            </a>
        </div>

        @if (session()->has('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                {{ session('success') }}
            </div>
        @endif

        {{-- Pending Listings Table --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200 sm:pl-6">Listing</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Category</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Price</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Vendor</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                        @forelse($listings as $listing)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($listing->images))
                                            <img src="{{ Storage::url($listing->images[0]) }}" alt="{{ $listing->title }}" class="h-10 w-10 rounded-lg object-cover bg-zinc-100 dark:bg-zinc-800">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-zinc-200">{{ $listing->title }}</div>
                                            <div class="text-xs text-zinc-500 max-w-xs truncate">{{ $listing->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $listing->category->name ?? 'Uncategorized' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-zinc-900 dark:text-zinc-200">
                                    ${{ number_format($listing->price, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $listing->vendor->name ?? 'Unknown Vendor' }}
                                    <div class="text-xs">{{ $listing->vendor->email ?? '' }}</div>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button wire:click="approve({{ $listing->id }})" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 mr-4 font-semibold">
                                        Approve
                                    </button>
                                    <button wire:click="reject({{ $listing->id }})" wire:confirm="Are you sure you want to reject this listing? It will be permanently deleted after 24 hours if the vendor makes no changes." class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-semibold">
                                        Reject
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    No pending listings require approval at this time. Great job!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
