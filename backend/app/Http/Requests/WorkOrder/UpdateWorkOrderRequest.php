<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'           => ['sometimes', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'priority'        => ['sometimes', 'in:low,medium,high,critical'],
            'type'            => ['sometimes', 'in:corrective,preventive,inspection,emergency'],
            'asset_id'        => ['nullable', 'exists:assets,id'],
            'assigned_to'     => ['nullable', 'exists:users,id'],
            'due_date'        => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours'    => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
