<?php

namespace App\Http\Controllers\User;

use App\Models\Category;

class CategoryController
{
    public function index()
    {
        $categories = Category::all();

        return view('user.categories.index', compact('categories'));
    }
}
