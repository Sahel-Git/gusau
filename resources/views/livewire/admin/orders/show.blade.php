<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use App\Services\TrustScoreService;

new #[Layout('admin.layouts.app')] class extends Component {
    public Order $order;
    public $disputeReason = '';
    public $adminNotes = '';
    public $refundAmount = '';
    public $refundReason = '';

    public function mount(Order $order)
    {
        $this->order = $order->load(['user', 'orderItems.store.user']);
        $this->adminNotes = $order->admin_notes;
    }

    public function with(): array
    {
        return [
            'groupedOrderItems' => $this->order->orderItems->groupBy('store_id'),
            'histories' => OrderStatusHistory::with('user')->where('order_id', $this->order->id)->latest()->get(),
            'activeRefund' => Refund::where('order_id', $this->order->id)->latest()->first(),
        ];
    }

    public function markAsDisputed()
    {
        $this->validate([
            'disputeReason' => 'required|string|min:3'
        ]);

        $this->order->update([
            'is_disputed' => true,
            'dispute_reason' => $this->disputeReason
        ]);
        
        // Deduct trust score for dispute creation
        $uniqueVendorIds = $this->order->orderItems->pluck('store.user_id')->unique();
        $vendors = \App\Models\User::whereIn('id', $uniqueVendorIds)->get();
        $trustService = app(TrustScoreService::class);
        
        foreach ($vendors as $vendor) {
            $trustService->updateScore($vendor, -5, 'Dispute created for order #' . $this->order->id);
        }

        $this->disputeReason = '';
    }

    public function saveAdminNotes()
    {
        $this->order->update([
            'admin_notes' => $this->adminNotes
        ]);
    }

    public function initiateRefund()
    {
        $this->validate([
            'refundAmount' => 'required|numeric|min:1',
            'refundReason' => 'required|string|min:5'
        ]);

        if (!$this->order->is_disputed) {
            abort(403, 'Order must be disputed to initiate a refund.');
        }

        $existingRefund = Refund::where('order_id', $this->order->id)
            ->whereIn('status', ['pending', 'approved', 'manual_review_required'])
            ->exists();

        if ($existingRefund) {
            session()->flash('error', 'A refund has already been initiated or approved for this order.');
            return;
        }

        Refund::create([
            'order_id' => $this->order->id,
            'user_id' => auth()->id(),
            'amount' => $this->refundAmount,
            'reason' => $this->refundReason,
            'status' => 'pending'
        ]);

        $this->refundAmount = '';
        $this->refundReason = '';
        session()->flash('success', 'Refund initiated successfully.');
    }

    public function processRefund($refundId, $decision)
    {
        DB::beginTransaction();
        try {
            $refund = Refund::where('id', $refundId)->lockForUpdate()->first();
            
            if (!$refund || $refund->status !== 'pending') {
                DB::rollBack();
                return;
            }

            if ($decision === 'reject') {
                $refund->update(['status' => 'rejected']);
                DB::commit();
                return;
            }

            if ($decision === 'approve') {
                $isPaidOut = $this->order->orderItems->contains('payout_status', 'paid');
                $penalty = min(10, max(2, round($refund->amount / 1000)));
                
                if ($isPaidOut) {
                    $vendor = optional(optional($this->order->orderItems->first())->store)->user;
                    
                    if ($vendor) {
                        try {
                            if (method_exists($vendor, 'debitWallet')) {
                                $vendor->debitWallet($refund->amount, 'Refund reversal for Order #' . $this->order->id, 'REFUND_REV_' . $refund->id);
                                $refund->update(['status' => 'approved']);
                                app(TrustScoreService::class)->updateScore($vendor, -1 * $penalty, 'Refund approved for Order #' . $this->order->id);
                            } else {
                                $refund->update(['status' => 'manual_review_required']);
                                app(TrustScoreService::class)->updateScore($vendor, -10, 'Manual review required for refund on Order #' . $this->order->id . ' (debit API missing)');
                            }
                        } catch (\Exception $e) {
                            $refund->update(['status' => 'manual_review_required']);
                            app(TrustScoreService::class)->updateScore($vendor, -10, 'Manual review required for refund on Order #' . $this->order->id . ' (debit failed)');
                        }
                    } else {
                        $refund->update(['status' => 'manual_review_required']);
                    }
                } else {
                    $refund->update(['status' => 'approved']);
                    $uniqueVendorIds = $this->order->orderItems->pluck('store.user_id')->unique();
                    $vendors = \App\Models\User::whereIn('id', $uniqueVendorIds)->get();
                    $trustService = app(TrustScoreService::class);
                    foreach ($vendors as $vendor) {
                        $trustService->updateScore($vendor, -1 * $penalty, 'Refund approved for Order #' . $this->order->id);
                    }
                }

                $this->order->update(['status' => 'cancelled']);
                
                OrderStatusHistory::create([
                    'order_id' => $this->order->id,
                    'user_id' => auth()->id(),
                    'status' => 'cancelled',
                    'note' => 'Order cancelled automatically due to refund approval',
                ]);

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Refund logic failed during processing.');
        }
    }

    public function getAllowedStatuses()
    {
        if (auth()->user()->role === 'admin') {
            return ['pending', 'accepted', 'processing', 'out_for_delivery', 'completed', 'cancelled'];
        }

        $currentStatus = $this->order->status;
        $orderType = $this->order->orderItems->contains(function ($item) {
            return ($item->type ?? optional($item->listing)->type) === 'service';
        }) ? 'service' : 'product';

        if ($orderType === 'product') {
            $allowedTransitions = [
                'pending' => ['accepted', 'cancelled'],
                'accepted' => ['processing', 'cancelled'],
                'processing' => ['out_for_delivery'],
                'out_for_delivery' => ['completed'],
            ];
        } else {
            $allowedTransitions = [
                'pending' => ['accepted', 'cancelled'],
                'accepted' => ['processing', 'cancelled'],
                'processing' => ['completed'],
            ];
        }

        return $allowedTransitions[$currentStatus] ?? [];
    }

    public function updateStatus($newStatus)
    {
        $currentStatus = $this->order->status;

        if ($currentStatus === $newStatus) {
            return;
        }

        if ($currentStatus === 'completed') {
            return;
        }

        if (auth()->user()->role !== 'admin') {
            $allowedTransitions = $this->getAllowedStatuses();
            if (!in_array($newStatus, $allowedTransitions)) {
                abort(403, 'Invalid status transition');
            }
        }

        $this->order->update([
            'status' => $newStatus
        ]);

        OrderStatusHistory::create([
            'order_id' => $this->order->id,
            'user_id' => auth()->id(),
            'status' => $newStatus,
            'note' => 'Status updated via admin panel',
        ]);

        if (function_exists('activity_log')) {
            activity_log(
                'order_status_updated',
                'Order #' . $this->order->id . ' changed from ' . $currentStatus . ' to ' . $newStatus
            );
        }
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Order #{{ $order->id }}</h1>
        </div>
        
        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
            {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
            {{ !in_array($order->status, ['completed', 'pending', 'cancelled', 'processing']) ? 'bg-gray-100 text-gray-800' : '' }}
        ">
            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- SECTION 1: ORDER SUMMARY -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Order Summary</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Order ID</p>
                        <p class="font-medium text-gray-900">#{{ $order->id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Amount</p>
                        <p class="font-medium text-gray-900">₦{{ number_format($order->total_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Order Status</p>
                        @if($order->status === 'completed')
                            <span class="inline-flex text-sm font-medium text-green-700 bg-green-50 px-2 py-1 rounded-md border border-green-200">Completed</span>
                        @else
                            <select wire:change="updateStatus($event.target.value)" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 w-full py-1">
                                <option value="{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</option>
                                @foreach($this->getAllowedStatuses() as $opt)
                                    @if($opt !== $order->status)
                                        <option value="{{ $opt }}">{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Method</p>
                        <p class="font-medium text-gray-900 uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Status</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($order->payment_status) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Created At</p>
                        <p class="font-medium text-gray-900">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: VENDOR BREAKDOWN -->
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-2">Vendor Breakdown</h2>
                
                @forelse($groupedOrderItems as $storeId => $items)
                    @php
                        $store = $items->first()->store;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-50">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-md font-bold text-gray-800">{{ optional($store)->name ?? 'Unknown Store' }}</h3>
                                <p class="text-sm text-gray-500">Vendor: {{ optional(optional($store)->user)->name ?? 'Unknown Vendor' }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item name</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price unit</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Earnings</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Payout status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($items as $item)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ optional($item->listing)->title ?? $item->product_name ?? $item->item_name ?? $item->title ?? 'Item #' . $item->id }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                                ₦{{ number_format($item->price, 2) }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                ₦{{ number_format($item->earnings ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ₦{{ number_format($item->commission_amount ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ strtolower($item->payout_status) === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ strtolower($item->payout_status) === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ strtolower($item->payout_status) === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ !in_array(strtolower($item->payout_status), ['paid', 'pending', 'failed']) ? 'bg-gray-100 text-gray-800' : '' }}
                                                ">
                                                    {{ ucfirst($item->payout_status ?? 'N/A') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
                        <p class="text-sm text-gray-500">No items found for this order.</p>
                    </div>
                @endforelse
            </div>

            <!-- ORDER TIMELINE -->
            <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Order Timeline</h2>
                
                @if($histories->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($histories as $history)
                            <div class="border-l-2 border-blue-200 pl-4 relative">
                                <div class="absolute w-2 h-2 bg-blue-500 rounded-full -left-[5px] top-1.5"></div>
                                <div class="mb-1">
                                    <span class="text-sm font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</span>
                                    <span class="text-xs text-gray-500 ml-2">{{ $history->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 font-medium pb-1">{{ $history->note }}</p>
                                @if($history->user)
                                    <p class="text-xs text-gray-500">By: {{ $history->user->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-4 text-center text-sm text-gray-500">
                        No timeline records found.
                    </div>
                @endif
            </div>

        </div>

        <!-- Sidebar Content -->
        <div class="space-y-6">
            
            <!-- SECTION 2: CUSTOMER INFO -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border-t-4 border-blue-500">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Customer Info</h2>
                @if($order->user)
                    <div class="space-y-3 mt-4">
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Name</span>
                            <span class="text-sm font-medium text-gray-900">{{ $order->user->name }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Email</span>
                            <span class="text-sm font-medium text-gray-900">{{ $order->user->email }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Phone</span>
                            <span class="text-sm font-medium text-gray-900">{{ $order->user->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 mt-4">Guest User / Information Unavailable</p>
                @endif
            </div>

            <!-- DISPUTE SECTION -->
            <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Dispute Status</h2>
                
                @if($order->is_disputed)
                    <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span class="text-sm font-bold text-red-800">Order is Disputed</span>
                        </div>
                        <p class="text-sm text-red-700 font-medium">{{ $order->dispute_reason }}</p>
                    </div>

                    <!-- REFUND PANEL -->
                    @if(isset($activeRefund))
                        <div class="mt-4 p-4 border rounded-lg {{ $activeRefund->status === 'pending' ? 'bg-yellow-50 border-yellow-200' : ($activeRefund->status === 'approved' ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200') }}">
                            <h3 class="font-semibold text-sm mb-2 text-gray-800">Refund Status: 
                                <span class="capitalize {{ $activeRefund->status === 'pending' ? 'text-yellow-600' : ($activeRefund->status === 'approved' ? 'text-green-600' : ($activeRefund->status === 'manual_review_required' ? 'text-orange-600' : 'text-gray-600')) }}">{{ str_replace('_', ' ', $activeRefund->status) }}</span>
                            </h3>
                            <p class="text-sm text-gray-600 mb-1"><strong>Amount:</strong> ₦{{ number_format($activeRefund->amount, 2) }}</p>
                            <p class="text-sm text-gray-600 mb-3"><strong>Reason:</strong> {{ $activeRefund->reason }}</p>

                            @if($activeRefund->status === 'pending')
                                <div class="flex gap-2 mt-3 pt-3 border-t border-yellow-200/50">
                                    <button wire:click="processRefund({{ $activeRefund->id }}, 'approve')" class="flex-1 px-3 py-1.5 bg-green-600 text-white rounded text-sm font-medium hover:bg-green-700 transition">Approve</button>
                                    <button wire:click="processRefund({{ $activeRefund->id }}, 'reject')" class="flex-1 px-3 py-1.5 bg-gray-600 text-white rounded text-sm font-medium hover:bg-gray-700 transition">Reject</button>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-4 border-t pt-4">
                            <h3 class="text-sm font-semibold text-gray-800 mb-3">Initiate Refund</h3>
                            
                            @if(session('error'))
                                <div class="mb-3 text-sm text-red-600">{{ session('error') }}</div>
                            @endif
                            @if(session('success'))
                                <div class="mb-3 text-sm text-green-600">{{ session('success') }}</div>
                            @endif

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Amount (₦)</label>
                                    <input type="number" step="0.01" wire:model="refundAmount" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 text-sm" placeholder="0.00">
                                    @error('refundAmount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Reason</label>
                                    <textarea wire:model="refundReason" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 text-sm" rows="2" placeholder="Explain refund..."></textarea>
                                    @error('refundReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <button wire:click="initiateRefund" type="button" class="w-full px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition">
                                    Initiate Refund
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="flex items-center space-x-2 text-gray-700 bg-green-50 p-3 rounded-lg border border-green-100 mb-4">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-medium text-green-800">No Active Disputes</span>
                    </div>

                    <div class="space-y-3">
                        <textarea wire:model="disputeReason" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 text-sm" rows="3" placeholder="Reason for dispute..."></textarea>
                        @error('disputeReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        <button wire:click="markAsDisputed" type="button" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                            Mark as Disputed
                        </button>
                    </div>
                @endif
            </div>

            <!-- ADMIN NOTES -->
            <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Admin Notes</h2>
                <div class="space-y-3">
                    <textarea wire:model="adminNotes" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm" rows="5" placeholder="Enter private admin notes..."></textarea>
                    <button wire:click="saveAdminNotes" type="button" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        Save Note
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</div>
