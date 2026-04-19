<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body x-data="{ sidebarOpen: false }" class="min-h-screen bg-white dark:bg-zinc-800">

    <!-- Sidebar -->
    <flux:sidebar x-show="sidebarOpen" x-transition
        class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <button class="lg:hidden" @click="sidebarOpen = false">
            <flux:icon name="x-mark" class="w-6 h-6" />
        </button>

        @auth
            <a href="{{ auth()->user()->isVendor() ? route('vendor.dashboard') : route('dashboard') }}"
                class="ml-1 flex items-center space-x-2 mb-4" wire:navigate>
        @else
                <a href="{{ route('home') }}" class="ml-1 flex items-center space-x-2 mb-4" wire:navigate>
            @endauth

                <div
                    class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black text-xl shadow-md">
                </div>

                <span class="font-extrabold text-xl tracking-tight text-zinc-900 dark:text-white hidden lg:block">
                    Sahel <span class="text-indigo-600 dark:text-indigo-400">DigiMart</span>
                </span>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Vendor Panel" class="grid">
                    <flux:navlist.item icon="globe-alt" :href="route('home')" :current="request()->routeIs('home')"
                        wire:navigate>
                        Marketplace Home
                    </flux:navlist.item>

                    @auth
                    <x-sidebar.user/>
                    @endauth

                </flux:navlist.group>
            </flux:navlist>

            <!-- Desktop User Menu -->
            @auth

                <!-- Here was the sidebar dropdown -->

            @endauth
    </flux:sidebar>


    <!-- Desktop Header (NEW) -->
    @auth
        <flux:header
            class="hidden lg:flex items-center gap-4 px-6 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">

            <!-- Sidebar Toggle (3 lines) -->
            <button @click="sidebarOpen = !sidebarOpen">
                <flux:icon name="bars-3" class="w-6 h-6" />
            </button>

            <!-- Search Bar -->
            <div class="flex-1 max-w-md">
                <input type="text" placeholder="Search..."
                    class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Icons Section -->
            <div class="flex items-center gap-4">

                <!-- Notifications -->
                <button class="relative">
                    <flux:icon name="bell" class="w-5 h-5 text-zinc-600 dark:text-zinc-300" />
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1">
                        3
                    </span>
                </button>

                <!-- Messages -->
                <button class="relative">
                    <flux:icon name="chat-bubble-left-right" class="w-5 h-5 text-zinc-600 dark:text-zinc-300" />
                    <span class="absolute -top-1 -right-1 bg-indigo-500 text-white text-xs rounded-full px-1">
                        2
                    </span>
                </button>

            </div>

            <flux:spacer />

            <!-- User Dropdown -->
            <flux:dropdown position="bottom" align="end">
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down" />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 dark:bg-neutral-700 text-black dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    <span class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>
                        Settings
                    </flux:menu.item>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                            Log Out
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>

        </flux:header>
    @endauth

    <!-- Mobile Header -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        @auth
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    <span class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>
                        Settings
                    </flux:menu.item>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        @endauth
    </flux:header>

    <flux:main>
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>

</html>