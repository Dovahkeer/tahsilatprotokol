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
            // 2048 yerine 15360 (15 MB) yapıyoruz
            'dekont' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,bmp,gif,tiff,tif', 'max:15360'],
        ];
    }
}
