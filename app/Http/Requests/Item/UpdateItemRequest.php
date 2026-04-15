<?php

namespace App\Http\Requests\Item;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'uuid', 'exists:categories,id'],
            'supplier_id' => ['sometimes', 'required', 'uuid', 'exists:suppliers,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'sku')->ignore($this->route('item')),
            ],
            'unit' => ['sometimes', 'required', 'string', 'max:50'],
            'stock_current' => ['sometimes', 'required', 'integer', 'min:0'],
            'stock_minimum' => ['sometimes', 'required', 'integer', 'min:0'],
            'purchase_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
