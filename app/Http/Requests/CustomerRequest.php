<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
        $customerId = $this->route('customer')?->id;

        return [
            'dni' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]{8,20}$/', // DNI válido
                Rule::unique('customers', 'dni')->ignore($customerId),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => 'nullable|string|regex:/^[+]?[0-9]{7,15}$/',
            'address' => 'nullable|string|max:500',
            'position' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.regex' => 'El DNI debe contener entre 8 y 20 dígitos.',
            'dni.unique' => 'El DNI ya está registrado en el sistema.',
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'phone.regex' => 'El teléfono no tiene un formato válido.',
        ];
    }
}
