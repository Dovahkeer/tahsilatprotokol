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
            // Sınırı Ghostscript sıkıştırması için 15360 KB (15 MB) olarak güncelledik.
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:15360'],
        ];
    }
}
