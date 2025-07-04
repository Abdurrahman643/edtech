<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{
    /**
     * Store a new lesson (admin upload)
     * Only admins can create lessons. Validates input and logs actions.
     */
    public function store(Request $request)
    {
        try {
            // Only allow admins to create lessons
            if ($request->user()->role !== 'admin') {
                Log::warning('Non-admin attempted to create lesson', [
                    'user_id' => $request->user()->id
                ]);
                return response()->json([
                    'message' => 'Forbidden: Only admins can create lessons.'
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:lessons'
                ],
                'content' => [
                    'required',
                    'string',
                    'min:50'
                ]
            ], [
                'title.unique' => 'A lesson with this title already exists.',
                'content.min' => 'Lesson content must be at least 50 characters.'
            ]);

            // Create a new lesson record
            $lesson = Lesson::create($validated);

            Log::info('Lesson created successfully', [
                'lesson_id' => $lesson->id,
                'admin_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Lesson created successfully',
                'lesson' => $lesson
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::notice('Lesson validation failed', [
                'errors' => $e->errors(),
                'admin_id' => $request->user()->id
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create lesson', [
                'error' => $e->getMessage(),
                'admin_id' => $request->user()->id
            ]);
            return response()->json([
                'message' => 'An error occurred while creating the lesson.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all lessons with optional search
     */
    public function index(Request $request)
    {
        try {
            // Validate query parameters
            if ($request->has('per_page')) {
                $request->validate([
                    'per_page' => 'integer|min:1|max:50'
                ]);
            }
            if ($request->has('search')) {
                $request->validate([
                    'search' => 'string|max:100'
                ]);
            }

            $perPage = $request->query('per_page', 10);
            $search = $request->query('search');

            $query = Lesson::query()
                ->select(['id', 'title', 'content', 'created_at', 'updated_at'])
                ->when($search, function($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                          ->orWhere('content', 'like', "%{$search}%");
                })
                ->orderBy('created_at', 'desc');

            $lessons = $query->paginate($perPage);
            
            return response()->json($lessons);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Invalid query parameters',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching lessons.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified lesson with its recent questions
     */
    public function show($id)
    {
        try {
            $lesson = Lesson::with(['questions' => function($query) {
                    $query->with('user:id,name')
                          ->latest()
                          ->limit(5);
                }])
                ->findOrFail($id);
            
            return response()->json($lesson);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Lesson not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the lesson.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
