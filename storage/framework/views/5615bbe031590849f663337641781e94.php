<?php
    // Simulate Laravel session
    $recentlyViewed = session('recently_viewed', [
        ['name' => 'Sneakers', 'price' => '12,500', 'rating' => '4.2', 'delivery' => '2-3 days', 'stock' => 'In Stock'],
        ['name' => 'Wireless Mouse', 'price' => '6,000', 'rating' => '4.8', 'delivery' => 'Next day', 'stock' => 'Limited'],
    ]);

    // Mock arrays for static sections not managed by API yet
    $flashDeals = [
        ['discount' => '-30%', 'price' => '1,500', 'name' => 'Spicy Wings', 'stock' => 'Limited'],
        ['discount' => '-20%', 'price' => '3,200', 'name' => 'Pepperoni Pizza', 'stock' => 'In Stock'],
        ['discount' => '-50%', 'price' => '800', 'name' => 'Cold Brew', 'stock' => 'Limited'],
    ];
?>

<?php $__env->startSection('content'); ?>

    <style>
        .u-horizontal-scroll {
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            padding: 0 1rem 1rem 1rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE 10+ */
        }
        .u-horizontal-scroll::-webkit-scrollbar {
            display: none; /* Chrome, Safari */
        }


        /* Pulse Animation for Chat Button */
        @keyframes circle-pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            70% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .chat-btn { animation: circle-pulse 2s infinite; }

        /* Tooltip behavior */
        .tooltip-container { position: relative; }
        .tooltip-container:hover .tooltip-label { visibility: visible; opacity: 1; transform: translateX(0); }
        .tooltip-label { 
            visibility: hidden; opacity: 0; transform: translateX(10px); transition: all 0.2s ease;
            position: absolute; right: 100%; top: 50%; margin-top: -14px; margin-right: 12px;
            background: #1f2937; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; white-space: nowrap; font-weight: 500;
        }
        .tooltip-label::after {
            content: ''; position: absolute; left: 100%; top: 50%; margin-top: -4px;
            border-width: 4px; border-style: solid; border-color: transparent transparent transparent #1f2937;
        }
    </style>

    <!-- MAIN HERO SECTION -->
    <div class="marketplace-wrapper" style="max-width: 1400px; margin: 0 auto; padding: 1rem;">
        
        <style>
            .hero-container {
                display: flex; gap: 1.5rem; margin-bottom: 2.5rem; align-items: stretch;
            }
            .hero-left {
                flex: 3; background: linear-gradient(135deg, #10b981 0%, #047857 100%); border-radius: 1rem; color: white; padding: 4rem 3rem; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            }
            .hero-right {
                flex: 1; background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; min-width: 300px; display: flex; flex-direction: column;
            }
            .hero-vendor-card {
                display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f3f4f6; transition: transform 0.2s; cursor: pointer; text-decoration: none; color: inherit;
            }
            .hero-vendor-card:hover { transform: translateX(4px); }
            .hero-vendor-card:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
            
            @media (max-width: 992px) {
                .hero-container { flex-direction: column; }
                .hero-left { padding: 2.5rem 1.5rem; }
                .hero-right { min-width: auto; }
            }
        </style>

        <div class="hero-container">
            <!-- LEFT SIDE (Banner) -->
            <div class="hero-left">
                <div style="position: relative; z-index: 10;">
                    <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 999px; font-size: 0.875rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.4); text-transform: uppercase;">Big Sale</span>
                    <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 900; margin: 1.5rem 0 1rem; line-height: 1.1; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Up to 50% OFF<br>Premium Selection</h1>
                    <p style="font-size: 1.125rem; opacity: 0.95; margin-bottom: 2.5rem; max-width: 450px; line-height: 1.5;">Discover thousands of products from the best local and international vendors.</p>
                    <a href="<?php echo e(route('categories.index')); ?>" style="display: inline-block; text-decoration: none; background: white; color: #047857; text-transform: uppercase; font-weight: 800; padding: 1rem 2.5rem; border-radius: 9999px; border: none; font-size: 1.125rem; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2); transition: transform 0.2s;">
                        Shop Now
                    </a>
                </div>
                <!-- Decorative Elements -->
                <div style="position: absolute; right: -5rem; top: -5rem; width: 25rem; height: 25rem; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                <div style="position: absolute; right: 10rem; bottom: -8rem; width: 15rem; height: 15rem; background: rgba(255,255,255,0.15); border-radius: 50%;"></div>
            </div>
            
            <!-- RIGHT SIDE (Featured Stores) -->
            <div class="hero-right">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <h3 style="margin: 0; font-size: 1.125rem; font-weight: 800; color: #1f2937;">Featured Stores</h3>
                    <span style="font-size: 0.75rem; color: #10b981; font-weight: 700; cursor: pointer;">View all</span>
                </div>
                
                <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-around;">
                    <?php $heroVendors = is_object($vendors ?? null) ? $vendors->take(4) : array_slice($vendors ?? [], 0, 4); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($heroVendors) && count($heroVendors)): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $heroVendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <a href="<?php echo e(route('store.show', data_get($vendor, 'slug', 'mock-store'))); ?>" class="hero-vendor-card">
                            <img src="<?php echo e(data_get($vendor, 'logo_path') ? asset('storage/'.data_get($vendor, 'logo_path')) : 'https://via.placeholder.com/60'); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" onerror="this.outerHTML='<div style=\'width:50px;height:50px;background:#e5e7eb;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;\'>🏪</div>'">
                            <div style="flex: 1; overflow: hidden;">
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1f2937; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo e(data_get($vendor, 'name', 'Store Name')); ?></h4>
                                <div style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;"><?php echo e(data_get($vendor, 'category.name', 'Top Seller')); ?></div>
                                <div style="display: flex; align-items: center; gap: 4px; margin-top: 4px;">
                                    <span style="color: #fbbf24; font-size: 0.875rem;">★</span>
                                    <span style="font-size: 0.8rem; font-weight: 600; color: #374151;"><?php echo e(data_get($vendor, 'rating', '4.9')); ?></span>
                                </div>
                            </div>
                        </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php else: ?>
                        <p style="padding:10px;">No items available</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

    <!-- 🔥 Trending Near You -->
    <div id="trending-section">
        <?php echo $__env->make('user.partials.section-title', ['title' => 'Trending Near You', 'action' => 'See All'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="u-horizontal-scroll">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($trendingProducts) && count($trendingProducts)): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trendingProducts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php echo $__env->make('user.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <p style="padding:10px;">No items available</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- ⚡ Fast Delivery Section -->
    <div id="fast-delivery-section">
        <?php echo $__env->make('user.partials.section-title', ['title' => 'Fast Delivery', 'subtitle' => 'Get it today. Delivered under 30 mins.', 'action' => 'Explore'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="u-horizontal-scroll">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($fastDeliveryProducts) && count($fastDeliveryProducts)): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fastDeliveryProducts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php echo $__env->make('user.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <p style="padding:10px;">No items available</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- 🏪 Trusted Vendors -->
    <div id="trusted-vendors-section">
        <?php echo $__env->make('user.partials.section-title', ['title' => 'Trusted Vendors', 'action' => 'View More'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="u-horizontal-scroll">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vendors ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php echo $__env->make('user.partials.vendor-card', ['vendor' => $vendor], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i=0; $i<2; $i++): ?>
                    <?php echo $__env->make('user.partials.vendor-skeleton', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- 💰 Flash Deals -->
    <div id="flash-deals-section">
        <?php echo $__env->make('user.partials.section-title', ['title' => 'Flash Deals ⚡', 'action' => 'See All'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div style="padding: 0 1rem; margin-bottom: 0.5rem;">
            <div style="background: #ef4444; border-radius: 0.5rem; padding: 0.5rem 1rem; color: white; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-weight: 600; font-size: 0.875rem;">Ends in:</span>
                <div id="flash-deal-timer" style="font-weight: 700; font-family: monospace; font-size: 1rem; background: rgba(0,0,0,0.2); padding: 2px 8px; border-radius: 4px;">--:--:--</div>
            </div>
        </div>
        <div class="u-horizontal-scroll">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($flashDeals) && count($flashDeals)): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $flashDeals ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php echo $__env->make('user.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <p style="padding:10px;">No items available</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- 👀 Recently Viewed -->
    <div id="recently-viewed-section">
        <?php echo $__env->make('user.partials.section-title', ['title' => 'Recently Viewed'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="u-horizontal-scroll">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($recentlyViewed) && count($recentlyViewed)): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentlyViewed ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php echo $__env->make('user.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <p style="padding:10px;">No items available</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- ♻️ Buy Again Section (Session Placeholder) -->
    <div id="buy-again-section">
        <?php echo $__env->make('user.partials.section-title', ['title' => 'Buy Again', 'subtitle' => 'Based on your recent activity'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="u-horizontal-scroll">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($recentlyViewed) && count($recentlyViewed)): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentlyViewed ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php echo $__env->make('user.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <p style="padding:10px;">No items available</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- 💡 Recommended -->
    <div id="recommended-section">
        <?php echo $__env->make('user.partials.section-title', ['title' => 'Recommended For You'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="u-horizontal-scroll">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($recommendedProducts) && count($recommendedProducts)): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recommendedProducts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php echo $__env->make('user.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <p style="padding:10px;">No items available</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Bottom Spacing so UI doesn't clip under Nav bar -->
    <div style="height: 30px;"></div>

    <!-- 💬 Floating Chat Button -->
    <div id="floating-chat-section" class="tooltip-container" style="position: fixed; bottom: 85px; right: 16px; z-index: 100;">
        <div class="tooltip-label">Need help?</div>
        <button class="chat-btn" style="background: #10b981; color: white; border: none; width: 56px; height: 56px; border-radius: 50%; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4); display: flex; align-items: center; justify-content: center; cursor: pointer;">
            <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
        </button>
    </div>

    <!-- Enhanced Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Flash Deal Timer Setup (Safe against negatives)
            let time = 2 * 3600 + 15 * 60 + 30; // Starts at 02:15:30
            const timerEl = document.getElementById('flash-deal-timer');
            if(timerEl) {
                const interval = setInterval(() => {
                    if(time <= 0) {
                        clearInterval(interval);
                        timerEl.textContent = '00:00:00';
                        return;
                    }
                    time--;
                    const h = Math.floor(time / 3600).toString().padStart(2, '0');
                    const m = Math.floor((time % 3600) / 60).toString().padStart(2, '0');
                    const s = (time % 60).toString().padStart(2, '0');
                    timerEl.textContent = `${h}:${m}:${s}`;
                }, 1000);
            }
        });
    </script>

    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('user.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\PROJECTS\gusau\resources\views/user/home.blade.php ENDPATH**/ ?>