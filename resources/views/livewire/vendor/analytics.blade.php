<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

new #[Layout('components.layouts.vendor')] class extends Component {
    public $range = '7days';

    public function getMetricsProperty()
    {
        $storeId = Auth::user()->store->id;

        $vendorItems = OrderItem::with('listing')
             ->where('store_id', $storeId)
             ->whereHas('order', function ($query) {
                 $query->where('status', 'completed');
             })
             ->get();

        $totalRevenue = $vendorItems->sum(function($item) {
            return $item->price * $item->quantity;
        });

        $totalOrdersCount = Order::whereHas('items', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('status', 'completed')
            ->count();

        $startDate = match($this->range) {
            '30days' => Carbon::now()->subDays(30),
            '12months' => Carbon::now()->subMonths(12),
            default => Carbon::now()->subDays(7),
        };

        $recentItems = $vendorItems->where('created_at', '>=', $startDate);
        
        $recentRevenue = $recentItems->sum(function($item) {
            return $item->price * $item->quantity;
        });
        
        $recentOrdersCount = Order::whereHas('items', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->count();

        $salesOverTime = collect();
        if ($this->range === '12months') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i)->format('Y-m');
                $salesOverTime->put($date, 0); 
            }
            foreach ($recentItems as $item) {
                $date = $item->created_at->format('Y-m');
                if ($salesOverTime->has($date)) {
                    $salesOverTime[$date] += ($item->price * $item->quantity);
                }
            }
            $chartDates = $salesOverTime->keys()->map(fn($d) => \Carbon\Carbon::parse($d."-01")->format('M Y'))->toArray();
        } elseif ($this->range === '30days') {
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $salesOverTime->put($date, 0); 
            }
            foreach ($recentItems as $item) {
                $date = $item->created_at->format('Y-m-d');
                if ($salesOverTime->has($date)) {
                    $salesOverTime[$date] += ($item->price * $item->quantity);
                }
            }
            $chartDates = $salesOverTime->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray();
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $salesOverTime->put($date, 0); 
            }
            foreach ($recentItems as $item) {
                $date = $item->created_at->format('Y-m-d');
                if ($salesOverTime->has($date)) {
                    $salesOverTime[$date] += ($item->price * $item->quantity);
                }
            }
            $chartDates = $salesOverTime->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray();
        }
        
        $chartSales = $salesOverTime->values()->map(fn($v) => round($v, 2))->toArray();

        $topProducts = $recentItems->groupBy('listing_id')->map(function ($items) {
            return (object)[
                'listing' => $items->first()->listing,
                'total_sold' => $items->sum('quantity'),
                'total_revenue' => $items->sum(function($i) { return $i->price * $i->quantity; })
            ];
        })->sortByDesc('total_sold')->take(5);

        return (object)[
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrdersCount,
            'recentRevenue' => $recentRevenue,
            'recentOrders' => $recentOrdersCount,
            'chartDates' => json_encode($chartDates),
            'chartSales' => json_encode($chartSales),
            'topProducts' => $topProducts,
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    
    <div class="mt-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                Store Analytics
            </h1>
            <p class="text-sm text-zinc-500 mt-1">Detailed performance metrics for your products.</p>
        </div>
        <div>
            <select wire:model.live="range" class="bg-white dark:bg-zinc-800 text-sm font-medium text-zinc-700 dark:text-zinc-200 shadow-sm ring-1 ring-inset ring-zinc-300 dark:ring-zinc-700 rounded-lg px-3 py-1.5 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="7days">Last 7 Days (Weekly)</option>
                <option value="30days">Last 30 Days (Monthly)</option>
                <option value="12months">Last 12 Months (Annual)</option>
            </select>
        </div>
    </div>

    {{-- Main Top level stats --}}
    <div class="grid gap-6 sm:grid-cols-4">
        <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Revenue (All Time)</h3>
            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format($this->metrics->totalRevenue, 2) }}</p>
        </div>
        <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Orders (All Time)</h3>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->metrics->totalOrders }}</p>
        </div>
        <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 bg-indigo-50/50 dark:bg-indigo-900/10">
            <h3 class="text-sm font-medium text-indigo-600/80 dark:text-indigo-400/80">Revenue (Selected Range)</h3>
            <p class="text-3xl font-bold text-indigo-700 dark:text-indigo-400">₦{{ number_format($this->metrics->recentRevenue, 2) }}</p>
        </div>
        <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 bg-indigo-50/50 dark:bg-indigo-900/10">
            <h3 class="text-sm font-medium text-indigo-600/80 dark:text-indigo-400/80">Orders (Selected Range)</h3>
            <p class="text-3xl font-bold text-indigo-700 dark:text-indigo-400">{{ $this->metrics->recentOrders }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Chart container --}}
        <div class="lg:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-900/50">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Sales Over Time</h2>
            </div>
            <div class="p-6">
                <!-- Fallback simplified UI Chart simulation since adding massive JS libraries violates minimal MVP -->
                <div class="relative h-64 w-full flex items-end justify-between gap-2 pt-10" x-data="{
                    sales: {{ $this->metrics->chartSales }},
                    dates: {{ $this->metrics->chartDates }}
                }">
                    @php
                        $maxSale = max(json_decode($this->metrics->chartSales)) ?: 1;
                    @endphp
                    
                    @foreach(json_decode($this->metrics->chartSales) as $index => $saleAmount)
                        @php
                            $heightPercentage = ($saleAmount / $maxSale) * 100;
                            // Ensure a minimum height so 0 values have a sliver
                            $heightPercentage = max($heightPercentage, 2); 
                        @endphp
                        
                        <div class="flex-1 flex flex-col items-center gap-2 group">
                            <div class="relative w-full rounded-t-sm bg-indigo-100 hover:bg-indigo-500 dark:bg-indigo-900/30 dark:hover:bg-indigo-500/80 transition-colors" style="height: {{ $heightPercentage }}%;">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 bg-zinc-800 text-white text-xs px-2 py-1 rounded shadow pointer-events-none transition-opacity whitespace-nowrap z-10">
                                    ₦{{ number_format($saleAmount, 2) }}
                                </div>
                            </div>
                            <span class="text-[10px] sm:text-xs text-zinc-500 -rotate-45 origin-top-left sm:rotate-0 sm:origin-center mt-2">{{ json_decode($this->metrics->chartDates)[$index] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 py-5 flex flex-col h-full">
            <div class="px-6 pb-4 border-b border-zinc-200 dark:border-zinc-800 mb-2">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Top Products (Selected Range)</h2>
            </div>
            
            <div class="flex-1 overflow-y-auto px-6">
                @forelse($this->metrics->topProducts as $top)
                    <div class="py-3 flex items-center justify-between {{ !$loop->last ? 'border-b border-zinc-100 dark:border-zinc-800/50' : '' }}">
                        <div class="flex-1 min-w-0 pr-4">
                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                {{ optional($top->listing)->title ?? 'Unknown' }}
                            </h4>
                            <p class="text-xs text-zinc-500">{{ $top->total_sold }} sold</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-sm font-bold text-teal-600 dark:text-teal-400">₦{{ number_format($top->total_revenue, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center flex flex-col items-center">
                        <svg class="h-8 w-8 text-zinc-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <p class="text-sm text-zinc-500">Not enough data to determine top products.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
