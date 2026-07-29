<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseStockRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'store_id' => ['required', 'integer', Rule::exists('stores', 'id')->where('is_active', true)],
            'kilos_available' => ['nullable', 'numeric', 'min:0'],
            'metros_available' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasKilos = is_numeric($this->input('kilos_available')) && (float) $this->input('kilos_available') > 0;
            $hasMetros = is_numeric($this->input('metros_available')) && (float) $this->input('metros_available') > 0;

            if (! $hasKilos && ! $hasMetros) {
                $validator->errors()->add('stock', 'Debes ingresar al menos un valor mayor a 0 en kilos o metros.');
            }
        });
    }
}
