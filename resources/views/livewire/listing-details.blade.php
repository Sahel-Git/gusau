<?php

use App\Models\Listing;
use App\Actions\CreateOrder;
use Illuminate\Validation\ValidationException;
use function Livewire\Volt\{state, mount, layout};

layout('components.layouts.app');

state(['listing', 'quantity' => 1, 'processing' => false]);

mount(function (Listing $listing) {
    // Only approved listings should be viewable publicly
    if ($listing->status !== 'approved') {
        abort(404);
    }
    $this->listing = $listing->load(['store', 'category']);
});

$checkout = function (CreateOrder $createOrder) {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // Prevent vendors from buying their own items
    if (auth()->user()->store && auth()->user()->store->id === $this->listing->store_id) {
        session()->flash('error', 'You cannot purchase your own items.');
        return;
    }

    $this->validate([
        'quantity' => 'required|integer|min:1' . ($this->listing->type === 'product' ? '|max:' . $this->listing->stock : ''),
    ]);

    $this->processing = true;

    try {
        $order = $createOrder->execute(auth()->user(), $this->listing->id, $this->quantity);
        session()->flash('success', 'Order #' . $order->id . ' placed successfully!');
        
        // In a real app, redirect to a checkout/payment gateway here
        $this->redirect(route('dashboard'));
    } catch (ValidationException $e) {
        $this->setErrorBag($e->validator->getMessageBag());
    } catch (\Exception $e) {
        session()->flash('error', $e->getMessage());
    }

    $this->processing = false;
};
?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-12">
    <div class="max-w-7xl mx-auto px-6">
        
        @if (session()->has('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mb-6 rounded-xl bg-red-50 p-4 text-red-800 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            {{-- Image Gallery --}}
            <div class="flex flex-col gap-4">
                <div class="aspect-square bg-zinc-200 dark:bg-zinc-800 rounded-3xl overflow-hidden shadow-sm relative">
                    @if(!empty($listing->images))
                        <img src="{{ Storage::url($listing->images[0]) }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-zinc-400">No Image</div>
                    @endif
                    
                    <div class="absolute top-4 left-4 bg-white/90 dark:bg-zinc-900/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ $listing->type }}
                    </div>
                </div>
            </div>

            {{-- Details & Checkout --}}
            <div class="flex flex-col">
                <div class="mb-6 flex items-center gap-3">
                    <a href="{{ route('store.show', $listing->store->slug) }}" class="flex items-center gap-2 group hover:opacity-80 transition">
                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600 text-xs shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition">
                            {{ substr($listing->store->name, 0, 1) }}
                        </div>
                        <span class="font-medium text-zinc-600 dark:text-zinc-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $listing->store->name }}</span>
                    </a>
                    <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
                    <span class="text-sm text-zinc-500">{{ $listing->category->name }}</span>
                </div>

                <h1 class="text-4xl font-extrabold text-zinc-900 dark:text-white tracking-tight text-balance mb-4">
                    {{ $listing->title }}
                </h1>

                <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mb-8 border-b border-zinc-200 dark:border-zinc-800 pb-8">
                    ${{ number_format($listing->price, 2) }}
                </div>

                <div class="prose prose-zinc dark:prose-invert text-zinc-600 dark:text-zinc-400 mb-10 leading-relaxed text-balance">
                    {{ $listing->description }}
                </div>
                
                {{-- Action Area --}}
                <div class="mt-auto bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
                    @if($listing->type === 'product' && $listing->stock === 0)
                        <div class="text-center py-4 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold rounded-2xl">
                            Out of Stock
                        </div>
                    @else
                        <form wire:submit="checkout" class="flex flex-col sm:flex-row gap-4">
                            @if($listing->type === 'product')
                                <div class="w-full sm:w-32 flex-shrink-0 relative">
                                    <label class="absolute -top-2 left-3 bg-white dark:bg-zinc-900 px-1 text-xs font-medium text-zinc-500">Qty ({{ $listing->stock }} available)</label>
                                    <input type="number" wire:model.live="quantity" min="1" max="{{ $listing->stock }}" class="w-full h-14 rounded-2xl border-zinc-200 dark:border-zinc-700 bg-transparent text-center text-lg font-bold shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                </div>
                            @endif

                            <button type="submit" wire:loading.attr="disabled" class="w-full h-14 rounded-2xl bg-indigo-600 text-white font-bold text-lg shadow-lg shadow-indigo-600/20 hover:bg-indigo-500 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2">
                                <span wire:loading.remove>
                                    <svg class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    Purchase Now
                                </span>
                                <span wire:loading>Processing Securely...</span>
                            </button>
                        </form>
                        @error('quantity') <span class="text-xs text-red-500 font-medium block mt-2">{{ $message }}</span> @enderror
                        <p class="text-xs text-center text-zinc-400 mt-4 flex items-center justify-center gap-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"/></svg>
                            Secure encrypted checkout
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
