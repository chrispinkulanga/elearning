<?php
// app/Http/Controllers/Api/ForumController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Forum;
use App\Models\ForumTopic;
use App\Models\ForumReply;
use App\Models\ForumCategory;
use App\Services\FileUploadService;
use App\Services\PollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForumReplyNotification;

class ForumController extends Controller
{
    // Standalone forum methods (no course requirement)
    public function getAllForums()
    {
        $forums = Forum::where('is_active', true)
            ->withCount(['topics', 'replies'])
            ->with(['topics' => function($query) {
                $query->latest()->limit(5);
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $forums
        ]);
    }

    public function getAllTopics(Request $request)
    {
        $query = ForumTopic::with(['user:id,name,avatar', 'forum:id,title'])
            ->withCount(['replies'])
            ->withSum('replies', 'upvotes')
            ->select('*'); // Explicitly select all fields including attachments

        // Apply category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhereJsonContains('tags', $search);
            });
        }

        // Apply sorting - Always prioritize pinned topics first
        $query->orderBy('is_pinned', 'desc'); // Pinned topics always come first
        
        switch ($request->get('sort', 'latest')) {
            case 'popular':
                $query->orderBy('replies_count', 'desc');
                break;
            case 'replies':
                $query->orderBy('replies_count', 'desc');
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'latest':
            default:
                $query->orderBy('is_locked', 'asc') // Unlocked topics before locked
                      ->latest(); // Then by creation date
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $topics = $query->paginate($perPage);

        // Add like status for authenticated users
        if (auth()->check()) {
            $userId = auth()->id();
            $topics->getCollection()->transform(function ($topic) use ($userId) {
                $topic->is_liked = $topic->likes()->where('user_id', $userId)->exists();
                return $topic;
            });
        } else {
            // For unregistered users, set is_liked to false
            $topics->getCollection()->transform(function ($topic) {
                $topic->is_liked = false;
                return $topic;
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $topics
        ]);
    }

    public function createStandaloneTopic(Request $request)
    {
        // DEBUG: Log the start of the method
        \Log::info('=== FORUM TOPIC CREATION START ===');
        \Log::info('Request received at: ' . now());
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request URL: ' . $request->fullUrl());
        \Log::info('Request headers: ' . json_encode($request->headers->all()));
        \Log::info('Request body: ' . json_encode($request->all()));
        \Log::info('User authenticated: ' . (auth()->check() ? 'YES' : 'NO'));
        if (auth()->check()) {
            \Log::info('User ID: ' . auth()->id());
            \Log::info('User email: ' . auth()->user()->email);
        }
        
        try {
            \Log::info('=== VALIDATION START ===');
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'category' => 'required|string|exists:forum_categories,slug',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
                // Poll validation
                'poll_question' => 'nullable|string|max:500',
                'poll_options' => 'nullable|array|min:2|max:10',
                'poll_options.*' => 'string|max:200',
                'poll_is_multiple_choice' => 'nullable|boolean',
                'poll_expires_at' => 'nullable|date|after:now',
                // Attachments validation
                'attachments' => 'nullable|array',
                'attachments.*.filename' => 'required|string',
                'attachments.*.file_path' => 'required|string',
                'attachments.*.file_size' => 'required|integer',
                'attachments.*.mime_type' => 'required|string',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed: ' . json_encode($validator->errors()));
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            \Log::info('=== VALIDATION PASSED ===');

            \Log::info('=== FORUM CREATION START ===');
            // Get or create a general forum for standalone topics
            $forum = Forum::firstOrCreate(
                ['title' => 'General Discussion'],
                [
                    'description' => 'General discussion forum for all users',
                    'course_id' => null,
                    'is_active' => true,
                ]
            );
            \Log::info('Forum found/created: ' . json_encode($forum->toArray()));

            \Log::info('=== TOPIC CREATION START ===');
            
            // Prepare topic data
            $topicData = [
                'title' => $request->title,
                'content' => $request->content,
                'category' => $request->category ?? 'general',
                'forum_id' => $forum->id,
                'user_id' => auth()->id(),
                'tags' => $request->tags ?? [],
                'is_pinned' => false,
                'is_locked' => false,
            ];

            // Add poll data if provided
            if ($request->has('poll_question') && !empty($request->poll_question)) {
                $topicData['poll_question'] = $request->poll_question;
                $topicData['poll_options'] = $request->poll_options ?? [];
                $topicData['poll_is_multiple_choice'] = $request->poll_is_multiple_choice ?? false;
                $topicData['poll_is_active'] = true;
                $topicData['poll_expires_at'] = $request->poll_expires_at;
            }

            // Add attachment data if provided
            if ($request->has('attachments') && !empty($request->attachments)) {
                $topicData['attachments'] = $request->attachments;
                $topicData['attachments_count'] = count($request->attachments);
            }

            $topic = ForumTopic::create($topicData);
            \Log::info('Topic created: ' . json_encode($topic->toArray()));

            \Log::info('=== LOADING RELATIONSHIPS ===');
            $topicWithRelations = $topic->load(['user:id,name,avatar']);
            \Log::info('Topic with relations: ' . json_encode($topicWithRelations->toArray()));

            \Log::info('=== RETURNING SUCCESS ===');
            return response()->json([
                'status' => 'success',
                'message' => 'Topic created successfully',
                'data' => $topicWithRelations
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('=== FORUM TOPIC CREATION ERROR ===');
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile());
            \Log::error('Error line: ' . $e->getLine());
            \Log::error('Request data: ' . json_encode($request->all()));
            \Log::error('User ID: ' . (auth()->check() ? auth()->id() : 'NOT AUTHENTICATED'));
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create topic: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user_authenticated' => auth()->check(),
                    'user_id' => auth()->check() ? auth()->id() : null
                ]
            ], 500);
        }
    }

    public function getStandaloneTopic(ForumTopic $topic)
    {
        $topic->increment('views');
        
        $topic->load([
            'user:id,name,avatar',
            'forum:id,title',
            'replies' => function($query) {
                $query->with(['user:id,name,avatar'])
                      ->select('id', 'content', 'user_id', 'topic_id', 'parent_id', 'upvotes', 'created_at', 'updated_at')
                      ->orderBy('created_at', 'asc');
            }
        ]);

        // Add like status for authenticated users
        if (auth()->check()) {
            $userId = auth()->id();
            $topic->is_liked = $topic->likes()->where('user_id', $userId)->exists();
            
            // Add like status for replies
            $topic->replies->transform(function ($reply) use ($userId) {
                $reply->is_liked = $reply->likes()->where('user_id', $userId)->exists();
                return $reply;
            });
        } else {
            // For unregistered users, set is_liked to false
            $topic->is_liked = false;
            
            // Add like status for replies
            $topic->replies->transform(function ($reply) {
                $reply->is_liked = false;
                return $reply;
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $topic
        ]);
    }

    public function updateStandaloneTopic(Request $request, ForumTopic $topic)
    {
        // Check if user owns the topic or is admin/instructor
        if ($topic->user_id !== auth()->id() && !auth()->user()->hasRole(['admin', 'instructor'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|exists:forum_categories,slug',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            // Poll validation
            'poll_question' => 'nullable|string|max:500',
            'poll_options' => 'nullable|array|min:2|max:10',
            'poll_options.*' => 'string|max:200',
            'poll_is_multiple_choice' => 'nullable|boolean',
            'poll_expires_at' => 'nullable|date|after:now',
            // Attachments validation
            'attachments' => 'nullable|array',
            'attachments.*.filename' => 'required|string',
            'attachments.*.file_path' => 'required|string',
            'attachments.*.file_size' => 'required|integer',
            'attachments.*.mime_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare update data
        $updateData = [
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
        ];

        // Add tags if provided
        if ($request->has('tags')) {
            $updateData['tags'] = $request->tags;
        }

        // Add poll data if provided
        if ($request->has('poll_question')) {
            if (!empty($request->poll_question)) {
                $updateData['poll_question'] = $request->poll_question;
                $updateData['poll_options'] = $request->poll_options ?? [];
                $updateData['poll_is_multiple_choice'] = $request->poll_is_multiple_choice ?? false;
                $updateData['poll_is_active'] = true;
                $updateData['poll_expires_at'] = $request->poll_expires_at;
            } else {
                // Clear poll data if empty question
                $updateData['poll_question'] = null;
                $updateData['poll_options'] = null;
                $updateData['poll_is_multiple_choice'] = false;
                $updateData['poll_is_active'] = false;
                $updateData['poll_expires_at'] = null;
            }
        }

        // Add attachment data if provided
        if ($request->has('attachments')) {
            $updateData['attachments'] = $request->attachments;
            $updateData['attachments_count'] = count($request->attachments);
        }

        $topic->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Topic updated successfully',
            'data' => $topic->load(['user:id,name,avatar'])
        ]);
    }

    public function deleteStandaloneTopic(ForumTopic $topic)
    {
        // Check if user owns the topic or is admin/instructor
        if ($topic->user_id !== auth()->id() && !auth()->user()->hasRole(['admin', 'instructor'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $topic->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Topic deleted successfully'
        ]);
    }

    public function pinStandaloneTopic(ForumTopic $topic)
    {
        // Only admins and instructors can pin topics
        if (!auth()->user()->hasRole(['admin', 'instructor'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $topic->update(['is_pinned' => !$topic->is_pinned]);

        return response()->json([
            'status' => 'success',
            'message' => $topic->is_pinned ? 'Topic pinned successfully' : 'Topic unpinned successfully',
            'data' => $topic->load(['user:id,name,avatar'])
        ]);
    }

    public function lockStandaloneTopic(ForumTopic $topic)
    {
        // Only admins and instructors can lock topics
        if (!auth()->user()->hasRole(['admin', 'instructor'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $topic->update(['is_locked' => !$topic->is_locked]);

        return response()->json([
            'status' => 'success',
            'message' => $topic->is_locked ? 'Topic locked successfully' : 'Topic unlocked successfully',
            'data' => $topic->load(['user:id,name,avatar'])
        ]);
    }

    public function likeStandaloneTopic(Request $request, ForumTopic $topic)
    {
        $user = auth()->user();
        
        // Check if user already liked this topic
        $existingLike = $topic->likes()->where('user_id', $user->id)->first();
        
        if ($existingLike) {
            // Unlike: remove the like
            $existingLike->delete();
            $topic->decrement('likes_count');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Like removed successfully',
                'data' => [
                    'is_liked' => false,
                    'likes_count' => $topic->likes_count
                ]
            ]);
        } else {
            // Like: create new like
            $topic->likes()->create([
                'user_id' => $user->id,
                'likeable_type' => ForumTopic::class,
                'likeable_id' => $topic->id
            ]);
            $topic->increment('likes_count');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Topic liked successfully',
                'data' => [
                    'is_liked' => true,
                    'likes_count' => $topic->likes_count
                ]
            ]);
        }
    }

    public function likeStandaloneReply(Request $request, $replyId)
    {
        $user = auth()->user();
        $reply = ForumReply::findOrFail($replyId);
        
        // Check if user already liked this reply
        $existingLike = $reply->likes()->where('user_id', $user->id)->first();
        
        if ($existingLike) {
            // Unlike: remove the like
            $existingLike->delete();
            $reply->decrement('upvotes');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Like removed successfully',
                'data' => [
                    'is_liked' => false,
                    'upvotes' => $reply->upvotes
                ]
            ]);
        } else {
            // Like: create new like
            $reply->likes()->create([
                'user_id' => $user->id,
                'likeable_type' => ForumReply::class,
                'likeable_id' => $reply->id
            ]);
            $reply->increment('upvotes');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Reply liked successfully',
                'data' => [
                    'is_liked' => true,
                    'upvotes' => $reply->upvotes
                ]
            ]);
        }
    }

    public function createStandaloneReply(Request $request, ForumTopic $topic)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:1',
            'parent_id' => 'nullable|exists:forum_replies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $reply = ForumReply::create([
            'content' => $request->content,
            'topic_id' => $topic->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
        ]);

        // Send notification to topic author if it's not the same user
        if ($topic->user_id !== auth()->id()) {
            $topic->user->notify(new ForumReplyNotification($reply, $topic));
        }

        // If this is a nested reply, also notify the parent reply author
        if ($request->parent_id) {
            $parentReply = ForumReply::find($request->parent_id);
            if ($parentReply && $parentReply->user_id !== auth()->id() && $parentReply->user_id !== $topic->user_id) {
                $parentReply->user->notify(new ForumReplyNotification($reply, $topic));
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reply posted successfully',
            'data' => $reply->load(['user:id,name,avatar'])
        ], 201);
    }

    public function searchAllTopics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }



        $query = ForumTopic::where(function($q) use ($request) {
            $q->where('title', 'like', '%' . $request->q . '%')
              ->orWhere('content', 'like', '%' . $request->q . '%')
              ->orWhereJsonContains('tags', $request->q);
        });

        // Apply category filter if provided
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Apply sorting - Always prioritize pinned topics first
        $query->orderBy('is_pinned', 'desc'); // Pinned topics always come first
        
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'newest':
            case 'latest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'most_replied':
                $query->withCount(['replies'])->orderBy('replies_count', 'desc');
                break;
            case 'most_viewed':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $topics = $query->with(['user:id,name,avatar', 'forum:id,title'])
        ->withCount(['replies'])
        ->select('*') // Explicitly select all fields including attachments
        ->paginate($request->get('per_page', 15));

        // Add like status for authenticated users
        if (auth()->check()) {
            $userId = auth()->id();
            $topics->getCollection()->transform(function ($topic) use ($userId) {
                // Since we don't have a likes table yet, set is_liked to false
                $topic->is_liked = false;
                return $topic;
            });
        } else {
            // For unregistered users, set is_liked to false
            $topics->getCollection()->transform(function ($topic) {
                $topic->is_liked = false;
                return $topic;
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $topics
        ]);
    }

    // Course-specific forum methods (existing)
    public function index(Course $course)
    {
        // Check if user has access to this course
        if (!$this->hasAccess($course)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        $forums = $course->forums()
            ->where('is_active', true)
            ->withCount(['topics', 'replies'])
            ->with(['topics' => function($query) {
                $query->latest()->limit(5);
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $forums
        ]);
    }

    public function getTopics(Course $course, Forum $forum)
    {
        // Check if forum belongs to course
        if ($forum->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forum not found in this course'
            ], 404);
        }

        // Check access
        if (!$this->hasAccess($course)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        $topics = $forum->topics()
            ->with(['user:id,name,avatar'])
            ->withCount(['replies'])
            ->withSum('replies', 'upvotes')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('is_locked', 'asc')
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $topics
        ]);
    }

    public function createTopic(Request $request, Course $course, Forum $forum)
    {
        try {
            // Check if forum belongs to course
            if ($forum->course_id !== $course->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forum not found in this course'
                ], 404);
            }

            // Check if user is enrolled
            if (!auth()->user()->isEnrolledIn($course->id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not enrolled in this course'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'category' => 'required|string|exists:forum_categories,slug',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $topic = ForumTopic::create([
                'title' => $request->title,
                'content' => $request->content,
                'category' => $request->category ?? 'general',
                'forum_id' => $forum->id,
                'user_id' => auth()->id(),
                'tags' => $request->tags ?? [],
                'is_pinned' => false,
                'is_locked' => false,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Topic created successfully',
                'data' => $topic->load(['user:id,name,avatar'])
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Course forum topic creation error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => auth()->id(),
                'course_id' => $course->id,
                'forum_id' => $forum->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create topic: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTopic(Course $course, Forum $forum, ForumTopic $topic)
    {
        // Check if topic belongs to forum and course
        if ($topic->forum_id !== $forum->id || $forum->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Topic not found'
            ], 404);
        }

        // Check access
        if (!$this->hasAccess($course)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        // Increment view count
        $topic->increment('views');

        $topic->load([
            'user:id,name,avatar',
            'replies' => function($query) {
                $query->with(['user:id,name,avatar', 'replies.user:id,name,avatar'])
                      ->orderBy('created_at', 'asc');
            }
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $topic
        ]);
    }

    public function replyToTopic(Request $request, Course $course, Forum $forum, ForumTopic $topic)
    {
        // Check if topic belongs to forum and course
        if ($topic->forum_id !== $forum->id || $forum->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Topic not found'
            ], 404);
        }

        // Check if user is enrolled
        if (!auth()->user()->isEnrolledIn($course->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enrolled in this course'
            ], 403);
        }

        // Check if topic is locked
        if ($topic->is_locked) {
            return response()->json([
                'status' => 'error',
                'message' => 'This topic is locked'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:1',
            'parent_id' => 'nullable|exists:forum_replies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $reply = ForumReply::create([
            'content' => $request->content,
            'topic_id' => $topic->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'upvotes' => 0,
        ]);

        // Send notification to topic author
        if ($topic->user_id !== auth()->id()) {
            $topic->user->notify(new ForumReplyNotification($reply, $topic));
        }

        // Send notification to parent reply author if replying to a reply
        if ($request->parent_id) {
            $parentReply = ForumReply::find($request->parent_id);
            if ($parentReply && $parentReply->user_id !== auth()->id() && $parentReply->user_id !== $topic->user_id) {
                $parentReply->user->notify(new ForumReplyNotification($reply, $topic));
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reply posted successfully',
            'data' => $reply->load(['user:id,name,avatar'])
        ], 201);
    }

    public function upvoteReply(ForumReply $reply)
    {
        $user = auth()->user();
        
        // Check if user already upvoted
        if ($reply->upvotes()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Already upvoted'
            ], 400);
        }

        // Add upvote
        $reply->upvotes()->create(['user_id' => $user->id]);
        $reply->increment('upvotes');

        return response()->json([
            'status' => 'success',
            'message' => 'Upvoted successfully',
            'data' => ['upvotes' => $reply->upvotes]
        ]);
    }

    public function searchTopics(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'forum_id' => 'nullable|exists:forums,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = ForumTopic::whereHas('forum', function($q) use ($course) {
            $q->where('course_id', $course->id);
        });

        if ($request->forum_id) {
            $query->where('forum_id', $request->forum_id);
        }

        $topics = $query->where(function($q) use ($request) {
            $q->where('title', 'like', '%' . $request->query . '%')
              ->orWhere('content', 'like', '%' . $request->query . '%')
              ->orWhereJsonContains('tags', $request->query);
        })
        ->with(['user:id,name,avatar', 'forum:id,title'])
        ->withCount(['replies'])
        ->latest()
        ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $topics
        ]);
    }

    public function pinTopic(Request $request, Course $course, Forum $forum, ForumTopic $topic)
    {
        // Check if user is instructor or admin
        if (!auth()->user()->hasRole(['instructor', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $topic->update(['is_pinned' => !$topic->is_pinned]);

        return response()->json([
            'status' => 'success',
            'message' => $topic->is_pinned ? 'Topic pinned' : 'Topic unpinned',
            'data' => $topic
        ]);
    }

    public function lockTopic(Request $request, Course $course, Forum $forum, ForumTopic $topic)
    {
        // Check if user is instructor or admin
        if (!auth()->user()->hasRole(['instructor', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $topic->update(['is_locked' => !$topic->is_locked]);

        return response()->json([
            'status' => 'success',
            'message' => $topic->is_locked ? 'Topic locked' : 'Topic unlocked',
            'data' => $topic
        ]);
    }

    private function hasAccess($course)
    {
        $user = auth()->user();
        
        // Admin has access to everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Instructor has access to their courses
        if ($user->hasRole('instructor') && $course->instructor_id === $user->id) {
            return true;
        }

        // Student must be enrolled
        if ($user->hasRole('student')) {
            return $user->isEnrolledIn($course->id);
        }

        return false;
    }

    // Poll methods
    public function createPoll(Request $request, $topicId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'question' => 'required|string|max:500',
                'options' => 'required|array|min:2|max:10',
                'options.*' => 'required|string|max:200',
                'is_multiple_choice' => 'boolean',
                'expires_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $topic = ForumTopic::findOrFail($topicId);
            
            // Check if user owns the topic or is admin
            if ($topic->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $pollService = new PollService();
            $poll = $pollService->createPoll($topicId, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Poll created successfully',
                'data' => $poll
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create poll: ' . $e->getMessage()
            ], 500);
        }
    }

    public function votePoll(Request $request, $topicId, $pollId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'option_id' => 'required|exists:poll_options,id',
            ]);

                    if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pollService = new PollService();
            $results = $pollService->vote($pollId, $request->option_id, auth()->id());

            return response()->json([
                'status' => 'success',
                'message' => 'Vote recorded successfully',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record vote: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPollResults($topicId, $pollId)
    {
        try {
            $pollService = new PollService();
            $results = $pollService->getPollResults($pollId);

            return response()->json([
                'status' => 'success',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get poll results: ' . $e->getMessage()
            ], 500);
        }
    }

    // File attachment methods
    public function uploadAttachment(Request $request, $topicId)
    {
        try {
            \Log::info('Upload attachment request received', [
                'topic_id' => $topicId,
                'user_id' => auth()->id(),
                'has_file' => $request->hasFile('file'),
                'file_name' => $request->file('file') ? $request->file('file')->getClientOriginalName() : null,
                'file_size' => $request->file('file') ? $request->file('file')->getSize() : null
            ]);

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                \Log::error('File validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $topic = ForumTopic::findOrFail($topicId);
            \Log::info('Topic found', ['topic_id' => $topic->id, 'user_id' => $topic->user_id]);
            
            // Check if user owns the topic or is admin
            if ($topic->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
                \Log::error('Unauthorized upload attempt', [
                    'topic_user_id' => $topic->user_id,
                    'auth_user_id' => auth()->id()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            \Log::info('Starting file upload process');
            $fileUploadService = new FileUploadService();
            $fileData = $fileUploadService->uploadFile($request->file('file'), 'topic-attachments');
            \Log::info('File uploaded successfully', ['file_data' => $fileData]);

            // Create attachment data for JSON storage
            $attachmentData = [
                'filename' => $fileData['filename'],
                'original_filename' => $fileData['original_filename'],
                'file_path' => $fileData['file_path'],
                'file_size' => $fileData['file_size'],
                'mime_type' => $fileData['mime_type'],
                'file_type' => $fileData['file_type'],
                'is_image' => $fileData['is_image'],
                'is_video' => $fileData['is_video'],
                'thumbnail_path' => $fileData['thumbnail_path'],
                'uploaded_at' => now()->toISOString(),
                'uploaded_by' => auth()->id()
            ];

            // Get current attachments or initialize empty array
            $currentAttachments = $topic->attachments ?? [];
            $currentAttachments[] = $attachmentData;
            \Log::info('Updated attachments array', ['count' => count($currentAttachments)]);

            // Update the topic with new attachment
            $topic->update([
                'attachments' => $currentAttachments,
                'attachments_count' => count($currentAttachments)
            ]);
            \Log::info('Topic updated with attachment data');

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'data' => $attachmentData
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Attachment upload failed: ' . $e->getMessage(), [
                'topic_id' => $topicId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAttachment($topicId, $attachmentId)
    {
        try {
            $topic = ForumTopic::findOrFail($topicId);
            
            // Check if user owns the topic or is admin
            if ($topic->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $currentAttachments = $topic->attachments ?? [];
            
            // Find the attachment by filename (since we don't have separate IDs anymore)
            $attachmentIndex = null;
            $attachmentToDelete = null;
            
            foreach ($currentAttachments as $index => $attachment) {
                if ($attachment['filename'] === $attachmentId) {
                    $attachmentIndex = $index;
                    $attachmentToDelete = $attachment;
                    break;
                }
            }

            if ($attachmentIndex === null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attachment not found'
                ], 404);
            }

            // Delete the file from storage
            $fileUploadService = new FileUploadService();
            $fileUploadService->deleteFile($attachmentToDelete['file_path'], $attachmentToDelete['thumbnail_path'] ?? null);
            
            // Remove attachment from array
            unset($currentAttachments[$attachmentIndex]);
            $currentAttachments = array_values($currentAttachments); // Re-index array

            // Update the topic
            $topic->update([
                'attachments' => $currentAttachments,
                'attachments_count' => count($currentAttachments)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Attachment deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Attachment deletion failed: ' . $e->getMessage(), [
                'topic_id' => $topicId,
                'attachment_id' => $attachmentId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete attachment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAttachmentInfo()
    {
        try {
            $fileUploadService = new FileUploadService();
            $info = $fileUploadService->getAllowedTypes();

            return response()->json([
                'status' => 'success',
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get attachment info: ' . $e->getMessage()
            ], 500);
        }
    }
}