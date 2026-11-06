<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:100'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du propriétaire est obligatoire.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'business_name.required' => 'Le nom du commerce est obligatoire.',
            'business_type.required' => 'Le type de commerce est obligatoire.',
        ];
    }
}
