<?php

namespace App\Http\Requests\Area;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'location_id' => ['sometimes', 'exists:locations,id'],
            'name'        => ['sometimes', 'string', 'max:255'],
            'area_code'   => ['nullable', 'string', 'max:50'],
        ];
    }
}
