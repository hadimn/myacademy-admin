<?php

namespace App\Http\Controllers;

use App\Services\SuggestionsService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuggestionsController extends Controller
{
    use ApiResponseTrait;
    protected SuggestionsService $suggestionService;
    public function __construct(SuggestionsService $suggestionService) {
        $this->suggestionService = $suggestionService;
    }

    public function getCoursesSuggestion(){
        $user = Auth::user();
        $suggestedCourses = $this->suggestionService->continueCourseSuggestion($user);
        
        return $this->successResponse(
            $suggestedCourses,
            "successfully retrieved all suggested courses!",
            Response::HTTP_OK,
        );
    }

    public function getLessonsSuggestion(){
        $user = Auth::user();
        $suggestedLessons = $this->suggestionService->continueLessonSuggestion($user);

        return $this->successResponse(
            $suggestedLessons,
            "successfully retrieved all suggested Lessons!",
            Response::HTTP_OK,
        );
    }
}
