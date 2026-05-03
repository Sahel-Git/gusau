<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Store;

class HomeController extends Controller
{
    public function index()
    {
        $trendingProducts = Listing::latest()->take(8)->get();

        $fastDeliveryProducts = Listing::take(8)->get();

        $recommendedProducts = Listing::inRandomOrder()->take(8)->get();

        $vendors = Store::latest()->take(6)->get();

        return view('user.home', compact(
            'trendingProducts',
            'fastDeliveryProducts',
            'recommendedProducts',
            'vendors'
        ));
    }
}
