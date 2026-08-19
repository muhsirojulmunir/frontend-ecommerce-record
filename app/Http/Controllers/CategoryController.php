<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar produk berdasarkan kategori tertentu.
     */
    public function show(Category $category, Request $request)
    {
        $query = $category->activeProducts()->with(['category', 'activeDiscount']);

        // Urutan tampil produk
        $sort = $request->get('sort', 'terbaru');
        $query = match ($sort) {
            'termurah' => $query->orderBy('price', 'asc'),
            'termahal' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $category,
            'sort' => $sort,
        ]);
    }
}
