<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'base_unit' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['required', 'integer', 'min:0'],
            'sale_price' => ['required', 'integer', 'min:0'],
            'alert_threshold' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'in:active,archived'],

            // Unités de mesure avec conversions (F-PROD-03)
            'units' => ['nullable', 'array'],
            'units.*.unit_name' => ['required_with:units', 'string', 'max:50'],
            'units.*.conversion_factor' => ['required_with:units', 'integer', 'min:1'],
            'units.*.sale_price' => ['required_with:units', 'integer', 'min:0'],
            'units.*.barcode' => ['nullable', 'string', 'max:100'],

            // Prix par boutique (F-PROD-05)
            'store_prices' => ['nullable', 'array'],
            'store_prices.*.store_id' => ['required_with:store_prices', 'integer', 'exists:stores,id'],
            'store_prices.*.sale_price' => ['nullable', 'integer', 'min:0'],
            'store_prices.*.alert_threshold' => ['nullable', 'integer', 'min:0'],
            'store_prices.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du produit est obligatoire.',
            'purchase_price.required' => 'Le prix d\'achat est obligatoire.',
            'purchase_price.integer' => 'Le prix d\'achat en FCFA doit être un nombre entier.',
            'sale_price.required' => 'Le prix de vente est obligatoire.',
            'sale_price.integer' => 'Le prix de vente en FCFA doit être un nombre entier.',
        ];
    }
}
