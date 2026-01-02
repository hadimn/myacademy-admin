<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tokenCan('admin-access') ?? false;
    }

    public function rules(): array
    {
        return [
            'resource' => ['required', 'string'],
            'fields'   => ['required', 'array', 'min:1'],
            'fields.*' => ['string'],
            'prompt'   => ['nullable', 'string'],
            'currentData' => ['nullable', 'array'],
        ];
    }
}
