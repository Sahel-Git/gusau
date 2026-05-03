<div style="display: flex; justify-content: space-between; align-items: flex-end; padding: 0 1rem; margin-top: 1.5rem; margin-bottom: 0.75rem;">
    <div>
        <h3 style="font-weight: 700; font-size: 1.125rem; margin: 0; color: var(--text-color, #1f2937);">{{ $title }}</h3>
        @if(isset($subtitle))
            <p style="font-size: 0.75rem; color: var(--muted-color, #6b7280); margin: 0; margin-top: 4px;">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($action))
        <a href="#" style="font-size: 0.8125rem; color: var(--primary-color, #10b981); text-decoration: none; font-weight: 600; padding-bottom: 2px;">{{ $action }}</a>
    @endif
</div>
