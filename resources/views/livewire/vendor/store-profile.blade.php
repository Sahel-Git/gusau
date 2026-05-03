<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new #[Layout('vendor.layouts.app')] class extends Component {
    use WithFileUploads;

    public bool $isEditing = false;
    
    public string $name = '';
    public string $bio = '';
    public string $address = '';
    public string $contact = '';
    public $new_logo;
    public string $logo_path = '';
    public $new_banner;
    public string $banner_path = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $store = Auth::user()->store;
        if ($store) {
            $this->name = $store->name ?? '';
            $this->bio = $store->bio ?? '';
            $this->address = $store->address ?? '';
            $this->contact = $store->contact ?? '';
            $this->logo_path = $store->logo_path ?? '';
            $this->banner_path = $store->banner_path ?? '';
        }
    }

    public function toggleEdit()
    {
        if ($this->isEditing) {
            $this->loadData(); // Reset data on cancel
            $this->new_logo = null;
            $this->new_banner = null;
        }
        $this->isEditing = !$this->isEditing;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'bio' => 'required|string',
            'address' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'new_logo' => 'nullable|image|max:2048',
            'new_banner' => 'nullable|image|max:4096',
        ]);

        $user = Auth::user();
        $store = $user->store ?? $user->store()->make();

        if ($this->new_logo) {
            $store->logo_path = $this->new_logo->store('store-logos', 'public');
        }
        if ($this->new_banner) {
            $store->banner_path = $this->new_banner->store('store-banners', 'public');
        }

        $store->name = $this->name;
        $store->bio = $this->bio;
        $store->address = $this->address;
        $store->contact = $this->contact;
        if (empty($store->slug) && !empty($this->name)) {
            $store->slug = \Illuminate\Support\Str::slug($this->name);
        }
        
        $store->save();
        $user->load('store');
        $this->loadData();

        session()->flash('success_profile', 'Store profile updated successfully.');

        $this->isEditing = false;
        $this->new_logo = null;
        $this->new_banner = null;
    }
}; ?>

<div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden mb-6">
    <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Store Profile</h2>
            <p class="text-sm text-zinc-500 mt-1">Manage your public store display details.</p>
        </div>
        <button wire:click="toggleEdit" class="text-sm font-semibold text-teal-600 hover:text-teal-500 transition-colors">
            {{ $isEditing ? 'Cancel' : 'Edit' }}
        </button>
    </div>

    @if (session()->has('success_profile'))
        <div class="px-6 pt-4">
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                {{ session('success_profile') }}
            </div>
        </div>
    @endif

    <div class="p-6">
        @if(!$isEditing)
            <div class="flex flex-col gap-6">
                <!-- Banner and Logo Visual -->
                <div class="relative w-full h-32 sm:h-48 rounded-xl overflow-hidden bg-zinc-200 dark:bg-zinc-800 mb-8 border border-zinc-200 dark:border-zinc-700">
                    @if($banner_path)
                        <img src="{{ Storage::url($banner_path) }}" class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-teal-500/10 opacity-50"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-zinc-400 text-sm">No Banner</div>
                    @endif
                    
                    <div class="absolute -bottom-1 left-4 sm:left-6 flex items-end">
                        <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-xl translate-y-1/2 bg-white dark:bg-zinc-900 overflow-hidden flex items-center justify-center border-4 border-white dark:border-zinc-900 shadow-md">
                            @if($logo_path)
                                <img src="{{ Storage::url($logo_path) }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Store Name</span>
                        <span class="text-zinc-900 dark:text-white font-medium">{{ $name ?: 'Not set' }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Store Contact</span>
                        <span class="text-zinc-900 dark:text-white font-medium">{{ $contact ?: 'Not set' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Address</span>
                        <span class="text-zinc-900 dark:text-white font-medium">{{ $address ?: 'Not set' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Store Bio</span>
                        <span class="text-zinc-900 dark:text-white font-medium whitespace-pre-wrap">{{ $bio ?: 'Not set' }}</span>
                    </div>
                </div>
            </div>
        @else
            <form wire:submit="save" class="flex flex-col gap-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Store Banner</label>
                    <div class="flex items-center gap-4">
                        <div class="h-20 w-32 rounded-xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden flex-shrink-0 flex items-center justify-center border border-zinc-200 dark:border-zinc-700">
                            @if ($new_banner)
                                <img src="{{ $new_banner->temporaryUrl() }}" class="h-full w-full object-cover">
                            @elseif($banner_path)
                                <img src="{{ Storage::url($banner_path) }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="new_banner" accept="image/png, image/jpeg" class="block w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 dark:file:bg-teal-900/30 dark:file:text-teal-400">
                            <p class="text-xs text-zinc-500 mt-2">PNG or JPG up to 4MB.</p>
                            @error('new_banner') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Store Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="h-20 w-20 rounded-xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden flex-shrink-0 flex items-center justify-center border border-zinc-200 dark:border-zinc-700">
                            @if ($new_logo)
                                <img src="{{ $new_logo->temporaryUrl() }}" class="h-full w-full object-cover">
                            @elseif($logo_path)
                                <img src="{{ Storage::url($logo_path) }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="new_logo" accept="image/png, image/jpeg" class="block w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 dark:file:bg-teal-900/30 dark:file:text-teal-400">
                            <p class="text-xs text-zinc-500 mt-2">PNG or JPG up to 2MB.</p>
                            @error('new_logo') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Store Name</label>
                        <input type="text" wire:model="name" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Store Contact Phone/Email</label>
                        <input type="text" wire:model="contact" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                        @error('contact') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Address</label>
                    <input type="text" wire:model="address" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent">
                    @error('address') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Store Bio/Description</label>
                    <textarea wire:model="bio" rows="3" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent"></textarea>
                    @error('bio') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="mt-4 flex justify-end gap-3">
                    <button type="button" wire:click="toggleEdit" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-lg bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 transition-all">
                        Save Profile
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
