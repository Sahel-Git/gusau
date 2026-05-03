<?php

namespace App\Http\Controllers\User;

use App\Models\Listing;

class DealController
{
    public function index()
    {
        $deals = Listing::latest()->take(20)->get();

        return view('user.deals.index', compact('deals'));
    }
}
