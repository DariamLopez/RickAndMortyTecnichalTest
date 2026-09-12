<?php

namespace App\Http\Requests\Character;

use App\Enums\CharacterGender;
use App\Enums\CharacterStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CharacterIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', 'min:3'],
            'status' => ['sometimes', 'string', Rule::enum(CharacterStatus::class)],
            'species' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', 'string', Rule::enum(CharacterGender::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
