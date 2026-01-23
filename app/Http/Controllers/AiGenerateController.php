<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiGenerateRequest;
use App\Services\Ai\AiPromptBuilder;
use App\Services\Ai\GeminiService;
use Illuminate\Http\JsonResponse;

class AiGenerateController extends Controller
{
    public function generate(
        AiGenerateRequest $request,
        AiPromptBuilder $promptBuilder,
        GeminiService $gemini
    ): JsonResponse {
        $prompt = $promptBuilder->build(
            resource: $request->resource,
            fields: $request->fields,
            basePrompt: $request->prompt,
            currentData: $request->currentData ?? []
        );

        $result = $gemini->generateStructuredData(
            prompt: $prompt,
            fields: $request->fields
        );

        return response()->json($result);
    }
}
