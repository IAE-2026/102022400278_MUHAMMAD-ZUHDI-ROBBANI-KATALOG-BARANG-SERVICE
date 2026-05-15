<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'current_price' => ['nullable', 'numeric', 'min:0'],
            'auction_start_at' => ['required', 'date'],
            'auction_end_at' => ['required', 'date', 'after:auction_start_at'],
            'status' => ['required', Rule::in(['DRAFT', 'OPEN', 'CLOSED', 'CANCELLED'])],
        ];
    }
}
