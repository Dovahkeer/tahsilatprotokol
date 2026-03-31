<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadTahsilatDekontRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'dekont' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
