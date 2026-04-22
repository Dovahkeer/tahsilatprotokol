<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTahsilatYetkiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'tahsilat_olusturabilir' => ['required', 'boolean'],
            'protokol_olusturabilir' => ['required', 'boolean'],
            'protokol_duzenleyebilir' => ['required', 'boolean'],
            'toplu_protokol_ekleyebilir' => ['required', 'boolean'],
            'tahsilat_takip_sorumlusu' => ['required', 'boolean'],
            'aktif' => ['required', 'boolean'],
            'tab_permissions' => ['nullable', 'array'],

            // YENİ EKLENECEK KISIM:
            'sorumlu_muvekkiller' => ['nullable', 'array'],
            'sorumlu_muvekkiller.*' => ['integer'],
        ];
    }
}
