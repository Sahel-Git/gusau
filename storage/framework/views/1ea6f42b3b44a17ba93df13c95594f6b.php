<?php

use App\Models\Listing;
use App\Actions\CreateOrder;
use Illuminate\Validation\ValidationException;

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-12">
    <div class="max-w-7xl mx-auto px-6">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
            <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
            <div class="mb-6 rounded-xl bg-red-50 p-4 text-red-800 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            
            <div class="flex flex-col gap-4">
                <div class="aspect-square bg-zinc-200 dark:bg-zinc-800 rounded-3xl overflow-hidden shadow-sm relative">
                    <img 
                        id="main-product-image"
                        src="<?php echo e(!empty($listing->images) ? Storage::url($listing->images[0]) : asset('fallback.png')); ?>"
                        style="width:100%; height:100%; object-fit:cover; border-radius:10px;"
                    >
                    <div class="absolute top-4 left-4 bg-white/90 dark:bg-zinc-900/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        <?php echo e($listing->type); ?>

                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:10px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $listing->images ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <img 
                            src="<?php echo e(Storage::url($img)); ?>" 
                            style="width:60px; height:60px; cursor:pointer; border-radius: 8px; object-fit: cover;"
                            onclick="changeImage('<?php echo e(Storage::url($img)); ?>')"
                        >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                <script>
                function changeImage(src) {
                    document.getElementById('main-product-image').src = src;
                }
                </script>
            </div>

            
            <div class="flex flex-col">
                <div class="mb-6 flex items-center gap-3">
                    <a href="<?php echo e(route('store.show', $listing->store->slug)); ?>" class="flex items-center gap-2 group hover:opacity-80 transition">
                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600 text-xs shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition">
                            <?php echo e(substr($listing->store->name, 0, 1)); ?>

                        </div>
                        <span class="font-medium text-zinc-600 dark:text-zinc-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition"><?php echo e($listing->store->name); ?></span>
                    </a>
                    <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
                    <span class="text-sm text-zinc-500"><?php echo e($listing->category->name); ?></span>
                </div>

                <h1 class="text-4xl font-extrabold text-zinc-900 dark:text-white tracking-tight text-balance mb-4">
                    <?php echo e($listing->title); ?>

                </h1>

                <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mb-8 border-b border-zinc-200 dark:border-zinc-800 pb-8">
                    $<?php echo e(number_format($listing->price, 2)); ?>

                </div>

                <div class="prose prose-zinc dark:prose-invert text-zinc-600 dark:text-zinc-400 mb-10 leading-relaxed text-balance">
                    <?php echo e($listing->description); ?>

                </div>
                
                
                <div class="mt-auto bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->type === 'product' && $listing->stock === 0): ?>
                        <div class="text-center py-4 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold rounded-2xl">
                            Out of Stock
                        </div>
                    <?php else: ?>
                        <form action="<?php echo e(route('cart.add')); ?>" method="POST" class="flex flex-col gap-4">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="listing_id" value="<?php echo e($listing->id); ?>">
                            <button type="submit" class="w-full h-14 rounded-2xl bg-indigo-600 text-white font-bold text-lg shadow-lg shadow-indigo-600/20 hover:bg-indigo-500 hover:-translate-y-1 transition-all duration-300">
                                Add to Cart
                            </button>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <a href="#" onclick="alert('Proceed to checkout from cart (next phase)')" class="inline-block mt-2 text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                Proceed to Checkout
                            </a>
                        </div>
                        <p class="text-xs text-center text-zinc-400 mt-4 flex items-center justify-center gap-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"/></svg>
                            Secure encrypted checkout
                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($relatedListings) && count($relatedListings) > 0): ?>
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-6 text-zinc-900 dark:text-white">Related Products</h2>
            <div style="display: flex; overflow-x: auto; gap: 1rem; padding-bottom: 1rem; scrollbar-width: none;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $relatedListings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php echo $__env->make('user.partials.product-card', ['product' => $item], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\PROJECTS\gusau\resources\views\livewire/listing-details.blade.php ENDPATH**/ ?>