<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('items');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSorts = ['name', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        $categories = $query->paginate($perPage);

        return ApiResponse::success(
            CategoryResource::collection($categories)->response()->getData(true),
            'Categories retrieved successfully.'
        );
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('items');

        return ApiResponse::success(
            new CategoryResource($category),
            'Category retrieved successfully.'
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        $category->loadCount('items');

        return ApiResponse::success(
            new CategoryResource($category),
            'Category created successfully.'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);
        $category->loadCount('items');

        return ApiResponse::success(
            new CategoryResource($category),
            'Category updated successfully.'
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->items()->exists()) {
            return ApiResponse::conflict('Category has items and cannot be deleted.');
        }

        $category->delete();

        return ApiResponse::success(
            null,
            'Category deleted successfully.'
        );
    }
}