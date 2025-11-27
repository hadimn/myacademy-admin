<?php

namespace App\Http\Controllers;

use App\Services\SuggestionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestionsController extends Controller
{
    protected SuggestionsService $suggestionService;
    public function __construct(SuggestionsService $suggestionService) {
        $this->suggestionService = $suggestionService;
    }

    public function getCoursesSuggestion(){
        $user = Auth::user();
        $suggestedCourses = $this->suggestionService->continueCourseSuggestion($user);
        return response()->json([
            "status" => "success",
            "message" => "successfully retrieved all suggested courses!",
            "data" => $suggestedCourses,
        ]);
    }

    public function getLessonsSuggestion(){
        $user = Auth::user();
        $suggestedLessons = $this->suggestionService->continueLessonSuggestion($user);
        return response()->json([
            "status" => "success",
            "message" => "successfully retrieved all suggested Lessons!",
            "data" => $suggestedLessons,
        ]);
    }
}
