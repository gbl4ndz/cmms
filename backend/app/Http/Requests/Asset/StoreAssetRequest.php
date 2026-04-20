<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'serial_number'   => ['nullable', 'string', 'max:255'],
            'model'           => ['nullable', 'string', 'max:255'],
            'manufacturer'    => ['nullable', 'string', 'max:255'],
            'purchase_date'   => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'status'          => ['nullable', 'in:active,inactive,under_maintenance,retired'],
            'contractor_id'   => ['nullable', 'exists:contractors,id'],
            'location_id'     => ['nullable', 'exists:locations,id'],
            'area_id'         => ['nullable', 'exists:areas,id'],
            'category_id'     => ['nullable', 'exists:categories,id'],
        ];
    }
}
