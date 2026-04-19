<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\OrderItem;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.vendor')] class extends Component {
    public $amount = '';

    public function getMetricsProperty()
    {
        $storeId = Auth::user()->store->id;

        $completedItems = OrderItem::with(['order', 'listing'])
            ->where('store_id', $storeId)
            ->whereHas('order', function ($query) {
                $query->where('status', 'completed');
            })
            ->latest()
            ->get();

        $totalEarnings = 0;
        $availableBalance = 0;
        $pendingBalance = 0;

        foreach ($completedItems as $item) {
            $total = $item->price * $item->quantity;
            $totalEarnings += $total;
            if ($item->payout_status === 'paid') {
                $availableBalance += $total;
            } else {
                $pendingBalance += $total;
            }
        }

        $totalWithdrawnOrPending = Withdrawal::where('vendor_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');
            
        $withdrawable = max(0, $availableBalance - $totalWithdrawnOrPending);

        return (object)[
            'totalEarnings' => $totalEarnings,
            'availableBalance' => $withdrawable,
            'pendingBalance' => $pendingBalance,
            'completedItems' => $completedItems,
        ];
    }
    
    public function getWithdrawalsProperty()
    {
        return Withdrawal::where('vendor_id', Auth::id())->latest()->get();
    }

    public function requestWithdrawal()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $metrics = $this->getMetricsProperty();

        if ($this->amount > $metrics->availableBalance) {
            $this->addError('amount', 'Cannot withdraw more than your available balance.');
            return;
        }

        Withdrawal::create([
            'vendor_id' => Auth::id(),
            'amount' => $this->amount,
            'status' => 'pending',
        ]);

        $this->amount = '';
        session()->flash('success', 'Withdrawal request submitted successfully.');
    }
}; ?>

<div class="flex flex-col gap-6 w-full max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    
    <div class="mt-4">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
            Wallet & Payouts
        </h1>
        <p class="text-sm text-zinc-500 mt-1">Manage your earnings and request withdrawals.</p>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif

    {{-- Metrics Grid --}}
    <div class="grid gap-6 sm:grid-cols-3">
        <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Available Balance</h3>
            <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">₦{{ number_format($this->metrics->availableBalance, 2) }}</p>
            <p class="text-xs text-zinc-500 mt-2">Ready for withdrawal</p>
        </div>
        
        <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending Balance</h3>
            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">₦{{ number_format($this->metrics->pendingBalance, 2) }}</p>
            <p class="text-xs text-zinc-500 mt-2">Waiting to be cleared</p>
        </div>

        <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 relative overflow-hidden bg-zinc-50 dark:bg-zinc-900/50">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Earnings</h3>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">₦{{ number_format($this->metrics->totalEarnings, 2) }}</p>
            <p class="text-xs text-zinc-500 mt-2">All-time cleared sales</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Withdraw Request Form --}}
        <div class="lg:col-span-1 rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden h-max">
            <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Request Withdrawal</h2>
            </div>
            <div class="p-6">
                <form wire:submit="requestWithdrawal" class="flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Amount (₦)</label>
                        <input type="number" step="0.01" wire:model="amount" class="block w-full rounded-lg border-zinc-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm bg-transparent" placeholder="0.00">
                        @error('amount') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <button type="submit" class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 transition-all">
                        Submit Request
                    </button>
                    
                    <p class="text-xs text-zinc-500 text-center mt-2">
                        Withdrawals are typically processed within 2-3 business days.
                    </p>
                </form>
            </div>
        </div>

        {{-- Withdrawals History --}}
        <div class="lg:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Previous Withdrawals</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Amount</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                        @forelse($this->withdrawals as $w)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-semibold text-zinc-900 dark:text-zinc-200">
                                    ₦{{ number_format($w->amount, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $w->created_at->format('M d, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @if($w->status === 'approved')
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Approved</span>
                                    @elseif($w->status === 'pending')
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-400">Pending</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-500/20 dark:text-red-400">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No withdrawal requests yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Earnings Transaction History --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 border dark:border-zinc-800/50 overflow-hidden mt-2">
        <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Earnings History</h2>
            <p class="text-sm text-zinc-500 mt-1">Cleared sales strictly from your products</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Product</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Date Ordered</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Sale Amount</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-200">Payout Status (Wallet)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                    @forelse($this->metrics->completedItems as $item)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-zinc-900 dark:text-zinc-200">
                                {{ optional($item->listing)->title ?? 'Unknown Item' }} (x{{ $item->quantity }})
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $item->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-teal-600 dark:text-teal-400">
                                +₦{{ number_format($item->price * $item->quantity, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($item->payout_status === 'paid')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Paid (Cleared)</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">Unpaid (Pending)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No completed sales yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
