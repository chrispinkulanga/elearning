<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CoursePage;
use App\Models\CourseWidget;
use App\Models\CourseTemplate;
use Illuminate\Http\Request;
use App\Services\FileUploadService;

class CourseBuilderController extends Controller
{
    /**
     * Show course builder interface with all pages and widgets
     */
    public function show(Course $course)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to access this course builder'
                ], 403);
            }

            // Load course with pages, widgets, and related data
            $course->load([
                'pages.widgets' => function($query) {
                    $query->orderBy('order_index');
                },
                'category',
                'instructor'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'course' => $course,
                    'available_widget_types' => CourseWidget::getAvailableTypes(),
                    'available_page_types' => [
                        'lesson' => 'Lesson',
                        'quiz' => 'Quiz',
                        'assignment' => 'Assignment',
                        'overview' => 'Overview'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Course builder show error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load course builder'
            ], 500);
        }
    }

    /**
     * Add a new page to the course
     */
    public function addPage(Request $request, Course $course)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'content_type' => 'required|in:lesson,quiz,assignment,overview',
                'order_index' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Determine order index
            $orderIndex = $request->order_index ?? $course->pages()->count();

            $page = $course->pages()->create([
                'title' => $request->title,
                'description' => $request->description,
                'content_type' => $request->content_type,
                'order_index' => $orderIndex,
                'is_published' => false,
                'is_preview' => false
            ]);

            // Reorder other pages if needed
            if ($request->order_index !== null) {
                $this->reorderPages($course, $request->order_index);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Page created successfully',
                'data' => $page->load('widgets')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Add page error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create page'
            ], 500);
        }
    }

    /**
     * Update an existing page
     */
    public function updatePage(Request $request, Course $course, CoursePage $page)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            // Check if page belongs to this course
            if ($page->course_id !== $course->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Page not found in this course'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'content_type' => 'sometimes|required|in:lesson,quiz,assignment,overview',
                'order_index' => 'nullable|integer|min:0',
                'is_published' => 'sometimes|boolean',
                'is_preview' => 'sometimes|boolean',
                'settings' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $page->update($request->only([
                'title', 'description', 'content_type', 'is_published', 
                'is_preview', 'settings'
            ]));

            // Handle order index change
            if ($request->has('order_index') && $request->order_index != $page->order_index) {
                $this->reorderPages($course, $request->order_index, $page->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Page updated successfully',
                'data' => $page->fresh()->load('widgets')
            ]);

        } catch (\Exception $e) {
            Log::error('Update page error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update page'
            ], 500);
        }
    }

    /**
     * Delete a page
     */
    public function deletePage(Course $course, CoursePage $page)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            // Check if page belongs to this course
            if ($page->course_id !== $course->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Page not found in this course'
                ], 404);
            }

            // Delete page (widgets will be deleted via cascade)
            $page->delete();

            // Reorder remaining pages
            $this->reorderPagesAfterDelete($course);

            return response()->json([
                'status' => 'success',
                'message' => 'Page deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete page error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete page'
            ], 500);
        }
    }

    /**
     * Duplicate a page
     */
    public function duplicatePage(Course $course, CoursePage $page)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            // Check if page belongs to this course
            if ($page->course_id !== $course->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Page not found in this course'
                ], 404);
            }

            $duplicatedPage = $page->duplicate();

            return response()->json([
                'status' => 'success',
                'message' => 'Page duplicated successfully',
                'data' => $duplicatedPage->load('widgets')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Duplicate page error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to duplicate page'
            ], 500);
        }
    }

    /**
     * Add a widget to a page
     */
    public function addWidget(Request $request, $page)
    {
        Log::info('=== CONTROLLER REACHED ===', [
            'page' => $page,
            'user_id' => auth()->id(),
            'user_authenticated' => auth()->check(),
            'request_method' => $request->method(),
            'request_url' => $request->url()
        ]);
        
        // Try to find the page manually
        $pageModel = CoursePage::find($page);
        if (!$pageModel) {
            Log::error('Page not found', ['page' => $page]);
            return response()->json([
                'status' => 'error',
                'message' => 'Page not found'
            ], 404);
        }
        
        Log::info('=== ADD WIDGET REQUEST START ===', [
            'page_id' => $pageModel->id,
            'user_id' => auth()->id(),
            'user_roles' => auth()->user() ? auth()->user()->roles->pluck('name') : 'not authenticated',
            'has_file' => $request->hasFile('file'),
            'request_data' => $request->all()
        ]);
        
        try {
            // Check if user owns this course
            if (!$pageModel->course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'widget_type' => 'required|string|in:' . implode(',', array_keys(CourseWidget::getAvailableTypes())),
                'widget_data' => 'required|string', // Changed to string for JSON data
                'order_index' => 'nullable|integer|min:0',
                'settings' => 'nullable|array',
                'file' => 'nullable|file|max:10240' // 10MB max for file uploads
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Parse widget data from JSON string
            $widgetData = json_decode($request->widget_data, true);
            if (!$widgetData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid widget data format'
                ], 422);
            }

            // Handle file upload if present
            if ($request->hasFile('file')) {
                try {
                    $fileUploadService = new FileUploadService();
                    $fileData = $fileUploadService->uploadFile($request->file('file'), 'course-widgets');
                    
                    // Update widget data with file information
                    $widgetData['url'] = asset('storage/' . $fileData['file_path']);
                    $widgetData['file_path'] = $fileData['file_path'];
                    $widgetData['original_filename'] = $fileData['original_filename'];
                    $widgetData['file_size'] = $fileData['file_size'];
                    $widgetData['mime_type'] = $fileData['mime_type'];
                    
                    Log::info('File uploaded successfully', [
                        'file_path' => $fileData['file_path'],
                        'url' => $widgetData['url']
                    ]);
                } catch (\Exception $e) {
                    Log::error('File upload failed: ' . $e->getMessage());
                    return response()->json([
                        'status' => 'error',
                        'message' => 'File upload failed: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Validate widget data based on type
            Log::info('Validating widget data', [
                'widget_type' => $request->widget_type,
                'widget_data' => $widgetData
            ]);
            
            $validationResult = $this->validateWidgetData($request->widget_type, $widgetData);
            if (!$validationResult['valid']) {
                Log::error('Widget validation failed', [
                    'errors' => $validationResult['errors'],
                    'widget_data' => $widgetData
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Widget data validation failed',
                    'errors' => $validationResult['errors']
                ], 422);
            }

               // Determine order index
               $orderIndex = $request->order_index ?? $pageModel->widgets()->count();

               $widget = $pageModel->widgets()->create([
                'widget_type' => $request->widget_type,
                'widget_data' => $widgetData,
                'order_index' => $orderIndex,
                'is_active' => true,
                'settings' => $request->settings ?? []
            ]);

               // Reorder other widgets if needed
               if ($request->order_index !== null) {
                   $this->reorderWidgets($pageModel, $request->order_index);
               }

            return response()->json([
                'status' => 'success',
                'message' => 'Widget added successfully',
                'data' => $widget
            ], 201);

        } catch (\Exception $e) {
            Log::error('Add widget error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add widget'
            ], 500);
        }
    }

    /**
     * Update a widget
     */
    public function updateWidget(Request $request, CourseWidget $widget)
    {
        try {
            // Check if user owns this course
            if (!$widget->page->course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'widget_data' => 'sometimes|required|array',
                'order_index' => 'nullable|integer|min:0',
                'is_active' => 'sometimes|boolean',
                'settings' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate widget data if provided
            if ($request->has('widget_data')) {
                $validationResult = $this->validateWidgetData($widget->widget_type, $request->widget_data);
                if (!$validationResult['valid']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Widget data validation failed',
                        'errors' => $validationResult['errors']
                    ], 422);
                }
            }

            $widget->update($request->only(['widget_data', 'is_active', 'settings']));

            // Handle order index change
            if ($request->has('order_index') && $request->order_index != $widget->order_index) {
                $this->reorderWidgets($widget->page, $request->order_index, $widget->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Widget updated successfully',
                'data' => $widget->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Update widget error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update widget'
            ], 500);
        }
    }

    /**
     * Delete a widget
     */
    public function deleteWidget(CourseWidget $widget)
    {
        try {
            // Check if user owns this course
            if (!$widget->page->course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            $widget->delete();

            // Reorder remaining widgets
            $this->reorderWidgetsAfterDelete($widget->page);

            return response()->json([
                'status' => 'success',
                'message' => 'Widget deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete widget error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete widget'
            ], 500);
        }
    }

    /**
     * Reorder content (pages, sections, widgets)
     */
    public function reorderContent(Request $request, Course $course)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:pages,widgets',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.order_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                if ($request->type === 'pages') {
                    $this->reorderPages($course, null, null, $request->items);
                } elseif ($request->type === 'widgets') {
                    $this->reorderWidgets($request->page_id, null, null, $request->items);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Content reordered successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Reorder content error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reorder content'
            ], 500);
        }
    }

    /**
     * Get available templates
     */
    public function getTemplates(Request $request)
    {
        try {
            $query = CourseTemplate::active();

            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            if ($request->boolean('featured')) {
                $query->featured();
            }

            $templates = $query->with('creator')
                              ->orderBy('usage_count', 'desc')
                              ->paginate($request->get('per_page', 12));

            return response()->json([
                'status' => 'success',
                'data' => $templates
            ]);

        } catch (\Exception $e) {
            Log::error('Get templates error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load templates'
            ], 500);
        }
    }

    /**
     * Apply template to course
     */
    public function applyTemplate(Request $request, Course $course)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'template_id' => 'required|exists:course_templates,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $template = CourseTemplate::findOrFail($request->template_id);

            if (!$template->isActive()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template is not available'
                ], 400);
            }

            // Apply template to course
            $template->applyToCourse($course);

            return response()->json([
                'status' => 'success',
                'message' => 'Template applied successfully',
                'data' => $course->fresh()->load(['pages.widgets', 'category', 'instructor'])
            ]);

        } catch (\Exception $e) {
            Log::error('Apply template error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to apply template'
            ], 500);
        }
    }

    /**
     * Save course as template
     */
    public function saveAsTemplate(Request $request, Course $course)
    {
        try {
            // Check if user owns this course
            if (!$course->isOwnedBy(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to modify this course'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|string|in:' . implode(',', array_keys(CourseTemplate::getAvailableCategories()))
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if course has content
            if ($course->pages()->count() === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course must have at least one page to save as template'
                ], 400);
            }

            $template = CourseTemplate::createFromCourse(
                $course,
                $request->name,
                $request->description,
                $request->category
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Course saved as template successfully',
                'data' => $template
            ], 201);

        } catch (\Exception $e) {
            Log::error('Save as template error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save course as template'
            ], 500);
        }
    }

    /**
     * Helper methods for reordering
     */
    private function reorderPages(Course $course, $newIndex = null, $pageId = null, $items = null)
    {
        if ($items) {
            // Reorder based on provided items array
            foreach ($items as $item) {
                $course->pages()->where('id', $item['id'])->update(['order_index' => $item['order_index']]);
            }
        } else {
            // Reorder after insert/update
            $pages = $course->pages()->orderBy('order_index')->get();
            $currentIndex = 0;
            
            foreach ($pages as $page) {
                if ($page->id == $pageId) {
                    $page->update(['order_index' => $newIndex]);
                } else {
                    if ($currentIndex >= $newIndex) {
                        $currentIndex++;
                    }
                    $page->update(['order_index' => $currentIndex]);
                    $currentIndex++;
                }
            }
        }
    }

    private function reorderWidgets(CoursePage $page, $newIndex = null, $widgetId = null, $items = null)
    {
        if ($items) {
            // Reorder based on provided items array
            foreach ($items as $item) {
                $page->widgets()->where('id', $item['id'])->update(['order_index' => $item['order_index']]);
            }
        } else {
            // Reorder after insert/update
            $widgets = $page->widgets()->orderBy('order_index')->get();
            $currentIndex = 0;
            
            foreach ($widgets as $widget) {
                if ($widget->id == $widgetId) {
                    $widget->update(['order_index' => $newIndex]);
                } else {
                    if ($currentIndex >= $newIndex) {
                        $currentIndex++;
                    }
                    $widget->update(['order_index' => $currentIndex]);
                    $currentIndex++;
                }
            }
        }
    }

    private function reorderPagesAfterDelete(Course $course)
    {
        $pages = $course->pages()->orderBy('order_index')->get();
        foreach ($pages as $index => $page) {
            $page->update(['order_index' => $index]);
        }
    }

    private function reorderWidgetsAfterDelete(CoursePage $page)
    {
        $widgets = $page->widgets()->orderBy('order_index')->get();
        foreach ($widgets as $index => $widget) {
            $widget->update(['order_index' => $index]);
        }
    }

    /**
     * Validate widget data based on type
     */
    private function validateWidgetData($widgetType, $widgetData)
    {
        $errors = [];

        switch ($widgetType) {
            case CourseWidget::TYPE_TEXT:
                if (empty($widgetData['content'] ?? '')) {
                    $errors[] = 'Text content is required';
                }
                break;

            case CourseWidget::TYPE_IMAGE:
                if (empty($widgetData['url'] ?? '')) {
                    $errors[] = 'Image URL is required';
                }
                break;

            case CourseWidget::TYPE_VIDEO:
                if (empty($widgetData['url'] ?? '')) {
                    $errors[] = 'Video URL is required';
                }
                break;

            case CourseWidget::TYPE_MCQ:
                if (empty($widgetData['question'] ?? '')) {
                    $errors[] = 'Question is required';
                }
                if (empty($widgetData['options'] ?? []) || count($widgetData['options']) < 2) {
                    $errors[] = 'At least 2 options are required';
                }
                break;

            case CourseWidget::TYPE_POLL:
                if (empty($widgetData['question'] ?? '')) {
                    $errors[] = 'Poll question is required';
                }
                if (empty($widgetData['options'] ?? []) || count($widgetData['options']) < 2) {
                    $errors[] = 'At least 2 poll options are required';
                }
                break;

            case CourseWidget::TYPE_FILE:
                if (empty($widgetData['url'] ?? '')) {
                    $errors[] = 'File URL is required';
                }
                break;

            case CourseWidget::TYPE_CODE:
                if (empty($widgetData['code'] ?? '')) {
                    $errors[] = 'Code content is required';
                }
                break;

            case CourseWidget::TYPE_EMBED:
                if (empty($widgetData['embed_code'] ?? '')) {
                    $errors[] = 'Embed code is required';
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
