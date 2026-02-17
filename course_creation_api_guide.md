# Instructor Course Creation API Guide

This guide shows how instructors can create courses with sections and lessons (like Udemy) using the new API endpoints.

## Course Creation Workflow

### 1. Authentication
First, the instructor needs to be authenticated and have the 'instructor' role.

```bash
POST /api/auth/login
{
    "email": "instructor@example.com",
    "password": "password"
}
```

### 2. Create Course Draft (Step 1)
```bash
POST /api/instructor/courses/draft
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "title": "Complete Flutter Development Course",
    "description": "Learn Flutter development from scratch to advanced",
    "short_description": "Master Flutter app development",
    "category_id": 1,
    "level": "beginner",
    "thumbnail": [file upload]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Course draft created successfully",
    "data": {
        "id": 1,
        "title": "Complete Flutter Development Course",
        "status": "draft",
        "instructor_id": 1,
        ...
    }
}
```

### 3. Add Sections to Course (Step 2)
```bash
POST /api/instructor/sections
Authorization: Bearer {token}

{
    "course_id": 1,
    "title": "Introduction to Flutter",
    "description": "Getting started with Flutter development",
    "sort_order": 0
}
```

**Response:**
```json
{
    "success": true,
    "message": "Section created successfully",
    "data": {
        "id": 1,
        "course_id": 1,
        "title": "Introduction to Flutter",
        "sort_order": 0,
        "lessons": []
    }
}
```

### 4. Add Lessons to Sections
```bash
POST /api/instructor/lessons/section/1
Authorization: Bearer {token}

{
    "title": "What is Flutter?",
    "description": "Introduction to Flutter framework",
    "type": "video",
    "video_url": "https://example.com/video.mp4",
    "video_duration": 600,
    "is_preview": true,
    "sort_order": 0
}
```

**Response:**
```json
{
    "success": true,
    "message": "Lesson created successfully",
    "data": {
        "id": 1,
        "section_id": 1,
        "title": "What is Flutter?",
        "type": "video",
        "is_preview": true,
        ...
    }
}
```

### 5. Upload Video for Lesson (Optional)
```bash
POST /api/instructor/lessons/upload-video
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "video": [video file upload]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Video uploaded successfully",
    "data": {
        "video_url": "/storage/lesson-videos/video.mp4",
        "path": "lesson-videos/video.mp4"
    }
}
```

### 6. Set Course Pricing (Step 3)
```bash
PUT /api/instructor/courses/1/pricing
Authorization: Bearer {token}

{
    "price": 99.99,
    "discounted_price": 79.99,
    "is_free": false,
    "requirements": ["Basic programming knowledge", "Computer with internet"],
    "outcomes": ["Build Flutter apps", "Deploy to app stores"],
    "tags": ["flutter", "mobile", "dart"],
    "access_type": "lifetime",
    "duration_hours": 40
}
```

### 7. Publish Course (Final Step)
```bash
POST /api/instructor/courses/1/publish
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Course submitted for review. It will be published after admin approval.",
    "data": {
        "id": 1,
        "status": "pending",
        ...
    }
}
```

## Additional API Endpoints

### Get Instructor Courses
```bash
GET /api/instructor/courses
Authorization: Bearer {token}
Query Parameters:
- status: draft|pending|approved|rejected
- search: search term
- per_page: pagination limit
```

### Get Course Curriculum
```bash
GET /api/instructor/courses/1/curriculum
Authorization: Bearer {token}
```

### Update Section
```bash
PUT /api/instructor/sections/1
Authorization: Bearer {token}

{
    "title": "Updated Section Title",
    "description": "Updated description"
}
```

### Reorder Sections
```bash
POST /api/instructor/sections/course/1/reorder
Authorization: Bearer {token}

{
    "sections": [
        {"id": 1, "sort_order": 1},
        {"id": 2, "sort_order": 0}
    ]
}
```

### Update Lesson
```bash
PUT /api/instructor/lessons/1
Authorization: Bearer {token}

{
    "title": "Updated Lesson Title",
    "video_duration": 720
}
```

### Reorder Lessons in Section
```bash
POST /api/instructor/lessons/section/1/reorder
Authorization: Bearer {token}

{
    "lessons": [
        {"id": 1, "sort_order": 1},
        {"id": 2, "sort_order": 0}
    ]
}
```

### Delete Course (only if no enrollments)
```bash
DELETE /api/instructor/courses/1
Authorization: Bearer {token}
```

### Delete Section
```bash
DELETE /api/instructor/sections/1
Authorization: Bearer {token}
```

### Delete Lesson
```bash
DELETE /api/instructor/lessons/1
Authorization: Bearer {token}
```

## Database Structure

### Courses Table
- Contains basic course information (title, description, price, etc.)
- Links to instructor (user)
- Has many sections

### Sections Table
- Belongs to a course
- Contains section title, description, sort_order
- Has many lessons

### Lessons Table
- Belongs to both course and section
- Contains lesson content (video, text, etc.)
- Has sort_order within section

## Key Features

1. **Multi-step Course Creation**: Like Udemy's course creation flow
2. **Section-based Organization**: Courses → Sections → Lessons
3. **Drag & Drop Reordering**: Reorder sections and lessons
4. **File Uploads**: Support for video and image uploads
5. **Draft System**: Save progress and publish when ready
6. **Admin Approval**: Courses go to 'pending' status for review
7. **Authorization**: Only course owners can edit their courses

## Error Handling

All endpoints return standardized error responses:

```json
{
    "success": false,
    "errors": {
        "field": ["Error message"]
    }
}
```

Common error scenarios:
- Unauthorized access (403)
- Validation errors (422)
- Course not found (404)
- Cannot delete course with enrollments (400)