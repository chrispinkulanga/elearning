<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseStoreRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('instructor');
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255|unique:courses,title',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'price' => 'required_if:is_free,false|numeric|min:0|max:9999',
            'is_free' => 'boolean',
            'status' => 'required|in:draft,pending,approved,rejected',
            'preview_video' => 'nullable|url',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:50',
            'requirements' => 'nullable|array|max:20',
            'requirements.*' => 'string|max:200',
            'outcomes' => 'nullable|array|max:20',
            'outcomes.*' => 'string|max:200',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'title.unique' => 'A course with this title already exists.',
            'tags.max' => 'Maximum 10 tags allowed.',
            'requirements.max' => 'Maximum 20 requirements allowed.',
            'outcomes.max' => 'Maximum 20 outcomes allowed.',
        ];
    }
}
