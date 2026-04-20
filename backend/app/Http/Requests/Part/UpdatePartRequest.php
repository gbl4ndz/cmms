<?php

namespace App\Http\Requests\Part;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'string', 'max:255'],
            'part_number'      => ['nullable', 'string', 'max:255', 'unique:parts,part_number,' . $this->route('part')],
            'description'      => ['nullable', 'string'],
            'unit'             => ['sometimes', 'string', 'max:50'],
            'unit_cost'        => ['sometimes', 'numeric', 'min:0'],
            'quantity_on_hand' => ['sometimes', 'integer', 'min:0'],
            'minimum_quantity' => ['sometimes', 'integer', 'min:0'],
            'category_id'      => ['nullable', 'exists:categories,id'],
        ];
    }
}
