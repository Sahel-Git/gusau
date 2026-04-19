<?php

use App\Models\Listing;
use App\Models\Category;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

new #[Layout('components.layouts.vendor')] class extends Component
{
    use WithFileUploads;

    public $listings = [];
    public $categories = [];
    
    // Form fields
    public $title = '';
    public $description = '';
    public $type = 'product';
    public $price = '';
    public $stock = '';
    public $category_id = '';
    public $images = [];
    
    public $editingId = null;
    public $showModal = false;

    // We can pull the max image size from Settings (in KB for livewire)
    public $maxImageSizeKb = 2048; 

    public function mount()
    {
        $this->categories = Category::all();
        $this->refreshListings();
        
        // Settings stores MB, we convert to KB for Livewire validator
        $sizeMb = Setting::get('max_image_upload_size', 2);
        $this->maxImageSizeKb = (int)$sizeMb * 1024;
    }

    public function refreshListings()
    {
        $this->listings = auth()->user()->listings()->with('category')->latest()->get();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:product,service',
            'stock' => $this->type === 'product' ? 'required|integer|min:0' : 'nullable',
            'images' => $this->editingId ? 'nullable|array|max:3' : 'required|array|max:3|min:1',
            'images.*' => "image|max:{$this->maxImageSizeKb}",
        ]);

        $imagePaths = [];
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $imagePaths[] = $image->store('listings', 'public');
            }
        }

        if ($this->editingId) {
            $listing = auth()->user()->listings()->findOrFail($this->editingId);
            Gate::authorize('update', $listing);
            
            $listing->update([
                'category_id' => $this->category_id,
                'type' => $this->type,
                'title' => $this->title,
                'slug' => $listing->title !== $this->title ? Str::slug($this->title) . '-' . uniqid() : $listing->slug,
                'description' => $this->description,
                'price' => $this->price,
                'stock' => $this->type === 'product' ? $this->stock : null,
                'status' => 'pending', // Re-approval requested
            ]);

            if (!empty($imagePaths)) {
                 foreach ($listing->images as $oldImage) {
                     if (Storage::disk('public')->exists($oldImage)) {
                         Storage::disk('public')->delete($oldImage);
                     }
                 }
                 $listing->update(['images' => $imagePaths]);
            }
            session()->flash('success', 'Listing updated and submitted for re-approval.');
        } else {
            Listing::create([
                'store_id' => auth()->user()->store->id,
                'category_id' => $this->category_id,
                'type' => $this->type,
                'title' => $this->title,
                'slug' => Str::slug($this->title) . '-' . uniqid(),
                'description' => $this->description,
                'price' => $this->price,
                'stock' => $this->type === 'product' ? $this->stock : null,
                'status' => 'pending',
                'images' => $imagePaths,
            ]);
            session()->flash('success', 'Listing submitted for approval.');
        }

        $this->reset(['title', 'description', 'type', 'price', 'stock', 'category_id', 'images', 'showModal', 'editingId']);
        $this->refreshListings();
    }
    public function edit($id)
    {
        $listing = auth()->user()->listings()->findOrFail($id);
        Gate::authorize('update', $listing);
        
        $this->editingId = $listing->id;
        $this->type = $listing->type;
        $this->title = $listing->title;
        $this->description = $listing->description;
        $this->price = $listing->price;
        $this->stock = $listing->stock;
        $this->category_id = $listing->category_id;
        $this->images = []; 
        $this->showModal = true;
    }
    
    public function delete($id)
    {
        $listing = auth()->user()->listings()->findOrFail($id);
        Gate::authorize('delete', $listing);
        
        if (!empty($listing->images)) {
            foreach ($listing->images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }
        
        $listing->delete();
        $this->refreshListings();
    }
}; ?>

    <div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center justify-between mt-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    My Listings
                </h1>
                <p class="text-sm text-zinc-500 mt-1">Manage your storefront listings.</p>
            </div>
            
            <button wire:click="$set('showModal', true)" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
                + Add New
            </button>
        </div>

        @if (session()->has('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                {{ session('success') }}
            </div>
        @endif

        {{-- Listing Table --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200 sm:pl-6">Title</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Category</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Price</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Status</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Stock</th>
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
                                            <img src="{{ Storage::url($listing->images[0]) }}" alt="{{ $listing->title }}" class="h-10 w-10 rounded-lg object-cover bg-zinc-100">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div class="font-medium text-zinc-900 dark:text-zinc-200">{{ $listing->title }}</div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $listing->category->name ?? 'Uncategorized' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-zinc-900 dark:text-zinc-200">
                                    ${{ number_format($listing->price, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500">
                                    @if($listing->status === 'approved')
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Approved</span>
                                    @elseif($listing->status === 'pending')
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-400">Pending</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Rejected</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $listing->type === 'service' ? 'N/A' : ($listing->stock ?? 0) }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button wire:click="edit({{ $listing->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $listing->id }})" wire:confirm="Are you sure you want to delete this listing?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    You haven't added any items yet. Let's create your first listing!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Creation Modal --}}
        @if($showModal)
            <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-zinc-900/75 backdrop-blur-sm transition-opacity"></div>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div class="relative w-full transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 text-left shadow-2xl transition-all sm:max-w-2xl border border-zinc-200 dark:border-zinc-800">
                            <form wire:submit="save">
                                <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-zinc-200 dark:border-zinc-800">
                                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">
                                        {{ $editingId ? 'Edit' : 'Create New' }} Listing
                                    </h3>
                                    
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</label>
                                            <input type="text" wire:model="title" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                                            @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                                            <select wire:model.live="type" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                                                <option value="product">Physical Product</option>
                                                <option value="service">Digital Service</option>
                                            </select>
                                            @error('type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Category</label>
                                            <select wire:model="category_id" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Price ($)</label>
                                            <input type="number" step="0.01" wire:model="price" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                                            @error('price') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        @if($type === 'product')
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Stock Quantity</label>
                                                <input type="number" wire:model="stock" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                                                @error('stock') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                        
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                                            <textarea wire:model="description" rows="3" class="mt-1 block w-full rounded-lg border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent"></textarea>
                                            @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                Images {{ $editingId ? '(Upload new to replace old, max 3)' : '(Required, Max 3)' }} - {{ App\Models\Setting::get('max_image_upload_size', 2) }}MB
                                            </label>
                                            <input type="file" wire:model="images" multiple accept="image/*" class="mt-1 block w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-500/10 dark:file:text-indigo-400 focus:outline-none">
                                            
                                            <div wire:loading wire:target="images" class="text-xs text-indigo-600 mt-2">Uploading & Processing...</div>
                                            
                                            @error('images') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                                            @error('images.*') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror

                                            @if ($images)
                                                <div class="mt-4 flex gap-4">
                                                    @foreach ($images as $image)
                                                        @if (in_array($image->extension(), ['jpg', 'jpeg', 'png', 'webp']))
                                                            <div class="relative h-20 w-20 rounded-lg overflow-hidden border border-zinc-200">
                                                                <img src="{{ $image->temporaryUrl() }}" class="object-cover w-full h-full">
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-zinc-50 dark:bg-zinc-900/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50">
                                        Save & Submit
                                    </button>
                                    <button type="button" wire:click="$set('showModal', false)" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-zinc-800 px-3 py-2 text-sm font-semibold text-zinc-900 dark:text-zinc-200 shadow-sm ring-1 ring-inset ring-zinc-300 dark:ring-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 sm:mt-0 sm:w-auto">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
