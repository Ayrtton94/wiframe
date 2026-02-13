<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveTransferRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.transfer_item_id' => ['required', 'integer', 'exists:transfer_items,id', 'distinct'],
            'items.*.kilos_received' => ['nullable', 'numeric', 'min:0'],
            'items.*.metros_received' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
