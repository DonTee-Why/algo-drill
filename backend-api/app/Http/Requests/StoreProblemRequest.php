<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Lang;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProblemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return Auth::check() && $user && $user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:problems,slug'],
            'difficulty' => ['required', 'string', 'in:Easy,Medium,Hard'],
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            'constraints' => ['required', 'array'],
            'constraints.*' => ['string'],
            'description_md' => ['required', 'string'],
            'is_premium' => ['boolean'],
            'signatures' => ['required', 'array', 'min:1'],
            'signatures.*.lang' => ['required', 'string', Rule::enum(Lang::class)],
            'signatures.*.function_name' => ['required', 'string'],
            'signatures.*.params' => ['required', 'array'],
            'signatures.*.returns' => ['required', 'array'],
            'tests' => ['required', 'array', 'min:1'],
            'tests.*.input' => ['required', 'array'],
            'tests.*.expected' => ['required'],
            'tests.*.is_edge' => ['boolean'],
            'tests.*.weight' => ['integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Problem title is required.',
            'slug.unique' => 'This slug is already in use.',
            'difficulty.required' => 'Difficulty level is required.',
            'difficulty.in' => 'Difficulty must be Easy, Medium, or Hard.',
            'tags.required' => 'At least one tag is required.',
            'constraints.required' => 'Constraints are required.',
            'description_md.required' => 'Problem description is required.',
            'signatures.required' => 'At least one signature is required.',
            'signatures.min' => 'At least one signature is required.',
            'signatures.*.lang.required' => 'Language is required for each signature.',
            'signatures.*.function_name.required' => 'Function name is required for each signature.',
            'signatures.*.params.required' => 'Parameters are required for each signature.',
            'signatures.*.returns.required' => 'Return type is required for each signature.',
            'tests.required' => 'At least one test case is required.',
            'tests.min' => 'At least one test case is required.',
            'tests.*.input.required' => 'Input is required for each test case.',
            'tests.*.expected.required' => 'Expected output is required for each test case.',
        ];
    }
}
