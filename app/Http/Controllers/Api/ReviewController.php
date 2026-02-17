<?php
// app/Http/Controllers/Api/ReviewController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index(Course $course)
    {
        $reviews = $course->reviews()
            ->where('status', 'approved')
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }

    public function store(Request $request, Course $course)
    {
        // Check if user is enrolled in the course
        if (!auth()->user()->isEnrolledIn($course->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must be enrolled to review this course'
            ], 403);
        }

        // Check if user already reviewed this course
        if (auth()->user()->reviews()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already reviewed this course'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Review::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending', // Reviews need approval
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review submitted successfully and is pending approval',
            'data' => $review->load('user:id,name,avatar')
        ], 201);
    }

    public function update(Request $request, Course $course, Review $review)
    {
        // Check if review belongs to current user
        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending', // Reset to pending after update
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review updated successfully',
            'data' => $review->load('user:id,name,avatar')
        ]);
    }

    public function destroy(Course $course, Review $review)
    {
        // Check if review belongs to current user or user is admin
        if ($review->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted successfully'
        ]);
    }

    public function moderate(Request $request, Review $review)
    {
        // Only admin can moderate reviews
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

        $review->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review status updated successfully',
            'data' => $review
        ]);
    }
}