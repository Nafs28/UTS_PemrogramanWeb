<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100', 'unique:categories,name'],
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }
    }
}