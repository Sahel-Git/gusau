<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('admin.layouts.app')] class extends Component {
    public function with()
    {
        return [
            'totalUsers' => User::where('role', 'user')->count(),
            'totalVendors' => User::where('role', 'vendor')->count(),
            'totalCategories' => Category::count(),
            'totalListings' => Listing::where('listings.status', 'approved')->count(),
            'pendingApprovals' => Listing::where('listings.status', 'pending')->count(),

            // NEW
            'totalOrders' => Order::count(),
            'totalRevenue' => Order::where('orders.status', 'completed')->sum('total_amount'),
            'totalCommission' => Order::where('orders.status', 'completed')->sum('commission'),

            'recentOrders' => Order::latest()->take(5)->get(),
        ];

    }
}; ?>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- HEADER SECTION --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm ring-1 ring-zinc-200 dark:border dark:border-zinc-800/50 p-4 mb-2 mt-0">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Market overview & Analytics</h1>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid gap-3 grid-cols-2 md:grid-cols-4 xl:grid-cols-7 mt-3">
                {{-- Stat 1 --}}
                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                        <flux:icon.users class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Users</h3>
                        <p class="text-l font-bold text-zinc-900 dark:text-white">{{ $totalUsers }}</p>
                    </div>
                </div>

                {{-- Stat 2 --}}
                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400">
                        <flux:icon.building-storefront class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Vendors</h3>
                        <p class="text-l font-bold text-zinc-900 dark:text-white">{{ $totalVendors }}</p>
                    </div>
                </div>

                {{-- Stat 3 --}}
                <div class="flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                        <flux:icon.tag class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Categories</h3>
                        <p class="text-1 font-bold text-zinc-900 dark:text-white">{{ $totalCategories }}</p>
                    </div>
                </div>

                {{-- Stat 4 --}}
                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <flux:icon.document-check class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Active Listings</h3>
                        <p class="text-l font-bold text-zinc-900 dark:text-white">{{ $totalListings }}</p>
                    </div>
                </div>


                {{-- Stat 5 --}}
                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                        <flux:icon.shopping-bag class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Orders</h3>
                        <p class="text-l font-bold text-zinc-900 dark:text-white">{{ $totalOrders }}</p>
                    </div>
                </div>

                {{-- Stat 6 --}}
                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">
                        <flux:icon.currency-dollar class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Revenue</h3>
                        <p class="text-l font-bold text-zinc-900 dark:text-white">₦{{ number_format($totalRevenue) }}</p>
                    </div>
                </div>

                {{-- Stat 7 --}}
                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-100 text-pink-600 dark:bg-pink-500/20 dark:text-pink-400">
                        <flux:icon.banknotes class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Commission</h3>
                        <p class="text-l font-bold text-zinc-900 dark:text-white">₦{{ number_format($totalCommission) }}</p>
                    </div>
                </div>
            </div>
        </div> {{-- END HEADER --}}

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Pending Approvals alert --}}
                @if($pendingApprovals > 0)          ({{-- Pending Approvals is hidden till it > 0 --}})
                    <div class="mt-0 rounded-2xl bg-amber-50 p-6 shadow-sm ring-1 ring-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 flex flex-col sm:flex-row items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                                <flux:icon.exclamation-triangle class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-amber-900 dark:text-amber-200">Pending Approvals Action Required</h3>
                                <p class="text-sm text-amber-700 dark:text-amber-400/80">There are {{ $pendingApprovals }} listings waiting for review.</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.approvals') }}" class="mt-4 sm:mt-0 rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-colors">
                            Review Now
                        </a>
                    </div>
                @endif
                    {{-- Sales & Orders Analytics --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">
                        Sales & Orders Analytics
                    </h2>
                    <div class="h-64 flex items-center justify-center text-zinc-400">
                        Chart coming soon...
                    </div>
                </div>

                {{-- Recent Orders --}}
                <div class="mt-0 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <h2 class="text-lg font-bold mb-4 text-zinc-900 dark:text-white">Recent Orders</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-zinc-500">
                                <tr>
                                    <th class="pb-2">Order ID</th>
                                    <th class="pb-2">Customer</th>
                                    <th class="pb-2">Amount</th>
                                    <th class="pb-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr class="border-t border-zinc-200 dark:border-zinc-800">
                                        <td class="py-2">#{{ $order->id }}</td>
                                        <td>{{ $order->user->name ?? 'N/A' }}</td>
                                        <td>₦{{ number_format($order->total_amount) }}</td>
                                        <td>{{ ucfirst($order->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                    <h2 class="text-lg font-bold mb-4 text-zinc-900 dark:text-white">
                        Delivery Analytics
                    </h2>

                    <div class="space-y-2 text-sm">
                        <p>Completed: 0</p>
                        <p>Pending: 0</p>
                        <p>Failed: 0</p>
                    </div>
                </div>

                {{-- Top Vendors & Services --}}
                <div class="rounded-2xl bg-white p-6 w-full shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
                        <h2 class="text-lg font-bold mb-4 text-zinc-900 dark:text-white">
                            Top Vendors & Services
                        </h2>
                            <div class="space-y-3">
                            <div>
                                <p class="text-sm">Vendor A</p>
                                    <div class="h-2 bg-zinc-200 rounded-full">
                                        <div class="h-2 bg-blue-500 rounded-full w-2/3"></div>
                                    </div>
                            </div>

                            <div>
                                <p class="text-sm">Vendor B</p>
                                <div class="h-2 bg-zinc-200 rounded-full">
                                <div class="h-2 bg-green-500 rounded-full w-1/2"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        
    </div>
</div>
