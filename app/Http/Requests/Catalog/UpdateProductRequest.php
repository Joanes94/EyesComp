<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'base_unit' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['sometimes', 'required', 'integer', 'min:0'],
            'sale_price' => ['sometimes', 'required', 'integer', 'min:0'],
            'alert_threshold' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'in:active,archived'],

            // Unités
            'units' => ['nullable', 'array'],
            'units.*.id' => ['nullable', 'integer'],
            'units.*.unit_name' => ['required_with:units', 'string', 'max:50'],
            'units.*.conversion_factor' => ['required_with:units', 'integer', 'min:1'],
            'units.*.sale_price' => ['required_with:units', 'integer', 'min:0'],
            'units.*.barcode' => ['nullable', 'string', 'max:100'],

            // Prix par boutique
            'store_prices' => ['nullable', 'array'],
            'store_prices.*.store_id' => ['required_with:store_prices', 'integer', 'exists:stores,id'],
            'store_prices.*.sale_price' => ['nullable', 'integer', 'min:0'],
            'store_prices.*.alert_threshold' => ['nullable', 'integer', 'min:0'],
            'store_prices.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
