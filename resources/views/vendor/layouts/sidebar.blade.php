<flux:navlist.item icon="home" :href="route('vendor.dashboard')" :current="request()->routeIs('vendor.dashboard')" wire:navigate>Dashboard</flux:navlist.item>
<flux:navlist.item icon="inbox" href="#" wire:navigate>Products/Services</flux:navlist.item>
<flux:navlist.item icon="shopping-bag" :href="route('vendor.listings')" :current="request()->routeIs('vendor.listings')" wire:navigate>My Listings</flux:navlist.item>
<flux:navlist.item icon="inbox" :href="route('vendor.orders')" :current="request()->routeIs('vendor.orders*')" wire:navigate>Order Management</flux:navlist.item>
<flux:navlist.item icon="banknotes" :href="route('vendor.wallet')" :current="request()->routeIs('vendor.wallet')" wire:navigate> Wallet & Payouts</flux:navlist.item>
<flux:navlist.item icon="inbox" href="#" wire:navigate>Reviews</flux:navlist.item>
<flux:navlist.item icon="inbox" :href="route('vendor.analytics')" :current="request()->routeIs('vendor.analytics')" wire:navigate>Analytics</flux:navlist.item>
<flux:navlist.item icon="inbox" :href="route('store.show', auth()->user()->store?->slug ?? '')" target="_blank">View Storefront</flux:navlist.item>

