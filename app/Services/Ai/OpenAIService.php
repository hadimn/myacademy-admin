<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIService
{
    public function generateStructuredData(string $prompt, array $fields): array
    {
        $response = Http::withToken(config('ai.openai.api_key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('ai.openai.model'),
                'temperature' => config('ai.openai.temperature'),
                'max_tokens' => config('ai.openai.max_tokens'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a strict JSON generator.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if (!$response->successful()) {
            // This will tell you if it's "insufficient_quota", "invalid_api_key", etc.
            throw new RuntimeException('AI generation failed: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');

        return $this->extractJson($content, $fields);
    }

    private function extractJson(string $content, array $fields): array
    {
        $json = json_decode($content, true);

        if (!is_array($json)) {
            throw new RuntimeException('Invalid AI JSON response');
        }

        // Whitelist fields
        return array_intersect_key($json, array_flip($fields));
    }
}
