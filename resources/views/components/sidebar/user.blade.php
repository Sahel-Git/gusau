<flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Dashboard</flux:navlist.item>
<flux:navlist.item icon="shopping-cart" href="#" wire:navigate>My Purchases</flux:navlist.item>
<flux:navlist.item icon="heart" href="#" wire:navigate>Saved Items</flux:navlist.item>
