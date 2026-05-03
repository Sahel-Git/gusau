@php
    $authUser = auth()->guard('vendor')->user();
    $roleLabel = match($authUser->role ?? null) {
        'admin' => 'System Admin',
        'vendor' => 'Market Vendor',
        default => 'User'
    };
@endphp

    <flux:header class="hidden lg:flex items-center gap-4 px-6 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
        <!-- Sidebar Toggle -->
        <button @click="sidebarOpen = !sidebarOpen">
            <flux:icon name="bars-3" class="w-6 h-6" />
        </button>

        <!-- Search Bar -->
        <div class="flex-1 max-w-md">
            <input type="text" placeholder="Search..." class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex items-center gap-4">
            <button class="relative">
                <flux:icon name="bell" class="w-5 h-5 text-zinc-600 dark:text-zinc-300" />
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1">100</span>
            </button>
        </div>

        <flux:spacer />

        <!-- User Dropdown -->
        <flux:dropdown position="bottom" align="end">
            <flux:profile :name="$authUser?->name ?? 'User'" :initials="$authUser?->initials() ?? 'U'" icon-trailing="chevron-down" />
            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 dark:bg-neutral-700 text-black dark:text-white">
                                    {{ $authUser?->initials() ?? 'U' }}
                                </span>
                            </span>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ $authUser?->name ?? 'Guest' }}</span>
                                <span class="truncate text-xs">{{ $authUser?->email ?? '' }}</span>
                                <span class="text-xs text-gray-500">{{ $roleLabel }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>
                <flux:menu.separator />
                <flux:menu.item href="{{ route('vendor.settings.profile') }}" icon="cog" wire:navigate>Settings</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">Log Out</flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Mobile Header -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <flux:dropdown position="top" align="end">
            <flux:profile :initials="$authUser?->initials() ?? 'U'" icon-trailing="chevron-down" />
            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ $authUser?->initials() ?? 'U' }}
                                </span>
                            </span>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ $authUser?->name ?? 'Guest' }}</span>
                                <span class="truncate text-xs">{{ $authUser?->email ?? '' }}</span>
                                <span class="text-xs text-gray-500">{{ $roleLabel }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>
                <flux:menu.separator />
                <flux:menu.item href="{{ route('vendor.settings.profile') }}" icon="cog" wire:navigate>Settings</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">{{ __('Log Out') }}</flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>
