<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('articles')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        Category::create($data + ['slug' => Category::makeSlug($data['name'])]);

        return back()->with('status', 'Kategori ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        $category->update($data + ['slug' => Category::makeSlug($data['name'], $category->id)]);

        return redirect()->route('admin.categories.index')->with('status', 'Kategori disimpan.');
    }

    public function destroy(Category $category)
    {
        // Artikel di kategori ini tetap ada, hanya menjadi tanpa kategori.
        $category->delete();

        return back()->with('status', 'Kategori dihapus.');
    }
}
