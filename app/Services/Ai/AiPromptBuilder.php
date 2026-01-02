<?php

namespace App\Services\Ai;

class AiPromptBuilder
{
    public function build(
        string $resource,
        array $fields,
        ?string $basePrompt,
        array $currentData
    ): string {
        $fieldList = implode(', ', $fields);

        $existing = empty($currentData)
            ? 'None'
            : json_encode($currentData, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are an expert educational content creator.

Resource type: {$resource}

Fields to generate:
{$fieldList}

Existing data (if any):
{$existing}

Instructions:
- Generate ONLY the requested fields
- Return valid JSON ONLY
- Do NOT include explanations
- Do NOT invent extra fields
- Keep content concise, professional, and realistic

{$basePrompt}
PROMPT;
    }
}
