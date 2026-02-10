<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
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
        $employeeId = $this->route('employee')?->id;

        return [
            'dni' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]{8,20}$/', // DNI válido
                Rule::unique('employees', 'dni')->ignore($employeeId),
            ],
            'name' => 'required|string|max:100',
            'area' => 'required|string|max:100',
            'phone' => 'required|string|regex:/^[+]?[0-9]{7,15}$/',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'area.required' => 'El área es obligatoria.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.regex' => 'El teléfono no tiene un formato válido.',
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'foto.max' => 'La imagen no debe superar 2MB.',
        ];
    }
}
