<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier');
        $supplierId = is_object($supplier) ? $supplier->id : $supplier;

        return [
            // 🔹 Información General
            'ruc' => [
                'required',
                'string',
                'regex:/^[0-9]{11}$/',
                Rule::unique('suppliers', 'ruc')->ignore($supplierId),
            ],

            'company_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',

            'phone' => [
                'required',
                'string',
                'regex:/^[+]?[0-9]{7,15}$/',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplierId),
            ],

            // 🔹 Beneficiario
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',

            'account' => [
                'required',
                'string',
                'regex:/^[0-9]{10,34}$/',
            ],

            // 🔹 Banco principal
            'cod_swift' => [
                'required',
                'string',
                'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
            ],

            'bank_name' => 'required|string|max:255',
            'bank_address' => 'required|string|max:255',
            'bank_city' => 'required|string|max:100',
            'bank_country' => 'required|string|max:100',

            'bank_cod_swift' => [
                'required',
                'string',
                'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
            ],

            // 🔹 Banco opcional
            'bank_name2' => 'nullable|string|max:255',
            'bank_address2' => 'nullable|string|max:255',

            'bank_cod_swift2' => [
                'nullable',
                'string',
                'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
            ],

            // 🔹 Otros
            'others' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.regex' => 'El RUC debe contener exactamente 11 dígitos.',
            'ruc.unique' => 'El RUC ya está registrado.',

            'company_name.required' => 'La razón social es obligatoria.',

            'phone.required' => 'El teléfono es obligatorio.',
            'phone.regex' => 'El teléfono no tiene un formato válido.',

            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo debe ser válido.',
            'email.unique' => 'El correo ya está registrado.',

            'account.required' => 'La cuenta bancaria es obligatoria.',
            'account.regex' => 'Debe tener entre 10 y 34 dígitos.',

            'cod_swift.required' => 'El código SWIFT es obligatorio.',
            'cod_swift.regex' => 'Formato SWIFT inválido (ej: ABCDPEMX).',

            'bank_cod_swift.required' => 'El SWIFT del banco es obligatorio.',
            'bank_cod_swift.regex' => 'SWIFT del banco inválido.',

            'bank_cod_swift2.regex' => 'SWIFT del banco secundario inválido.',
        ];
    }
}
