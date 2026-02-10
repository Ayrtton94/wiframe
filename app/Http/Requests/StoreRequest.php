<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $storeId = $this->route('store')?->id;

        return [
            'code_product' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stores', 'code_product')->ignore($storeId),
            ],
            'name_product' => 'required|string|max:255',
            'fabric_type' => 'required|string|max:100',
            'color' => 'required|string|max:100',
            'proveedor' => 'required|string|max:255',
            'kilos' => 'nullable|numeric|min:0|max:999999.99',
            'metros' => 'nullable|numeric|min:0|max:999999.99',
            'minimum_stock' => 'required|integer|min:0|max:999999',
            'price' => 'required|numeric|min:0|max:999999.99',
            'public_price' => 'required|numeric|min:0|max:999999.99',
            'wholesale_price' => 'required|numeric|min:0|max:999999.99',
            'price_roll' => 'nullable|numeric|min:0|max:999999.99',
            'special_price' => 'nullable|numeric|min:0|max:999999.99',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code_product.required' => 'El código del producto es obligatorio.',
            'code_product.unique' => 'El código del producto ya existe en el sistema.',
            'name_product.required' => 'El nombre del producto es obligatorio.',
            'fabric_type.required' => 'El tipo de tela es obligatorio.',
            'color.required' => 'El color es obligatorio.',
            'proveedor.required' => 'El proveedor es obligatorio.',
            'minimum_stock.required' => 'El stock mínimo es obligatorio.',
            'minimum_stock.integer' => 'El stock mínimo debe ser un número entero.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'public_price.required' => 'El precio público es obligatorio.',
            'wholesale_price.required' => 'El precio al por mayor es obligatorio.',
            'kilos.numeric' => 'Los kilos deben ser un número válido.',
            'metros.numeric' => 'Los metros deben ser un número válido.',
        ];
    }
}
