<?php

namespace App\Http\Requests\Part;

use Illuminate\Foundation\Http\FormRequest;

class StorePartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'part_number'      => ['nullable', 'string', 'max:255', 'unique:parts,part_number'],
            'description'      => ['nullable', 'string'],
            'unit'             => ['required', 'string', 'max:50'],
            'unit_cost'        => ['required', 'numeric', 'min:0'],
            'quantity_on_hand' => ['required', 'integer', 'min:0'],
            'minimum_quantity' => ['required', 'integer', 'min:0'],
            'category_id'      => ['nullable', 'exists:categories,id'],
        ];
    }
}
