<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'  => ['required', 'in:open,in_progress,on_hold,closed'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
