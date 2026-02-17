<?php
// app/Http/Controllers/Api/QuizController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    public function index(Course $course)
    {
        // Check if user has access to this course
        if (!$this->hasAccess($course)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        $quizzes = $course->quizzes()
            ->with(['userAttempts' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $quizzes
        ]);
    }

    public function show(Course $course, Quiz $quiz)
    {
        // Check if quiz belongs to course
        if ($quiz->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz not found in this course'
            ], 404);
        }

        // Check access
        if (!$this->hasAccess($course)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        $quiz->load([
            'questions' => function ($query) {
                $query->orderBy('sort_order');
            },
            'userAttempts' => function ($query) {
                $query->where('user_id', auth()->id());
            }
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $quiz
        ]);
    }

    public function store(Request $request, Course $course)
    {
        // Check if user can add quizzes to this course
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'required|integer|min:1',
            'passing_score' => 'required|numeric|min:0|max:100',
            'show_results_immediately' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $quizData = $request->all();
        $quizData['course_id'] = $course->id;

        $quiz = Quiz::create($quizData);

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz created successfully',
            'data' => $quiz
        ], 201);
    }

    public function startAttempt(Course $course, Quiz $quiz)
    {
        // Check if quiz belongs to course
        if ($quiz->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz not found in this course'
            ], 404);
        }

        // Check if user is enrolled
        if (!auth()->user()->isEnrolledIn($course->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enrolled in this course'
            ], 403);
        }

        // Check if max attempts reached
        $attemptCount = $quiz->userAttempts()->count();
        if ($attemptCount >= $quiz->max_attempts) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maximum attempts reached'
            ], 400);
        }

        $questions = $quiz->questions()
            ->orderBy('sort_order')
            ->select(['id', 'question', 'type', 'options', 'points', 'sort_order'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'quiz' => $quiz,
                'questions' => $questions,
                'time_limit' => $quiz->time_limit,
                'started_at' => now()
            ]
        ]);
    }

    public function submitAttempt(Request $request, Course $course, Quiz $quiz)
    {
        // Check if quiz belongs to course
        if ($quiz->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz not found in this course'
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
            'answers' => 'required|array',
            'started_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if max attempts reached
        $attemptCount = $quiz->userAttempts()->count();
        if ($attemptCount >= $quiz->max_attempts) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maximum attempts reached'
            ], 400);
        }

        // Calculate score
        $questions = $quiz->questions()->get();
        $totalQuestions = $questions->count();
        $correctAnswers = 0;
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $totalPoints += $question->points;
            $userAnswer = $request->answers[$question->id] ?? null;
            
            if ($this->isAnswerCorrect($question, $userAnswer)) {
                $correctAnswers++;
                $earnedPoints += $question->points;
            }
        }

        $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $passed = $score >= $quiz->passing_score;

        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'answers' => $request->answers,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'passed' => $passed,
            'started_at' => $request->started_at,
            'completed_at' => now(),
        ]);

        $response = [
            'status' => 'success',
            'message' => 'Quiz submitted successfully',
            'data' => [
                'attempt' => $attempt,
                'passed' => $passed,
            ]
        ];

        // Include detailed results if configured to show immediately
        if ($quiz->show_results_immediately) {
            $response['data']['detailed_results'] = $this->getDetailedResults($quiz, $attempt);
        }

        return response()->json($response);
    }

    public function getResults(Course $course, Quiz $quiz, QuizAttempt $attempt)
    {
        // Check if attempt belongs to current user
        if ($attempt->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $results = $this->getDetailedResults($quiz, $attempt);

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }

    private function isAnswerCorrect($question, $userAnswer)
    {
        if ($question->type === 'multiple_choice') {
            return in_array($userAnswer, $question->correct_answers);
        } elseif ($question->type === 'true_false') {
            return $userAnswer === $question->correct_answers[0];
        } elseif ($question->type === 'short_answer') {
            return in_array(strtolower(trim($userAnswer)), array_map('strtolower', $question->correct_answers));
        }

        return false;
    }

    private function getDetailedResults($quiz, $attempt)
    {
        $questions = $quiz->questions()->get();
        $results = [];

        foreach ($questions as $question) {
            $userAnswer = $attempt->answers[$question->id] ?? null;
            $isCorrect = $this->isAnswerCorrect($question, $userAnswer);

            $results[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'user_answer' => $userAnswer,
                'correct_answers' => $question->correct_answers,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation,
                'points' => $isCorrect ? $question->points : 0,
            ];
        }

        return [
            'attempt' => $attempt,
            'questions' => $results,
        ];
    }

    private function hasAccess($course)
    {
        return auth()->check() && (
            auth()->user()->isEnrolledIn($course->id) ||
            $course->instructor_id === auth()->id() ||
            auth()->user()->hasRole('admin')
        );
    }
}