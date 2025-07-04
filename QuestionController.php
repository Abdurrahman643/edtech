<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

class QuestionController extends Controller
{
    /**
     * Store a student's question and AI answer for a lesson.
     * Validates the request and saves the Q&A to the database.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id', // Lesson must exist
            'question' => 'required|string',            // Student's question
            'answer' => 'required|string',              // AI's answer
        ]);

        // Create the question record in the database
        $question = Question::create([
            'lesson_id' => $request->lesson_id,         // Link to the lesson
            'user_id' => $request->user()->id,          // Authenticated student
            'question' => $request->question,           // The student's question
            'answer' => $request->answer                // The AI's answer
        ]);

        // Return a JSON response with a success message and the saved Q&A
        return response()->json(['message' => 'Q&A saved', 'data' => $question], 201);
    }

    /**
     * List all Q&A for a lesson with pagination.
     */
    public function index(Request $request, $lesson_id)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $questions = Question::where('lesson_id', $lesson_id)->paginate($perPage);
            return response()->json($questions);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching Q&A.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
