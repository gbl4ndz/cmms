<?php

namespace App\Http\Requests\Meter;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'asset_id'  => ['required', 'exists:assets,id'],
            'name'      => ['required', 'string', 'max:255'],
            'unit'      => ['required', 'string', 'max:50'],
            'frequency' => ['required', 'integer', 'min:1'],
        ];
    }
}
