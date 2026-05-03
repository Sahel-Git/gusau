<a href="{{ route('store.show', data_get($vendor, 'slug', 'mock-store')) }}" style="text-decoration: none; color: inherit; display: block;">
<div style="min-width: 220px; max-width: 220px; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; flex-shrink: 0; position: relative;">
    @php
        $status_active = data_get($vendor, 'status') === 'active' || data_get($vendor, 'is_open', true);
    @endphp
    
    <div style="height: 90px; background: #d1d5db; display: flex; align-items: center; justify-content: center; position: relative;">
        <img src="{{ data_get($vendor, 'cover_path') ? asset('storage/'.data_get($vendor, 'cover_path')) : 'https://via.placeholder.com/220x90' }}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.7;" onerror="this.style.display='none'">
        <span style="position: absolute; top: 8px; right: 8px; font-size: 0.625rem; font-weight: bold; padding: 2px 6px; border-radius: 4px; background: white; color: {{ $status_active ? '#059669' : '#ef4444' }}; z-index: 2; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
            {{ $status_active ? 'Open' : 'Closed' }}
        </span>
    </div>
    
    <div style="padding: 0.75rem; position: relative;">
        <!-- Vendor Logo/Avatar -->
        <div style="width: 44px; height: 44px; background: white; border-radius: 50%; position: absolute; top: -22px; left: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 2px solid white; z-index: 3;">
            <img src="{{ data_get($vendor, 'logo_path') ? asset('storage/'.data_get($vendor, 'logo_path')) : 'https://via.placeholder.com/100' }}" style="width:100%; height:100%; border-radius:50%; object-fit:cover;" onerror="this.outerHTML='👨‍🍳'">
        </div>
        
        <div style="margin-top: 20px;">
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ data_get($vendor, 'name', 'Vendor Store') }}
                @if(data_get($vendor, 'verified', false))
                    <svg style="width: 14px; height: 14px; color: #3b82f6; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                @endif
            </h4>
            <div style="display: flex; align-items: center; margin-top: 4px; gap: 6px; font-size: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 2px;">
                    <span style="color: #fbbf24;">★</span>
                    <span style="font-weight: 500; color: #374151;">{{ data_get($vendor, 'rating', '4.9') }}</span>
                </div>
                <span style="color: #9ca3af;">•</span>
                <span style="color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ data_get($vendor, 'category.name', data_get($vendor, 'category', 'Store')) }}</span>
            </div>
            
            <div style="display: flex; align-items: center; margin-top: 6px; gap: 8px; font-size: 0.7rem; color: #4b5563;">
                <span style="display: flex; align-items: center; gap: 2px;">
                    🚚 {{ data_get($vendor, 'avg_delivery', '30 mins') }}
                </span>
                <span style="color: #9ca3af;">•</span>
                <span style="display: flex; align-items: center; gap: 2px;">
                    📍 {{ data_get($vendor, 'distance', '2km') }}
                </span>
            </div>
        </div>
    </div>
</div>
</a>
