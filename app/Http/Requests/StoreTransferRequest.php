<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
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
            'from_warehouse_id' => ['required', 'integer', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.store_id' => ['required', 'integer', 'exists:stores,id', 'distinct'],
            'items.*.kilos' => ['nullable', 'numeric', 'min:0'],
            'items.*.metros' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach ($this->input('items', []) as $index => $item) {
                $kilos = (float) ($item['kilos'] ?? 0);
                $metros = (float) ($item['metros'] ?? 0);

                if ($kilos <= 0 && $metros <= 0) {
                    $validator->errors()->add("items.$index", 'Cada item debe tener kilos o metros mayor a cero.');
                }
            }
        });
    }
}
