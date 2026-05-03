<style>
    .marketplace-header {
        background: white; border-bottom: 1px solid #e5e7eb; position: fixed; top: 0; width: 100%; z-index: 50; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .mh-top { display: flex; align-items: center; justify-content: space-between; padding: 1rem 2rem; max-width: 1400px; margin: 0 auto; gap: 1rem; }
    .mh-logo { font-weight: 800; font-size: 1.5rem; color: #10b981; text-decoration: none; display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .mh-search { flex: 1; max-width: 600px; margin: 0 2rem; display: flex; }
    .mh-icons { display: flex; align-items: center; gap: 1.5rem; flex-shrink: 0; }
    .mh-nav { background: #f8f9fa; border-top: 1px solid #f3f4f6; }
    .mh-nav-inner { display: flex; gap: 2rem; padding: 0.75rem 2rem; max-width: 1400px; margin: 0 auto; font-size: 0.95rem; font-weight: 600; color: #4b5563; overflow-x: auto; white-space: nowrap; scrollbar-width: none; }
    .mh-nav-inner::-webkit-scrollbar { display: none; }
    .mh-nav-item { text-decoration: none; color: inherit; padding-bottom: 4px; }
    .mh-nav-item.active { color: #10b981; border-bottom: 2px solid #10b981; }
    .mh-login-text { font-weight: 600; font-size: 0.95rem; }

    /* Mobile overrides */
    @media (max-width: 768px) {
        .mh-top { flex-direction: column; padding: 1rem; align-items: stretch; }
        .mh-search { margin: 1rem 0 0 0; max-width: none; }
        .mh-icons { position: absolute; top: 1rem; right: 1rem; gap: 1rem; }
        .mh-logo { font-size: 1.25rem; }
        .mh-nav-inner { padding: 0.75rem 1rem; gap: 1.25rem; }
        .mh-login-text { display: none; }
    }
</style>

<header class="marketplace-header">
    <div class="mh-top">
        <!-- LEFT: Logo -->
        <a href="<?php echo e(route('welcome')); ?>" class="mh-logo">
            <svg style="width: 28px; height: 28px;" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
            Sahel
        </a>
        
        <!-- CENTER: Large Search Bar -->
        <div class="mh-search">
            <form action="#" method="GET" style="display: flex; width: 100%;">
                <input type="text" name="q" placeholder="Search products, categories or vendors..." style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #10b981; border-radius: 9999px 0 0 9999px; outline: none; font-size: 1rem;">
                <button type="submit" style="background: #10b981; color: white; border: none; padding: 0 1.5rem; border-radius: 0 9999px 9999px 0; cursor: pointer; font-weight: bold;">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>
        </div>
        
        <!-- RIGHT: Icons & Auth -->
        <div class="mh-icons">
            <div class="mh-login-text" style="display: flex; gap: 8px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('login')); ?>" style="color: #4b5563; text-decoration: none;">Login</a>
                    <span style="color: #d1d5db;">/</span>
                    <a href="<?php echo e(route('register')); ?>" style="color: #4b5563; text-decoration: none;">Register</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('user.dashboard')); ?>" style="color: #10b981; text-decoration: none;">My Account</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <a href="#" style="color: #4b5563; position: relative;">
                <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </a>
            
            <?php
                $cart = session()->get('cart', []);
                $cartCount = 0;
                foreach($cart as $store) {
                    foreach($store['items'] ?? [] as $item) {
                        $cartCount += $item['quantity'] ?? 1;
                    }
                }
            ?>
            <a href="<?php echo e(route('cart.index')); ?>" style="color: #4b5563; position: relative;">
                <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <!-- Badge -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cartCount > 0): ?>
                <span style="position: absolute; top: -6px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; font-weight: bold; display: flex; align-items: center; justify-content: center;"><?php echo e($cartCount); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </a>
        </div>
    </div>
    
    <!-- NAVIGATION MENU BAR -->
    <div class="mh-nav">
        <div class="mh-nav-inner">
            <a href="<?php echo e(route('home')); ?>" class="mh-nav-item <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">Home</a>
            <a href="<?php echo e(route('categories.index')); ?>" class="mh-nav-item <?php echo e(request()->routeIs('categories.*') ? 'active' : ''); ?>">Categories</a>
            <a href="<?php echo e(route('services.index')); ?>" class="mh-nav-item">Services</a>
            <a href="<?php echo e(route('deals.index')); ?>" class="mh-nav-item">Top Deals</a>
            <a href="<?php echo e(route('contact.index')); ?>" class="mh-nav-item">Contact Us</a>
        </div>
    </div>
</header>
<?php /**PATH C:\PROJECTS\gusau\resources\views/user/partials/header.blade.php ENDPATH**/ ?>