<?php

namespace App\Http\Requests;

use App\Enums\Lang;
use App\Enums\Stage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitCoachingSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stage' => ['required', Rule::in(Stage::values())],
            'payload' => ['required', 'array'],
            'payload.text' => ['sometimes', 'string'],
            'payload.code' => ['sometimes', 'string'],
            'payload.lang' => ['sometimes', Rule::enum(Lang::class)],
            'payload.complexityAnalysis' => ['sometimes', 'string'],
            'payload.optimizationTechnique' => ['sometimes', 'string'],
            'payload.tradeoffs' => ['sometimes', 'string'],
        ];
    }
}
