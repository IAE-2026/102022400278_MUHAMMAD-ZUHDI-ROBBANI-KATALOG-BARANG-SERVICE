<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'base_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'current_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'auction_start_at' => ['sometimes', 'required', 'date'],
            'auction_end_at' => ['sometimes', 'required', 'date', 'after:auction_start_at'],
            'status' => ['sometimes', 'required', Rule::in(['DRAFT', 'OPEN', 'CLOSED', 'CANCELLED'])],
        ];
    }
}
