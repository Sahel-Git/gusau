<a href="<?php echo e(route('listing.show', data_get($product, 'slug', 'mock-slug'))); ?>" style="text-decoration: none; color: inherit; display: block;">
    <div style="min-width: 150px; max-width: 150px; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; flex-shrink: 0; position: relative;">
        <div style="height: 110px; background: #e5e7eb; position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <!-- Safe Image Render -->
            <img src="<?php echo e(data_get($product, 'image') ? asset('storage/'.data_get($product, 'image')) : 'https://via.placeholder.com/300'); ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;" onerror="this.src='https://via.placeholder.com/300'">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($product, 'discount', null)): ?>
                <span style="position: absolute; top: 8px; left: 8px; background: #ef4444; color: white; margin:0; font-size: 0.625rem; font-weight: bold; padding: 2px 6px; border-radius: 4px; z-index: 2;"><?php echo e(data_get($product, 'discount')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <!-- Wishlist Icon Toggle -->
            <button onclick="event.preventDefault(); this.classList.toggle('active'); this.querySelector('svg').setAttribute('fill', this.classList.contains('active') ? 'currentColor' : 'none'); this.style.color = this.classList.contains('active') ? '#ef4444' : '#6b7280';" style="position: absolute; top: 8px; right: 8px; background: white; border: none; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.1); color: #6b7280; padding: 0; z-index: 2; transition: all 0.2s;">
                <svg style="width: 16px; height: 16px; transition: all 0.2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </button>
        </div>
        
        <div style="padding: 0.75rem; flex: 1; display: flex; flex-direction: column;">
            <h4 style="margin: 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <?php echo e(data_get($product, 'title', data_get($product, 'name', 'Product Item'))); ?>

            </h4>
            
            <div style="display: flex; align-items: center; margin-top: 4px; gap: 4px;">
                <span style="color: #fbbf24; font-size: 0.75rem;">★</span>
                <span style="font-size: 0.75rem; font-weight: 500; color: #374151;"><?php echo e(data_get($product, 'rating', '4.8')); ?></span>
                <span style="font-size: 0.75rem; color: #9ca3af;">•</span>
                <span style="font-size: 0.7rem; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: flex; align-items: center; gap: 2px;">
                    ⚡ <?php echo e(data_get($product, 'delivery', '30 mins')); ?>

                </span>
            </div>

            <div style="font-size: 0.7rem; color: #6b7280; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <?php echo e(data_get($product, 'store.name', 'Store')); ?> • <?php echo e(data_get($product, 'category.name', 'Category')); ?>

            </div>
            
            <div style="margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($product, 'stock', 1) > 0 || data_get($product, 'in_stock', true)): ?>
                    <span style="font-size: 0.65rem; color: #059669; background: #d1fae5; padding: 2px 6px; border-radius: 4px; font-weight: 600;">In Stock</span>
                <?php else: ?>
                    <span style="font-size: 0.65rem; color: #d97706; background: #fef3c7; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Limited</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div style="margin-top: auto; padding-top: 8px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-weight: 700; font-size: 0.875rem; color: #10b981;">₦<?php echo e(number_format((float) data_get($product, 'price', 0), 0)); ?></span>
                
                <div class="cart-action-container" style="position: relative;">
                    <button onclick="event.preventDefault(); this.style.display='none'; this.nextElementSibling.style.display='flex';" style="background: #10b981; color: white; border: none; border-radius: 9999px; font-weight: 600; font-size: 0.75rem; padding: 4px 10px; cursor: pointer; white-space: nowrap; transition: background 0.2s;">
                        + Add
                    </button>
                    <div style="display: none; align-items: center; background: #f3f4f6; border-radius: 9999px; overflow: hidden; border: 1px solid #e5e7eb;">
                        <button onclick="event.preventDefault(); let span = this.nextElementSibling; let v = parseInt(span.innerText); if(v > 1) span.innerText = v - 1; else { this.parentElement.style.display='none'; this.parentElement.previousElementSibling.style.display='block'; span.innerText=1; }" style="background: transparent; border: none; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; color: #374151;">-</button>
                        <span style="font-size: 0.75rem; font-weight: 600; width: 16px; text-align: center; color: #1f2937;">1</span>
                        <button onclick="event.preventDefault(); let span = this.previousElementSibling; span.innerText = parseInt(span.innerText) + 1;" style="background: transparent; border: none; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; color: #374151;">+</button>
                    </div>
                </div>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($product, 'socialProof', null)): ?>
                <div style="margin-top: 8px; font-size: 0.65rem; color: #ef4444; font-weight: 500; display: flex; align-items: flex-start; gap: 4px; background: #fef2f2; padding: 4px; border-radius: 4px;">
                    <span style="font-size: 0.7rem; line-height: 1;">🔥</span> 
                    <span style="line-height: 1.1;"><?php echo e(data_get($product, 'socialProof')); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</a>
<?php /**PATH C:\PROJECTS\gusau\resources\views/user/partials/product-card.blade.php ENDPATH**/ ?>