<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => ['sometimes', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'serial_number'   => ['nullable', 'string', 'max:255'],
            'model'           => ['nullable', 'string', 'max:255'],
            'manufacturer'    => ['nullable', 'string', 'max:255'],
            'purchase_date'   => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'status'          => ['nullable', 'in:active,inactive,under_maintenance,retired'],
            'contractor_id'   => ['nullable', 'exists:contractors,id'],
            'location_id'     => ['nullable', 'exists:locations,id'],
            'area_id'         => ['nullable', 'exists:areas,id'],
            'category_id'     => ['nullable', 'exists:categories,id'],
        ];
    }
}
