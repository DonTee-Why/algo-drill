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
        $stage = $this->input('stage');
        $rules = [
            'stage' => ['required', Rule::in(Stage::values())],
            'payload' => ['required', 'array'],
        ];

        // Stage-specific validation
        if ($stage === Stage::Clarify->value) {
            $rules['payload.inputs_outputs'] = ['required', 'string', 'min:1'];
            $rules['payload.constraints'] = ['required', 'string', 'min:1'];
            $rules['payload.examples'] = ['required', 'string', 'min:1'];
        } else {
            $rules['payload.text'] = ['sometimes', 'string'];
        }

        // Common fields
        $rules['payload.code'] = ['sometimes', 'string', 'required_if:stage,' . Stage::BruteForce->value . ',' . Stage::Optimize->value];
        $rules['payload.lang'] = ['sometimes', Rule::enum(Lang::class), 'required_if:stage,' . Stage::BruteForce->value . ',' . Stage::Optimize->value];
        $rules['payload.complexityAnalysis'] = ['sometimes', 'string', 'required_if:stage,' . Stage::Optimize->value];
        $rules['payload.optimizationTechnique'] = ['sometimes', 'string', 'required_if:stage,' . Stage::Optimize->value];
        $rules['payload.tradeoffs'] = ['sometimes', 'string', 'required_if:stage,' . Stage::Optimize->value];

        return $rules;
    }
}
