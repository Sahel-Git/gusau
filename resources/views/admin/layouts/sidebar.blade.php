<flux:navlist.item icon="command-line" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>Admin Dashboard</flux:navlist.item>
<flux:navlist.item icon="document-check" :href="route('admin.approvals')" :current="request()->routeIs('admin.approvals')" wire:navigate>Approval Queue</flux:navlist.item>
<flux:navlist.item icon="tag" :href="route('admin.categories')" :current="request()->routeIs('admin.categories')" wire:navigate>Categories</flux:navlist.item>
<flux:navlist.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>User Management</flux:navlist.item>

<flux:navlist.item 
    icon="chart-bar" 
    href="{{ route('admin.analytics.index') }}" 
    :current="request()->routeIs('admin.analytics.*')"
    wire:navigate>
    Market Analytics
</flux:navlist.item>

<flux:navlist.item 
    icon="users" 
    href="{{ route('admin.vendors.index') }}" 
    :current="request()->routeIs('admin.vendors.*')"
    wire:navigate>
    Vendors Management
</flux:navlist.item>

<flux:navlist.item 
    icon="building-storefront" 
    href="{{ route('admin.stores.index') }}" 
    :current="request()->routeIs('admin.stores.*')"
    wire:navigate>
    Stores Management
</flux:navlist.item>

<flux:navlist.item 
    icon="shopping-bag" 
    href="{{ route('admin.listings.index') }}" 
    :current="request()->routeIs('admin.listings.*')"
    wire:navigate>
    Products & Services
</flux:navlist.item>

<flux:navlist.item 
    icon="shopping-cart" 
    href="{{ route('admin.orders.index') }}" 
    :current="request()->routeIs('admin.orders.*')"
    wire:navigate>
    Orders Management
</flux:navlist.item>

{{-- System Section --}}
<flux:navlist.group heading="System" class="grid mt-10">

    <flux:navlist.item 
        icon="cog-6-tooth" 
        href="{{ route('admin.settings.index') }}" 
        :current="request()->routeIs('admin.settings.*')"
        wire:navigate
    >
        Business Settings
    </flux:navlist.item>

    <flux:navlist.item 
        icon="clipboard-document-list" 
        href="{{ route('admin.logs.index') }}" 
        :current="request()->routeIs('admin.logs.*')"
        wire:navigate
    >
        Activity Logs
    </flux:navlist.item>

</flux:navlist.group>