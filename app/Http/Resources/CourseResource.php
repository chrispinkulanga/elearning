<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'level' => $this->level,
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'current_price' => $this->current_price,
            'is_free' => $this->is_free,
            'status' => $this->status,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'image' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null, // Alias for frontend compatibility
            'preview_video' => $this->preview_video,
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'outcomes' => $this->outcomes,
            'is_featured' => $this->is_featured,
            // Provide totals whether counted via withCount or computed accessors
            'enrollments_count' => $this->enrollments_count ?? $this->total_enrollments,
            'lessons_count' => $this->lessons_count ?? $this->total_lessons,
            'reviews_count' => $this->reviews_count ?? $this->reviews()->count(),
            'average_rating' => $this->reviews_avg_rating ?? $this->average_rating,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            // Expose builder content. If full pages are loaded (instructor/admin), return them.
            // Otherwise, return published pages for students.
            'pages' => $this->when(
                $this->relationLoaded('pages') || $this->relationLoaded('publishedPages'),
                function () {
                    $pagesRelation = $this->relationLoaded('pages') ? 'pages' : 'publishedPages';
                    $pages = $this->{$pagesRelation}->map(function ($page) {
                        return [
                            'id' => $page->id,
                            'title' => $page->title,
                            'order_index' => $page->order_index,
                            'content_type' => $page->content_type,
                            'is_published' => $page->is_published,
                            'widgets' => $page->relationLoaded('widgets')
                                ? $page->widgets->map(function ($widget) {
                                    return [
                                        'id' => $widget->id,
                                        'widget_type' => $widget->widget_type,
                                        'widget_data' => $widget->widget_data,
                                        'order_index' => $widget->order_index,
                                        'is_active' => $widget->is_active,
                                    ];
                                })
                                : []
                        ];
                    });
                    

                    
                    return $pages;
                }
            ),
            
            // Conditional data
            'is_enrolled' => $this->when(auth()->check(), function () {
                return auth()->user()->isEnrolledIn($this->id);
            }),
            'can_edit' => $this->when(auth()->check(), function () {
                return auth()->user()->hasRole('admin') || $this->instructor_id === auth()->id();
            }),
        ];
    }
}
