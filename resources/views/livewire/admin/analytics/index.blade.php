<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

new #[Layout('admin.layouts.app')] class extends Component {
    
    public string $period = 'last_30_days';

    public function with()
    {
        $cacheKey = "analytics.stats.{$this->period}";

        return Cache::remember($cacheKey, 60, function () {
            $startDate = match ($this->period) {
                'today' => Carbon::today(),
                'last_7_days' => Carbon::now()->subDays(7),
                'last_30_days' => Carbon::now()->subDays(30),
                default => Carbon::now()->subDays(30),
            };

            // Order query base
            $orderBase = DB::table('orders')->where('created_at', '>=', $startDate);
            
            // Total GMV
            $totalGmv = (clone $orderBase)->sum('total_amount');
            
            // Commissions & Earnings
            $financials = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.created_at', '>=', $startDate)
                ->selectRaw('sum(order_items.commission_amount) as platform_commission, sum(order_items.earnings) as vendor_earnings')
                ->first();

            $totalOrdersCount = (clone $orderBase)->count();
            $completedOrdersCount = (clone $orderBase)->where('status', 'completed')->count();

            // Refund Rate (approved refunds / completed orders)
            $approvedRefunds = DB::table('refunds')
                ->where('status', 'approved')
                ->where('created_at', '>=', $startDate)
                ->count();
                
            $refundRate = $completedOrdersCount > 0 
                ? round(($approvedRefunds / $completedOrdersCount) * 100, 2) 
                : 0;

            // Conversion rate
            $conversionRate = $totalOrdersCount > 0 
                ? round(($completedOrdersCount / $totalOrdersCount) * 100, 2) 
                : 0;

            // Funnel
            $funnelStats = (clone $orderBase)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')->toArray();

            $funnel = [
                'pending' => $funnelStats['pending'] ?? 0,
                'accepted' => $funnelStats['accepted'] ?? 0,
                'processing' => $funnelStats['processing'] ?? 0,
                'completed' => $completedOrdersCount,
                'cancelled' => $funnelStats['cancelled'] ?? 0,
            ];

            // Charts
            $ordersChart = (clone $orderBase)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
                
            $revenueChart = (clone $orderBase)
                ->where('status', 'completed')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total_amount) as total_revenue'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // Disputes
            $totalDisputes = (clone $orderBase)->where('is_disputed', true)->count();

            // Recent Refunds (Not cached directly inside array to allow model binding naturally, but caching the raw query is fine)
            // Wait, caching full eloquent models can cause issues sometimes, but works in single-node. Better to load fresh or use DB
            $recentRefunds = Refund::with(['user:id,name', 'order:id,status'])
                ->where('created_at', '>=', $startDate)
                ->latest()
                ->take(5)
                ->get();

            // Vendor Risk
            // Low trust vendors
            $lowTrustVendors = DB::table('users')
                ->where('role', 'vendor')
                ->where('trust_score', '<', 50)
                ->select('id', 'name', 'trust_score')
                ->take(5)
                ->get();

             $flaggedVendors = DB::table('users')
                ->where('role', 'vendor')
                ->where('is_flagged', true)
                ->select('id', 'name')
                ->take(5)
                ->get();

            // Vendors with high refunds (Threshold: > 2)
            $riskyVendors = DB::table('refunds')
                ->join('order_items', 'refunds.order_item_id', '=', 'order_items.id')
                ->join('stores', 'order_items.store_id', '=', 'stores.id')
                ->join('users', 'stores.user_id', '=', 'users.id')
                ->where('refunds.created_at', '>=', $startDate)
                ->where('refunds.status', 'approved')
                ->select('users.id', 'users.name', DB::raw('count(refunds.id) as risk_count'))
                ->groupBy('users.id', 'users.name')
                ->havingRaw('count(refunds.id) >= 2')
                ->orderByDesc('risk_count')
                ->take(5)
                ->get();
                
            $disputeVendors = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('stores', 'order_items.store_id', '=', 'stores.id')
                ->join('users', 'stores.user_id', '=', 'users.id')
                ->where('orders.is_disputed', true)
                ->where('orders.created_at', '>=', $startDate)
                ->select('users.id', 'users.name', DB::raw('count(distinct orders.id) as dispute_count'))
                ->groupBy('users.id', 'users.name')
                ->havingRaw('count(distinct orders.id) >= 2')
                ->orderByDesc('dispute_count')
                ->take(5)
                ->get();

            return [
                'totalOrders' => $totalOrdersCount,
                'totalGmv' => (float) $totalGmv,
                'platformCommission' => (float) $financials->platform_commission,
                'vendorEarnings' => (float) $financials->vendor_earnings,
                
                'conversionRate' => $conversionRate,
                'refundRate' => $refundRate,
                'approvedRefunds' => $approvedRefunds,
                
                'funnel' => $funnel,
                'ordersChart' => $ordersChart,
                'revenueChart' => $revenueChart,
                
                'totalDisputes' => $totalDisputes,
                'recentRefunds' => $recentRefunds,
                
                'flaggedVendors' => $flaggedVendors,
                'lowTrustVendors' => $lowTrustVendors,
                'riskyVendors' => $riskyVendors,
                'disputeVendors' => $disputeVendors,
                
                'topVendors' => DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('stores', 'order_items.store_id', '=', 'stores.id')
                    ->join('users', 'stores.user_id', '=', 'users.id')
                    ->where('orders.created_at', '>=', $startDate)
                    ->select('users.id', 'users.name', DB::raw('sum(order_items.earnings) as total_revenue'))
                    ->groupBy('users.id', 'users.name')
                    ->orderByDesc('total_revenue')
                    ->take(5)
                    ->get(),

                'topStores' => DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('stores', 'order_items.store_id', '=', 'stores.id')
                    ->where('orders.created_at', '>=', $startDate)
                    ->select('stores.id', 'stores.name', DB::raw('count(order_items.id) as orders_count'))
                    ->groupBy('stores.id', 'stores.name')
                    ->orderByDesc('orders_count')
                    ->take(5)
                    ->get(),
            ];
        });
    }
}; ?>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm ring-1 ring-zinc-200 dark:border dark:border-zinc-800/50 p-4 mb-6 mt-0">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Market Analytics</h1>
            
            <div>
                <select wire:model.live="period" class="rounded-lg border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-shadow">
                    <option value="today">Today</option>
                    <option value="last_7_days">Last 7 Days</option>
                    <option value="last_30_days">Last 30 Days</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Revenue & Order Clarity KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        
        <div class="flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Volume (Orders)</h3>
            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($totalOrders) }}</p>
            </div>
            <div class="mt-auto text-sm text-zinc-500">
                <span class="font-medium text-blue-600 dark:text-blue-400">{{ $conversionRate }}%</span> conversion rate
            </div>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total GMV</h3>
            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">₦{{ number_format($totalGmv) }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden ring-inset focus-within:ring-green-500">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Platform Commission</h3>
            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($platformCommission) }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Vendor Earnings</h3>
            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">₦{{ number_format($vendorEarnings) }}</p>
            </div>
        </div>

    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Orders Over Time</h2>
            <div class="h-64 flex items-end gap-[2px] text-zinc-400">
                @forelse($ordersChart as $data)
                    <div class="flex-1 bg-blue-100 dark:bg-blue-900/50 rounded-t-sm hover:bg-blue-200 dark:hover:bg-blue-800/50 transition-colors" 
                         style="height: {{ max(($data->total / max(1, $ordersChart->max('total'))) * 100, 2) }}%" 
                         title="{{ $data->date }}: {{ $data->total }} orders"></div>
                @empty
                    <div class="w-full flex justify-center items-center h-full text-sm">No order data available yet.</div>
                @endforelse
            </div>
        </div>
        
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Completed GMV Over Time</h2>
            <div class="h-64 flex items-end gap-[2px] text-zinc-400">
                @forelse($revenueChart as $data)
                    <div class="flex-1 bg-green-100 dark:bg-green-900/50 rounded-t-sm hover:bg-green-200 dark:hover:bg-green-800/50 transition-colors" 
                         style="height: {{ max(($data->total_revenue / max(1, $revenueChart->max('total_revenue'))) * 100, 2) }}%" 
                         title="{{ $data->date }}: ₦{{ number_format($data->total_revenue) }}"></div>
                @empty
                    <div class="w-full flex justify-center items-center h-full text-sm">No revenue data available yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Order Funnel -->
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Order Funnel Drop-off</h2>
            <div class="text-sm font-semibold text-zinc-500">Overall Conversion: <span class="text-blue-600">{{ $conversionRate }}%</span></div>
        </div>

        <div class="space-y-4">
            @php 
                $maxFunnel = max(1, $totalOrders); 
            @endphp
            
            <div class="w-full relative group">
                <div class="flex justify-between text-sm mb-1 font-medium text-zinc-600 dark:text-zinc-400">
                    <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-yellow-400"></span> Pending</span> 
                    <span>{{ $funnel['pending'] }} ({{ round(($funnel['pending'] / $maxFunnel) * 100, 1) }}%)</span>
                </div>
                <div class="h-3 bg-zinc-100 rounded-full dark:bg-zinc-800 overflow-hidden">
                    <div class="h-full bg-yellow-400 rounded-full transition-all duration-500" style="width: {{ ($funnel['pending'] / $maxFunnel) * 100 }}%"></div>
                </div>
            </div>

            <div class="w-full relative group">
                <div class="flex justify-between text-sm mb-1 font-medium text-zinc-600 dark:text-zinc-400">
                    <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-400"></span> Accepted</span> 
                    <span>{{ $funnel['accepted'] }} ({{ round(($funnel['accepted'] / $maxFunnel) * 100, 1) }}%)</span>
                </div>
                <div class="h-3 bg-zinc-100 rounded-full dark:bg-zinc-800 overflow-hidden">
                    <div class="h-full bg-blue-400 rounded-full transition-all duration-500" style="width: {{ ($funnel['accepted'] / $maxFunnel) * 100 }}%"></div>
                </div>
            </div>

            <div class="w-full relative group">
                <div class="flex justify-between text-sm mb-1 font-medium text-zinc-600 dark:text-zinc-400">
                    <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-indigo-400"></span> Processing</span> 
                    <span>{{ $funnel['processing'] }} ({{ round(($funnel['processing'] / $maxFunnel) * 100, 1) }}%)</span>
                </div>
                <div class="h-3 bg-zinc-100 rounded-full dark:bg-zinc-800 overflow-hidden">
                    <div class="h-full bg-indigo-400 rounded-full transition-all duration-500" style="width: {{ ($funnel['processing'] / $maxFunnel) * 100 }}%"></div>
                </div>
            </div>

            <div class="w-full relative group">
                <div class="flex justify-between text-sm mb-1 font-medium text-zinc-600 dark:text-zinc-400">
                    <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-green-500"></span> Completed</span> 
                    <span>{{ $funnel['completed'] }} ({{ round(($funnel['completed'] / $maxFunnel) * 100, 1) }}%)</span>
                </div>
                <div class="h-3 bg-zinc-100 rounded-full dark:bg-zinc-800 overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full transition-all duration-500" style="width: {{ ($funnel['completed'] / $maxFunnel) * 100 }}%"></div>
                </div>
            </div>

            <div class="w-full relative group mt-6 pt-4 border-t border-dashed border-zinc-200 dark:border-zinc-800">
                <div class="flex justify-between text-sm mb-1 font-medium text-red-600 dark:text-red-400">
                    <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-red-400"></span> Drop-off / Cancelled</span> 
                    <span>{{ $funnel['cancelled'] }} ({{ round(($funnel['cancelled'] / $maxFunnel) * 100, 1) }}%)</span>
                </div>
                <div class="h-3 bg-red-50 rounded-full dark:bg-red-900/20 overflow-hidden">
                    <div class="h-full bg-red-400 rounded-full transition-all duration-500" style="width: {{ ($funnel['cancelled'] / $maxFunnel) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund & Dispute + Vendor Risk -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Refund & Dispute Analytics -->
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Refunds & Disputes Overview</h2>
            
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl text-center border border-zinc-100 dark:border-zinc-800">
                    <div class="text-2xl font-bold text-red-600">{{ $approvedRefunds }}</div>
                    <div class="text-[10px] font-semibold text-zinc-500 uppercase tracking-widest mt-1">Approved Refunds</div>
                </div>
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl text-center border border-zinc-100 dark:border-zinc-800">
                    <div class="text-2xl font-bold text-orange-600">{{ $refundRate }}%</div>
                    <div class="text-[10px] font-semibold text-zinc-500 uppercase tracking-widest mt-1">Refund Rate</div>
                </div>
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl text-center border border-zinc-100 dark:border-zinc-800">
                    <div class="text-2xl font-bold text-purple-600">{{ $totalDisputes }}</div>
                    <div class="text-[10px] font-semibold text-zinc-500 uppercase tracking-widest mt-1">Total Disputes</div>
                </div>
            </div>

            <h3 class="text-sm font-bold text-zinc-700 dark:text-zinc-300 mb-3">Recent Processing</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead class="text-left text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="pb-2 font-medium">Order ID</th>
                            <th class="pb-2 font-medium">Customer</th>
                            <th class="pb-2 font-medium">Amount</th>
                            <th class="pb-2 font-medium text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRefunds as $refund)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0 group">
                                <td class="py-3">
                                    <a href="{{ route('admin.orders.show', $refund->order_id) }}" class="text-blue-600 hover:underline">#{{ $refund->order_id }}</a>
                                </td>
                                <td class="py-3 text-zinc-700 dark:text-zinc-300">{{ collect(explode(' ', $refund->user->name ?? 'N/A'))->first() }}</td>
                                <td class="py-3 font-semibold text-red-600">₦{{ number_format($refund->amount) }}</td>
                                <td class="py-3 text-right">
                                    <span class="px-2 py-1 bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 rounded-md text-xs font-medium">{{ ucfirst($refund->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-zinc-500 text-sm">No recent refunds found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vendor Risk Dashboard -->
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 flex flex-col h-full">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 border-b border-zinc-100 dark:border-zinc-800 pb-3">Vendor Risk Indicators</h2>
            
            <div class="flex-1 space-y-5 overflow-y-auto pr-2">
                
                @if($flaggedVendors->count() > 0 || $lowTrustVendors->count() > 0)
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 p-4 rounded-xl flex flex-col items-center justify-center text-center">
                        <div class="text-sm font-semibold text-red-800 dark:text-red-400 mb-1">Flagged System</div>
                        <div class="text-3xl font-black text-red-600 my-1">{{ $flaggedVendors->count() }}</div>
                    </div>
                    
                    <div class="bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/20 p-4 rounded-xl flex flex-col items-center justify-center text-center">
                        <div class="text-sm font-semibold text-orange-800 dark:text-orange-400 mb-1">Low Trust (<50)</div>
                        <div class="text-3xl font-black text-orange-600 my-1">{{ $lowTrustVendors->count() }}</div>
                    </div>
                </div>
                @endif

                <!-- Risk Lists -->
                <div class="space-y-4">
                    @if($riskyVendors->count() > 0)
                    <div>
                        <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-2 flex items-center gap-2"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> High Refund Threshold Met</h3>
                        <div class="space-y-2">
                            @foreach($riskyVendors as $vendor)
                                <div class="flex items-center justify-between p-2.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800 rounded-lg">
                                    <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="font-medium text-sm text-zinc-900 dark:text-zinc-100 hover:text-blue-600 transition-colors truncate max-w-[150px]">{{ $vendor->name }}</a>
                                    <span class="text-xs font-bold text-white bg-red-500/90 py-0.5 px-2 rounded-full shadow-sm">{{ $vendor->risk_count }} Refunds</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($disputeVendors->count() > 0)
                    <div>
                        <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-2 flex items-center gap-2"><span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span> High Dispute Threshold Met</h3>
                        <div class="space-y-2">
                            @foreach($disputeVendors as $vendor)
                                <div class="flex items-center justify-between p-2.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800 rounded-lg">
                                    <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="font-medium text-sm text-zinc-900 dark:text-zinc-100 hover:text-blue-600 transition-colors truncate max-w-[150px]">{{ $vendor->name }}</a>
                                    <span class="text-xs font-bold text-white bg-purple-500/90 py-0.5 px-2 rounded-full shadow-sm">{{ $vendor->dispute_count }} Disputes</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($riskyVendors->count() === 0 && $disputeVendors->count() === 0 && $flaggedVendors->count() === 0 && $lowTrustVendors->count() === 0)
                        <div class="h-32 flex flex-col items-center justify-center text-center px-4 rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/20">
                            <flux:icon.check-circle class="w-8 h-8 text-green-500 mb-2" />
                            <p class="text-sm font-semibold text-green-800 dark:text-green-400">All Systems Clear</p>
                            <p class="text-xs text-green-600 dark:text-green-500/70">No high-risk vendors detected in this period.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <!-- Top Vendors & Stores -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Top Vendors (by Revenue)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="pb-2 font-medium">Vendor</th>
                            <th class="pb-2 text-right font-medium">Vendor Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topVendors as $vendor)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="py-3">
                                    <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="font-medium text-zinc-900 dark:text-zinc-100 hover:text-blue-600 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-bold text-zinc-600 dark:text-zinc-300">
                                            {{ substr($vendor->name, 0, 1) }}
                                        </div>
                                        {{ $vendor->name }}
                                    </a>
                                </td>
                                <td class="py-3 text-right text-green-600 font-bold">₦{{ number_format($vendor->total_revenue) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-6 text-center text-zinc-500 border border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">No vendor revenue data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Top Stores (by Volume)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-zinc-500 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="pb-2 font-medium">Store</th>
                            <th class="pb-2 text-right font-medium">Items Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topStores as $store)
                            <tr class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="py-3">
                                    <a href="{{ route('admin.stores.show', $store->id) }}" class="font-medium text-zinc-900 dark:text-zinc-100 hover:text-blue-600 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-bold text-zinc-600 dark:text-zinc-300">
                                            <flux:icon.building-storefront class="w-4 h-4" />
                                        </div>
                                        {{ $store->name }}
                                    </a>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="inline-flex items-center justify-center bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 py-1 px-3 rounded-full text-xs font-bold ring-1 ring-inset ring-blue-700/10 dark:ring-blue-400/20">
                                        {{ number_format($store->orders_count) }} Items
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-6 text-center text-zinc-500 border border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">No store order data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
