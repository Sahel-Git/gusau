<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\VendorOrder;
use App\Models\WalletTransaction;

new #[Layout('vendor.layouts.app')] class extends Component {
    public function with(): array
    {
        $vendorId = auth()->id();

        return [
            'totalEarnings' => VendorOrder::where('vendor_id', $vendorId)->sum('earnings'),
            'availableBalance' => auth()->user()->wallet_balance ?? 0,
            'pendingPayouts' => VendorOrder::where('vendor_id', $vendorId)->where('payout_status', 'pending')->sum('earnings'),
            'completedPayouts' => VendorOrder::where('vendor_id', $vendorId)->where('payout_status', 'paid')->sum('earnings'),
            'recentTransactions' => WalletTransaction::where('user_id', $vendorId)->latest()->limit(20)->get()
        ];
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Financial Dashboard</h1>
        <p class="text-sm text-zinc-500 mt-1">Track your earnings, wallet balance, and payout history.</p>
    </div>

    {{-- Top Metrics Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Earnings -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Total Earnings</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($totalEarnings, 2) }}</p>
        </div>
        <!-- Wallet Balance -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Available Balance</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($availableBalance, 2) }}</p>
        </div>
        <!-- Pending Payouts -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Pending Payout</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($pendingPayouts, 2) }}</p>
        </div>
        <!-- Completed Payouts -->
        <div class="rounded-2xl shadow-sm p-6 bg-white dark:bg-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-800">
            <p class="text-sm font-medium text-zinc-500 truncate dark:text-zinc-400">Paid Amount</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">₦{{ number_format($completedPayouts, 2) }}</p>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Transaction History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead>
                    <tr>
                        <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Date</th>
                        <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Description</th>
                        <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Type</th>
                        <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Amount</th>
                        <th class="py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Balance After</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($recentTransactions as $tx)
                        <tr>
                            <td class="py-3 whitespace-nowrap text-sm text-zinc-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            <td class="py-3 text-sm text-zinc-900 dark:text-zinc-200">{{ $tx->description }}</td>
                            <td class="py-3 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $tx->type === 'credit' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="py-3 whitespace-nowrap text-sm font-medium {{ $tx->type === 'credit' ? 'text-green-600 dark:text-green-400' : 'text-zinc-900 dark:text-zinc-200' }}">
                                {{ $tx->type === 'credit' ? '+' : '-' }}₦{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="py-3 whitespace-nowrap text-sm text-zinc-500">₦{{ number_format($tx->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-sm text-zinc-500">No transactions recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
