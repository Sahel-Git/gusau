@extends('user.layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 2rem; color: #1f2937;">Shopping Cart</h1>

    @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($cart))
        <div style="text-align: center; padding: 40px; background: white; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <svg style="width: 64px; height: 64px; margin: 0 auto; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #4b5563; margin-top: 1rem; margin-bottom: 0.5rem;">Your cart is empty</h3>
            <p style="color: #6b7280; margin-bottom: 2rem;">Browse products and add them to your cart</p>
            <a href="{{ route('categories.index') }}" style="background: #10b981; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">Start Shopping</a>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            @php $grandTotal = 0; @endphp

            @foreach($cart as $storeId => $storeData)
                <div style="background: white; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
                    <!-- Store Header -->
                    <div style="background: #f9fafb; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 20px; height: 20px; color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <h3 style="font-size: 1.125rem; font-weight: 700; color: #1f2937; margin: 0;">{{ $storeData['store_name'] }}</h3>
                    </div>

                    <!-- Store Items -->
                    <div style="padding: 1.5rem;">
                        @foreach($storeData['items'] as $listingId => $item)
                            @php
                                $itemTotal = $item['price'] * $item['quantity'];
                                $grandTotal += $itemTotal;
                            @endphp
                            <div style="display: flex; align-items: center; gap: 1.5rem; padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid #f3f4f6;">
                                <!-- Image -->
                                <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 0.5rem; overflow: hidden; flex-shrink: 0;">
                                    @php
                                        $imgSrc = !empty($item['image']) && $item['image'] !== 'fallback.png' 
                                            ? asset('storage/' . $item['image']) 
                                            : asset('fallback.png');
                                    @endphp
                                    <img src="{{ $imgSrc }}" 
                                         alt="{{ $item['title'] }}" 
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.src='{{ asset('fallback.png') }}'">
                                </div>

                                <!-- Details -->
                                <div style="flex: 1;">
                                    <h4 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0 0 0.5rem 0;">{{ $item['title'] }}</h4>
                                    <div style="font-size: 1rem; color: #10b981; font-weight: 700;">₦{{ number_format($item['price'], 2) }}</div>
                                </div>

                                <!-- Quantity Controls -->
                                <div style="display: flex; align-items: center; gap: 1rem; flex-shrink: 0;">
                                    <form action="{{ route('cart.decrease') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="store_id" value="{{ $storeId }}">
                                        <input type="hidden" name="listing_id" value="{{ $listingId }}">
                                        <button type="submit" style="background: #f3f4f6; border: none; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #4b5563; font-weight: bold;">-</button>
                                    </form>
                                    
                                    <span style="font-weight: 600; min-width: 24px; text-align: center;">{{ $item['quantity'] }}</span>
                                    
                                    <form action="{{ route('cart.increase') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="store_id" value="{{ $storeId }}">
                                        <input type="hidden" name="listing_id" value="{{ $listingId }}">
                                        @if($item['quantity'] >= 10)
                                            <button disabled type="button" style="background: #f3f4f6; border: none; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-weight: bold; cursor: not-allowed; opacity: 0.5;">+</button>
                                        @else
                                            <button type="submit" style="background: #f3f4f6; border: none; width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #4b5563; font-weight: bold;">+</button>
                                        @endif
                                    </form>
                                </div>

                                <!-- Total & Remove -->
                                <div style="text-align: right; min-width: 120px; flex-shrink: 0;">
                                    <div style="font-size: 1.125rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">₦{{ number_format($itemTotal, 2) }}</div>
                                    <form action="{{ route('cart.remove') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="store_id" value="{{ $storeId }}">
                                        <input type="hidden" name="listing_id" value="{{ $listingId }}">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 0.875rem; cursor: pointer; padding: 0; text-decoration: underline;">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Cart Summary -->
            <div style="background: white; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 2rem; display: flex; flex-direction: column; align-items: flex-end; margin-top: 1rem;">
                <div style="display: flex; justify-content: space-between; width: 100%; max-width: 400px; margin-bottom: 1.5rem; font-size: 1.25rem;">
                    <span style="color: #4b5563; font-weight: 600;">Grand Total:</span>
                    <span style="font-weight: 800; color: #1f2937;">₦{{ number_format($grandTotal, 2) }}</span>
                </div>
                
                @guest
                    <a href="{{ route('login') }}">
                        <button type="button"
                            style="width:100%; background:#f59e0b; color:white; padding:1rem; border:none; border-radius:8px; font-weight:700;">
                            Login to Checkout
                        </button>
                    </a>
                @endguest

                @auth
                    <form method="POST" action="{{ route('cart.pay') }}">
                        @csrf
                
                        <button type="submit"
                            style="width:100%; background:#10b981; color:white; padding:1rem; border:none; border-radius:8px; font-weight:700;">
                            Pay Now
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    @endif
    
    <div style="height: 100px;"></div>
</div>
@endsection
