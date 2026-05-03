<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function toggleSuspend()
    {
        if ($this->user->isAdmin()) return;

        $this->user->status = $this->user->status === 'active' ? 'suspended' : 'active';
        $this->user->save();
        
        if (function_exists('activity_log')) {
            activity_log('User Status Changed', "Status of {$this->user->email} changed to {$this->user->status}");
        }

        session()->flash('status', 'User status updated successfully.');
    }

    public function sendPasswordResetLink()
    {
        if ($this->user->isAdmin() && $this->user->id === auth()->id()) {
            session()->flash('error', 'You cannot reset your own password here.');
            return;
        }

        $status = Password::broker()->sendResetLink(
            ['email' => $this->user->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('success', 'Password reset link sent to ' . $this->user->email);
        } else {
            session()->flash('error', __($status));
        }
    }

    public function with(): array
    {
        $store = $this->user->isVendor() ? $this->user->store()->first() : null;
        $listingsCount = $this->user->isVendor() ? $this->user->listings()->count() : 0;

        return [
            'store' => $store,
            'listingsCount' => $listingsCount,
        ];
    }
}; ?>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>
                <flux:icon.arrow-left class="h-5 w-5" />
            </a>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">User Profile</h1>
        </div>
        
        <div class="flex items-center gap-3">
            @if(!$user->isAdmin())
                <button wire:click="toggleSuspend" wire:confirm="Are you sure you want to change this user's status?" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition-colors shadow-sm {{ $user->status === 'active' ? 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 border border-red-200 dark:border-red-500/20' : 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20 border border-green-200 dark:border-green-500/20' }}">
                    <flux:icon.shield-exclamation class="h-4 w-4 mr-2" />
                    {{ $user->status === 'active' ? 'Suspend Account' : 'Activate Account' }}
                </button>
            @endif
        </div>
    </div>

    @if (session()->has('status'))
        <div class="mb-6 p-4 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">
            {{ session('status') }}
        </div>
    @endif
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 border border-green-200 dark:border-green-500/20">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Main Profile Card --}}
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm ring-1 ring-zinc-200 dark:border dark:border-zinc-800/50 p-6 object-cover relative overflow-hidden">
                <div class="flex items-start gap-6">
                    <div class="h-24 w-24 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center dark:bg-indigo-900/30 dark:text-indigo-400 flex-shrink-0 text-3xl font-bold border-4 border-white dark:border-zinc-900 shadow-sm">
                        {{ $user->initials() }}
                    </div>
                    
                    <div class="flex-1 pt-2">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $user->name }}</h2>
                            <div class="flex gap-2">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400 border border-green-200 dark:border-green-500/30">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 border border-red-200 dark:border-red-500/30">
                                        Suspended
                                    </span>
                                @endif
                                
                                @if($user->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 border border-red-200 dark:border-red-500/30">Admin</span>
                                @elseif($user->role === 'vendor' && $user->vendor_type === 'service_provider')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30">Provider</span>
                                @elseif($user->role === 'vendor')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30">Vendor</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-800 dark:bg-zinc-700/50 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">Buyer</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-zinc-500 dark:text-zinc-400 flex items-center gap-2 mt-1">
                            <flux:icon.envelope class="h-4 w-4" />
                            {{ $user->email }}
                        </p>
                        <p class="text-zinc-500 dark:text-zinc-400 flex items-center gap-2 mt-1 text-sm">
                            <flux:icon.calendar class="h-4 w-4" />
                            Joined {{ $user->created_at->format('F j, Y') }} ({{ $user->created_at->diffForHumans() }})
                        </p>
                    </div>
                </div>
            </div>

            {{-- Vendor Details --}}
            @if($user->isVendor() || ($user->role === 'vendor'))
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm ring-1 ring-zinc-200 dark:border dark:border-zinc-800/50 p-6">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                        <flux:icon.building-storefront class="h-5 w-5 text-indigo-500" />
                        Vendor Information
                    </h3>
                    
                    @if($store)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-100 dark:border-zinc-800/50">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Store Name</p>
                                <p class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">{{ $store->name }}</p>
                            </div>
                            
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-100 dark:border-zinc-800/50">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Business Type</p>
                                <p class="mt-1 text-base font-semibold text-zinc-900 dark:text-white capitalize">{{ str_replace('_', ' ', $user->vendor_type ?? 'Product Seller') }}</p>
                            </div>

                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-100 dark:border-zinc-800/50">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Listings</p>
                                <p class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">{{ $listingsCount }}</p>
                            </div>
                            
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-100 dark:border-zinc-800/50">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Store Status</p>
                                <p class="mt-1 text-base font-semibold {{ $store->status === 'approved' ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">{{ ucfirst($store->status) }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium inline-flex items-center">
                                View Storefront <flux:icon.arrow-top-right-on-square class="h-4 w-4 ml-1.5" />
                            </a>
                        </div>
                    @else
                        <div class="p-6 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 flex items-center justify-center text-center">
                            <div>
                                <flux:icon.exclamation-circle class="h-8 w-8 text-amber-500 mx-auto mb-2" />
                                <p class="text-amber-800 dark:text-amber-200 font-medium">No Store Profile</p>
                                <p class="text-sm text-amber-600 dark:text-amber-400/80 mt-1">This vendor has not set up their store profile yet.</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Sidebar Cards --}}
        <div class="space-y-6">
            
            {{-- Activity Summary --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm ring-1 ring-zinc-200 dark:border dark:border-zinc-800/50 p-6">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white mb-4 uppercase tracking-wider text-zinc-500">Activity & Security</h3>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800/50">
                        <div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 shrink-0">
                            <flux:icon.clock class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">Last Login</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('M d, Y h:i A') }}<br>
                                    <span class="text-zinc-400">{{ $user->last_login_at->diffForHumans() }}</span>
                                @else
                                    Never logged in
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800/50">
                        <div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 shrink-0">
                            <flux:icon.envelope-open class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">Email Verification</p>
                            <p class="text-xs mt-1 {{ $user->email_verified_at ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $user->email_verified_at ? 'Verified on ' . $user->email_verified_at->format('M d, Y') : 'Unverified' }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button wire:click="sendPasswordResetLink" wire:loading.attr="disabled" class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-900 dark:bg-white px-4 py-2 text-sm font-semibold text-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-100 shadow-sm transition-colors disabled:opacity-50">
                            <flux:icon.key class="h-4 w-4 mr-2" />
                            Send Password Reset Link
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
