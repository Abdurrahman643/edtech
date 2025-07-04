<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Lesson;
use App\Models\Question;

class AIController extends Controller
{
    /**
     * Handle a student's question about a lesson and return an AI-generated answer.
     * Validates input, calls OpenAI API, and stores the Q&A.
     */
    public function answer(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'question' => 'required|string',    // The student's question
                'lesson_id' => 'required|exists:lessons,id', // The lesson ID
            ]);

            // Get the lesson
            $lesson = Lesson::findOrFail($request->lesson_id);

            // Prepare the messages for the chat API
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful teaching assistant. Answer questions about the lesson content clearly and concisely.'],
                ['role' => 'user', 'content' => "Lesson: {$lesson->content}\n\nQuestion: {$request->question}"]
            ];

            // Call the OpenAI chat API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'max_tokens' => 150,
                'temperature' => 0.7,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Failed to get AI response',
                    'error' => $response->body()
                ], 500);
            }

            // Extract the AI's answer from the chat response
            $aiAnswer = $response->json('choices.0.message.content');

            // Store the Q&A in history
            $question = Question::create([
                'lesson_id' => $lesson->id,
                'user_id' => $request->user()->id,
                'question' => $request->question,
                'answer' => trim($aiAnswer)
            ]);

            return response()->json([
                'message' => 'AI answer generated and saved',
                'data' => $question
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the AI answer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recommend lessons based on a student's question using AI similarity.
     * Validates input and returns top relevant lessons.
     */
    public function recommend(Request $request)
    {
        try {
            $request->validate([
                'question' => 'required|string',
            ]);

            // Get all lessons for comparison
            $lessons = Lesson::all();
            $recommendations = [];

            // Simple but more effective recommendation logic
            foreach ($lessons as $lesson) {
                // Calculate relevance score based on both title and content
                $titleScore = similar_text(strtolower($request->question), strtolower($lesson->title), $titlePercent);
                $contentScore = similar_text(strtolower($request->question), strtolower($lesson->content), $contentPercent);
                
                // Weighted score (title matches are more important)
                $score = ($titlePercent * 0.7) + ($contentPercent * 0.3);
                
                if ($score > 30) { // Only include somewhat relevant lessons
                    $recommendations[] = [
                        'lesson' => $lesson,
                        'relevance' => $score
                    ];
                }
            }

            // Sort by relevance and get top 5
            usort($recommendations, function($a, $b) {
                return $b['relevance'] <=> $a['relevance'];
            });
            $recommendations = array_slice($recommendations, 0, 5);

            // Extract just the lessons for the response
            $recommendedLessons = array_map(function($rec) {
                return $rec['lesson'];
            }, $recommendations);

            return response()->json([
                'recommendations' => $recommendedLessons
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while recommending lessons.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Q&A history for a specific lesson.
     * Returns paginated Q&A records for the lesson.
     */
    public function getHistory(Request $request, $lessonId)
    {
        try {
            $request->validate([
                'per_page' => 'sometimes|integer|min:1|max:50'
            ]);

            $perPage = $request->query('per_page', 10);
            
            $questions = Question::where('lesson_id', $lessonId)
                ->with('user:id,name') // Only get user's id and name
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json($questions);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching Q&A history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
