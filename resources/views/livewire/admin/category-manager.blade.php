<?php

use App\Models\Category;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

new #[Layout('admin.layouts.app')] class extends Component {
    public $name;
    public $description;

    public function with()
    {
        return [
            'categories' => Category::latest()->get(),
        ];
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
        ]);

        $this->reset(['name', 'description']);
        session()->flash('success', 'Category created successfully.');
    }
    
    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        session()->flash('success', 'Category deleted.');
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Category Management</h1>
            <p class="text-sm text-zinc-500 mt-1">Manage marketplace categories for vendor listings.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        
        {{-- Create Category Form --}}
        <div class="lg:col-span-1 border dark:border-zinc-800/50 bg-white dark:bg-zinc-900 rounded-2xl p-6 shadow-sm h-min">
            <h2 class="text-lg font-bold mb-4">Add New Category</h2>
            <form wire:submit.prevent="store" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Category Name</label>
                    <input type="text" name="name" wire:model="name" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">
                    @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" wire:model="description" rows="6" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                    @error('description') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                
                <button type="submit" class="w-full rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 font-bold shadow-sm hover:scale-[1.02] transition-transform">
                    Create Category
                </button>
            </form>
        </div>

        {{-- Categories List --}}
        <div class="lg:col-span-2 border dark:border-zinc-800/50 bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                    @forelse($categories as $category)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-zinc-900 dark:text-zinc-200">{{ $category->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $category->slug }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 max-w-xs">{{ $category->description ?? 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                No categories found. Create your first marketplace category!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>
