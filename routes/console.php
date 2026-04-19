<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use App\Models\Listing;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $listings = Listing::where('status', 'rejected')
        ->where('updated_at', '<', now()->subHours(24))
        ->get();

    foreach ($listings as $listing) {
        if (!empty($listing->images)) {
            foreach ($listing->images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }
        $listing->delete();
    }
})->hourly();
