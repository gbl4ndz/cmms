<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Foundation\Http\FormRequest;

class AddPartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'part_id'  => ['required', 'exists:parts,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
