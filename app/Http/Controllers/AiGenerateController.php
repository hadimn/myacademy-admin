<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiGenerateRequest;
use App\Services\Ai\AiPromptBuilder;
use App\Services\Ai\OpenAIService;
use Illuminate\Http\JsonResponse;

class AiGenerateController extends Controller
{
    public function generate(
        AiGenerateRequest $request,
        AiPromptBuilder $promptBuilder,
        OpenAIService $openAI
    ): JsonResponse {
        $prompt = $promptBuilder->build(
            resource: $request->resource,
            fields: $request->fields,
            basePrompt: $request->prompt,
            currentData: $request->currentData ?? []
        );

        $result = $openAI->generateStructuredData(
            prompt: $prompt,
            fields: $request->fields
        );

        return response()->json($result);
    }
}
