<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $allowedSort = ['id', 'name', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSort, true)
            ? $request->get('sort_by')
            : 'id';
        $sortOrder = $request->get('sort_order') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Category::findOrFail($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(int $id): JsonResponse
    {
        Category::findOrFail($id)->delete();

        return response()->json(['message' => 'Категория удалена.']);
    }
}
