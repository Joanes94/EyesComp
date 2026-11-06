<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class PinLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'pin_code' => ['required', 'string', 'digits_between:4,6'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'La boutique est requise pour la connexion par code PIN.',
            'store_id.exists' => 'La boutique sélectionnée est invalide.',
            'pin_code.required' => 'Le code PIN est obligatoire.',
            'pin_code.digits_between' => 'Le code PIN doit comporter entre 4 et 6 chiffres.',
        ];
    }
}
