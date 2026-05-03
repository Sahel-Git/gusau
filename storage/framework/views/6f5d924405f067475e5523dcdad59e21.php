<div style="display: flex; justify-content: space-between; align-items: flex-end; padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.75rem;">
    <div>
        <h3 style="font-weight: 700; font-size: 1.125rem; margin: 0; color: var(--text-color, #1f2937);"><?php echo e($title); ?></h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($subtitle)): ?>
            <p style="font-size: 0.75rem; color: var(--muted-color, #6b7280); margin: 0; margin-top: 4px;"><?php echo e($subtitle); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($action)): ?>
        <a href="#" style="font-size: 0.8125rem; color: var(--primary-color, #10b981); text-decoration: none; font-weight: 600; padding-bottom: 2px;"><?php echo e($action); ?></a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\PROJECTS\gusau\resources\views/user/partials/section-title.blade.php ENDPATH**/ ?>