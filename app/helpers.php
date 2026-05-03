<?php

use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    /**
     * Get a business setting securely with caching.
     * Auto-casts predefined boolean/numeric keys.
     */
    function setting($key, $default = null)
    {
        $value = Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });

        // Type casting
        if ($key === 'cod_enabled') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        if (in_array($key, ['commission_percentage', 'delivery_fee_per_km', 'min_withdrawal', 'payout_delay_hours'])) {
            return is_numeric($value) ? (float) $value : $value;
        }

        return $value;
    }
}

if (!function_exists('activity_log')) {
    /**
     * Helper to log activity accurately avoiding sensitive info.
     */
    function activity_log($action, $description = null)
    {
        try {
            // Strip any sensitive data from description if it was inadvertently passed
            if ($description && is_string($description)) {
                $description = preg_replace('/(password|token|secret|hidden)=([^\s&]+)/i', '$1=***', $description);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Failsafe to ensure logging never breaks the app
            report($e);
        }
    }
}
