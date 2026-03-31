<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHacizciKademeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'hacizciler' => ['required', 'array'],
            'hacizciler.*.hacizci_id' => ['required', 'exists:hacizciler,id'],
            'hacizciler.*.kademe' => ['required', 'string'],
        ];
    }
}
