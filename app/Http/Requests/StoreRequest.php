<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->route('store')?->id;

        return [
            'code_product' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stores', 'code_product')
                    ->ignore($storeId),
            ],

            'name_product' => [
                'required',
                'string',
                'max:255',
            ],

            'fabric_type' => [
                'required',
                'string',
                'max:100',
            ],

            'color' => [
                'required',
                'string',
                'max:100',
            ],

            'proveedor' => [
                'required',
                'string',
                'max:255',
            ],

            // ROLLOS
            'kilos' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            // METROS
            'metros' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            // STOCK MÍNIMO
            'minimum_stock' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],

            // PRECIOS
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'public_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'wholesale_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'price_roll' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'special_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'warehouse_id' => [
                'nullable',
                'integer',
                'exists:warehouses,id',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'image_path' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code_product.required' =>
                'El código del producto es obligatorio.',

            'code_product.unique' =>
                'El código del producto ya existe en el sistema.',

            'name_product.required' =>
                'El nombre del producto es obligatorio.',

            'fabric_type.required' =>
                'El tipo de tela es obligatorio.',

            'color.required' =>
                'El color es obligatorio.',

            'proveedor.required' =>
                'El proveedor es obligatorio.',

            'kilos.required' =>
                'La cantidad de rollos es obligatoria.',

            'kilos.numeric' =>
                'Los rollos deben ser un número válido.',

            'kilos.min' =>
                'La cantidad de rollos no puede ser negativa.',

            'metros.required' =>
                'La cantidad de metros es obligatoria.',

            'metros.numeric' =>
                'Los metros deben ser un número válido.',

            'metros.min' =>
                'La cantidad de metros no puede ser negativa.',

            'minimum_stock.required' =>
                'El stock mínimo es obligatorio.',

            'minimum_stock.integer' =>
                'El stock mínimo debe ser un número entero.',

            'minimum_stock.min' =>
                'El stock mínimo no puede ser negativo.',

            'price.required' =>
                'El precio es obligatorio.',

            'price.numeric' =>
                'El precio debe ser un número válido.',

            'price.min' =>
                'El precio no puede ser negativo.',

            'public_price.required' =>
                'El precio público es obligatorio.',

            'public_price.numeric' =>
                'El precio público debe ser un número válido.',

            'public_price.min' =>
                'El precio público no puede ser negativo.',

            'wholesale_price.required' =>
                'El precio mayorista es obligatorio.',

            'wholesale_price.numeric' =>
                'El precio mayorista debe ser un número válido.',

            'wholesale_price.min' =>
                'El precio mayorista no puede ser negativo.',

            'price_roll.required' =>
                'El precio por rollo es obligatorio.',

            'price_roll.numeric' =>
                'El precio por rollo debe ser un número válido.',

            'price_roll.min' =>
                'El precio por rollo no puede ser negativo.',

            'special_price.required' =>
                'El precio especial es obligatorio.',

            'special_price.numeric' =>
                'El precio especial debe ser un número válido.',

            'special_price.min' =>
                'El precio especial no puede ser negativo.',

            'image.image' =>
                'El archivo debe ser una imagen válida.',

            'image_path.image' =>
                'El archivo debe ser una imagen válida.',

            'image.mimes' =>
                'La imagen debe ser JPG, PNG o WebP.',

            'image_path.mimes' =>
                'La imagen debe ser JPG, PNG o WebP.',

            'image.max' =>
                'La imagen no debe superar 2MB.',

            'image_path.max' =>
                'La imagen no debe superar 2MB.',
        ];
    }
}