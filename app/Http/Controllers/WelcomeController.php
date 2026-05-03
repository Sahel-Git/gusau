<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;

class WelcomeController extends Controller
{
    public function index()
    {
        $categories = Category::take(6)->get();
        $latestListings = Listing::latest()->take(6)->get();

        return view('welcome', compact('categories', 'latestListings'));
    }
}
