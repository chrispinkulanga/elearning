<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForumCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ForumCategory::active()
            ->withCount('topics')
            ->ordered()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to create categories
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only admins can create forum categories.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:forum_categories',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $categoryData = $request->all();
        $categoryData['slug'] = Str::slug($request->name);

        // Set default values
        if (empty($categoryData['color'])) {
            $categoryData['color'] = '#3B82F6';
        }
        if (!isset($categoryData['sort_order'])) {
            $categoryData['sort_order'] = ForumCategory::max('sort_order') + 1;
        }

        $category = ForumCategory::create($categoryData);

        return response()->json([
            'status' => 'success',
            'message' => 'Forum category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ForumCategory $forumCategory)
    {
        $forumCategory->loadCount('topics');

        return response()->json([
            'status' => 'success',
            'data' => $forumCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ForumCategory $forumCategory)
    {
        // Check if user has permission to update categories
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only admins can update forum categories.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:forum_categories,name,' . $forumCategory->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $categoryData = $request->all();
        
        // Update slug if name changed
        if ($request->has('name') && $request->name !== $forumCategory->name) {
            $categoryData['slug'] = Str::slug($request->name);
        }

        $forumCategory->update($categoryData);

        return response()->json([
            'status' => 'success',
            'message' => 'Forum category updated successfully',
            'data' => $forumCategory
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ForumCategory $forumCategory)
    {
        // Check if user has permission to delete categories
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only admins can delete forum categories.'
            ], 403);
        }

        // Check if category has topics
        if ($forumCategory->topics()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete category with existing topics'
            ], 400);
        }

        $forumCategory->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Forum category deleted successfully'
        ]);
    }
}
