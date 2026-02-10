<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
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
        $supplierId = $this->route('supplier')?->id;

        return [
            // Información General - REQUERIDO
            'ruc' => [
                'required',
                'string',
                'regex:/^[0-9]{11}$/', // Validar formato RUC Perú (11 dígitos)
                Rule::unique('suppliers', 'ruc')->ignore($supplierId),
            ],
            'company_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'phone' => 'required|string|regex:/^[+]?[0-9]{7,15}$/', // Teléfono internacional
            'email' => 'required|email|max:255',

            // Datos del Beneficiario - REQUERIDO
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'account' => 'required|string|regex:/^[0-9]{10,34}$/', // IBAN/número de cuenta válido

            // Datos del Banco Beneficiario - REQUERIDO
            'cod_swift' => 'required|string|regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', // SWIFT Code validation
            'bank_name' => 'required|string|max:255',
            'bank_address' => 'required|string|max:255',
            'bank_city' => 'required|string|max:100',
            'bank_country' => 'required|string|max:100',
            'bank_cod_swift' => 'required|string|regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',

            // Banco Internacional (OPCIONAL)
            'bank_name2' => 'sometimes|nullable|string|max:255',
            'bank_address2' => 'sometimes|nullable|string|max:255',
            'bank_cod_swift2' => 'sometimes|nullable|string|regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',

            // Información Adicional (OPCIONAL)
            'others' => 'sometimes|nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.regex' => 'El RUC debe contener 11 dígitos.',
            'ruc.unique' => 'El RUC ya está registrado en el sistema.',
            'company_name.required' => 'La razón social es obligatoria.',
            'phone.regex' => 'El teléfono no tiene un formato válido.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'account.regex' => 'El número de cuenta debe tener entre 10 y 34 dígitos.',
            'cod_swift.regex' => 'El código SWIFT no tiene un formato válido (ej: ABCDPEMX).',
            'bank_cod_swift.regex' => 'El código SWIFT del banco no tiene un formato válido.',
            'bank_cod_swift2.regex' => 'El código SWIFT del banco secundario no tiene un formato válido.',
        ];
    }
}
