<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    /**
     * Display sections for a specific course
     */
    public function index(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        
        // Check if user is the instructor of this course
        if ($course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        $sections = $course->sections()
            ->with(['lessons' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sections,
            'course' => $course->only(['id', 'title', 'status'])
        ]);
    }

    /**
     * Store a new section
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $course = Course::findOrFail($request->course_id);
        
        // Check if user is the instructor of this course
        if ($course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        // If no sort_order provided, set it to be last
        $sortOrder = $request->sort_order ?? ($course->sections()->max('sort_order') + 1);

        $section = Section::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $sortOrder,
            'is_published' => true, // Default to published
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => $section->load('lessons')
        ], 201);
    }

    /**
     * Display a specific section
     */
    public function show($id)
    {
        $section = Section::with(['course', 'lessons' => function($query) {
            $query->orderBy('sort_order');
        }])->findOrFail($id);

        // Check if user is the instructor of this course
        if ($section->course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $section
        ]);
    }

    /**
     * Update a section
     */
    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        // Check if user is the instructor of this course
        if ($section->course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $section->update($request->only(['title', 'description', 'sort_order', 'is_published']));

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'data' => $section->load('lessons')
        ]);
    }

    /**
     * Delete a section
     */
    public function destroy($id)
    {
        $section = Section::findOrFail($id);

        // Check if user is the instructor of this course
        if ($section->course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        // Check if section has lessons
        if ($section->lessons()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete section that contains lessons. Please delete or move lessons first.'
            ], 400);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully'
        ]);
    }

    /**
     * Reorder sections
     */
    public function reorder(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        
        // Check if user is the instructor of this course
        if ($course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:sections,id',
            'sections.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->sections as $sectionData) {
            Section::where('id', $sectionData['id'])
                ->where('course_id', $courseId)
                ->update(['sort_order' => $sectionData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sections reordered successfully'
        ]);
    }
}