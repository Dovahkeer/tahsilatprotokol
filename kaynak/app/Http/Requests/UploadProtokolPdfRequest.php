<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProtokolPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            // 2048 KB olan sınırı 10240 KB (10 MB) olarak değiştirdik.
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
