<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use App\Models\VendorOrder;
use App\Models\WalletTransaction;

new #[Layout('admin.layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'totalRevenue' => Order::whereIn('status', ['paid', 'completed'])->sum('total_amount'),
            'totalCommission' => VendorOrder::sum('commission_amount'),
            'pendingPayouts' => VendorOrder::where('payout_status', 'pending')->sum('earnings'),
            'completedPayouts' => VendorOrder::where('payout_status', 'paid')->sum('earnings'),
            'failedPayouts' => VendorOrder::where('payout_status', 'failed')->sum('earnings'),
            'recentTransactions' => WalletTransaction::with('user')->latest()->limit(10)->get(),
            'recentPayouts' => VendorOrder::with('vendor')->whereIn('payout_status', ['paid', 'pending', 'failed'])->latest()->limit(10)->get()
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Payments Dashboard</h1>
        <p class="text-sm text-zinc-500 mt-1">Platform financial overview and transaction logs.</p>
    </div>

    {{-- Top Metrics Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Revenue -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Total Revenue</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($totalRevenue, 2) }}</p>
        </div>
        <!-- Commission -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Total Commission</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($totalCommission, 2) }}</p>
        </div>
        <!-- Pending Payouts -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Pending Payouts</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($pendingPayouts, 2) }}</p>
        </div>
        <!-- Completed Payouts -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Completed Payouts</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($completedPayouts, 2) }}</p>
        </div>
    </div>

    @if($failedPayouts > 0)
        <!-- Failed Payout Alert -->
        <div class="rounded-2xl bg-red-50 p-4 ring-1 ring-red-200 dark:bg-red-500/10 dark:ring-red-500/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Failed Payouts Detected</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p>There are ₦{{ number_format($failedPayouts, 2) }} in failed vendor payouts requiring attention.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Second Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent Transactions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead>
                        <tr>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">User</th>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Type</th>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Amount</th>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($recentTransactions as $tx)
                            <tr>
                                <td class="py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">{{ optional($tx->user)->name ?? 'System' }}</td>
                                <td class="py-3 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $tx->type === 'credit' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400' }}">
                                        {{ ucfirst($tx->type) }}
                                    </span>
                                </td>
                                <td class="py-3 whitespace-nowrap text-sm font-medium {{ $tx->type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-zinc-900 dark:text-zinc-200' }}">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}₦{{ number_format($tx->amount, 2) }}
                                </td>
                                <td class="py-3 whitespace-nowrap text-sm text-zinc-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-sm text-zinc-500">No transactions recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payouts -->
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent Payouts</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead>
                        <tr>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Vendor</th>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Amount</th>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($recentPayouts as $payout)
                            <tr>
                                <td class="py-3 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">{{ optional($payout->vendor)->name ?? 'Unknown Vendor' }}</td>
                                <td class="py-3 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-200">
                                    ₦{{ number_format($payout->earnings, 2) }}
                                </td>
                                <td class="py-3 whitespace-nowrap text-sm">
                                    @php
                                        $statusClass = match($payout->payout_status) {
                                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400',
                                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400',
                                            'failed' => 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400',
                                            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $statusClass }}">
                                        {{ ucfirst($payout->payout_status) }}
                                    </span>
                                </td>
                                <td class="py-3 whitespace-nowrap text-sm text-zinc-500">{{ $payout->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-sm text-zinc-500">No payouts logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
