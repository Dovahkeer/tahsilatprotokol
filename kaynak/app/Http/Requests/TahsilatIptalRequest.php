<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TahsilatIptalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'iptal_nedeni' => ['nullable', 'string'],
        ];
    }
}
