<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\ComplaintCategoryResource;
use App\Models\ComplaintCategory;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ComplaintCategoryResource::collection(
                ComplaintCategory::query()
                    ->withCount('complaints')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
            ),
        ]);
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = ComplaintCategory::query()->create($request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => new ComplaintCategoryResource($category),
        ], 201);
    }

    public function update(CategoryRequest $request, ComplaintCategory $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => new ComplaintCategoryResource($category->fresh()),
        ]);
    }

    public function destroy(ComplaintCategory $category): JsonResponse
    {
        if ($category->complaints()->exists()) {
            return response()->json([
                'message' => 'This category cannot be deleted because complaints are using it.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
