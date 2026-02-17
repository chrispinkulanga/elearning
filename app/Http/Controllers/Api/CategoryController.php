<?php
// app/Http/Controllers/Api/CategoryController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::active()
            ->withCount('courses')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        // Debug: Log the request data
        \Log::info('Category creation request', [
            'user_id' => auth()->id(),
            'user_roles' => auth()->user()->roles->pluck('name')->toArray(),
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Check if user has permission to create categories
        if (!auth()->user()->hasAnyRole(['instructor', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only instructors and admins can create categories.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            \Log::warning('Category validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $categoryData = $request->all();
        $categoryData['slug'] = Str::slug($request->name);

        // Ensure color has a default value if not provided
        if (empty($categoryData['color'])) {
            $categoryData['color'] = '#3B82F6';
        }

        // Ensure sort_order has a default value if not provided
        if (!isset($categoryData['sort_order'])) {
            $categoryData['sort_order'] = 0;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category-images', 'public');
            $categoryData['image'] = $path;
        }

        $category = Category::create($categoryData);

        \Log::info('Category created successfully', [
            'user_id' => auth()->id(),
            'category_id' => $category->id,
            'category_name' => $category->name,
            'category_data' => $category->toArray()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $categoryData = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            
            $path = $request->file('image')->store('category-images', 'public');
            $categoryData['image'] = $path;
        }

        $category->update($categoryData);

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        // Check if category has courses
        if ($category->courses()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete category with existing courses'
            ], 400);
        }

        // Delete image
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ]);
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Category status updated successfully',
            'data' => $category
        ]);
    }
}











