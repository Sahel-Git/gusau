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
                        View More 
                    </flux:navlist.item>

                    @auth
                    @include('vendor.layouts.sidebar')
                    @endauth

                </flux:navlist.group>
            </flux:navlist>

            <!-- Desktop User Menu -->
            @auth

                <!-- Here wa the sidebar dropdown -->

            @endauth
    </flux:sidebar>


    @include('vendor.layouts.partials.header')

     <flux:main>
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>

</html>