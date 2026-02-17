<?php
// app/Http/Controllers/Api/StudentProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\StudentProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StudentProductController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentProduct::with(['user:id,name,avatar', 'course:id,title'])
            ->where('status', 'approved');

        // Apply filters
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        switch ($request->get('sort_by', 'latest')) {
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function show(StudentProduct $product)
    {
        if ($product->status !== 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        // Increment views
        $product->increment('views');

        $product->load(['user:id,name,avatar', 'course:id,title']);

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video_url' => 'nullable|url',
            'files.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is enrolled in the course
        if (!auth()->user()->isEnrolledIn($request->course_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must be enrolled in this course to submit a product'
            ], 403);
        }

        $productData = $request->only(['title', 'description', 'course_id', 'video_url']);
        $productData['user_id'] = auth()->id();

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('student-products/images', 'public');
                $images[] = $path;
            }
            $productData['images'] = $images;
        }

        // Handle file uploads
        if ($request->hasFile('files')) {
            $files = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('student-products/files', 'public');
                $files[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
            $productData['files'] = $files;
        }

        $product = StudentProduct::create($productData);

        return response()->json([
            'status' => 'success',
            'message' => 'Product submitted successfully and is pending approval',
            'data' => $product->load(['user:id,name,avatar', 'course:id,title'])
        ], 201);
    }

    public function update(Request $request, StudentProduct $product)
    {
        // Check if product belongs to current user
        if ($product->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_url' => 'nullable|url',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'files.*' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $productData = $request->only(['title', 'description', 'video_url']);
        $productData['status'] = 'pending'; // Reset to pending after update

        // Handle image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images) {
                foreach ($product->images as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('student-products/images', 'public');
                $images[] = $path;
            }
            $productData['images'] = $images;
        }

        // Handle file uploads
        if ($request->hasFile('files')) {
            // Delete old files
            if ($product->files) {
                foreach ($product->files as $file) {
                    Storage::disk('public')->delete($file['path']);
                }
            }

            $files = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('student-products/files', 'public');
                $files[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
            $productData['files'] = $files;
        }

        $product->update($productData);

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product->load(['user:id,name,avatar', 'course:id,title'])
        ]);
    }

    public function destroy(StudentProduct $product)
    {
        // Check if product belongs to current user or user is admin
        if ($product->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete associated files
        if ($product->images) {
            foreach ($product->images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        if ($product->files) {
            foreach ($product->files as $file) {
                Storage::disk('public')->delete($file['path']);
            }
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ]);
    }

    public function myProducts(Request $request)
    {
        $query = auth()->user()->studentProducts()
            ->with('course:id,title')
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function moderate(Request $request, StudentProduct $product)
    {
        // Only admin can moderate products
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product status updated successfully',
            'data' => $product
        ]);
    }

    public function importFromCsv(Request $request)
    {
        // Only admin can import
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = Excel::toArray([], $request->file('csv_file'));
            $rows = $data[0];
            $imported = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header row

                // Assuming CSV columns: title, description, user_email, course_id, video_url
                if (count($row) >= 4) {
                    $user = \App\Models\User::where('email', $row[2])->first();
                    if ($user) {
                        StudentProduct::create([
                            'title' => $row[0],
                            'description' => $row[1],
                            'user_id' => $user->id,
                            'course_id' => $row[3],
                            'video_url' => $row[4] ?? null,
                            'status' => 'approved',
                        ]);
                        $imported++;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$imported} products"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
