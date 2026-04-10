<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTahsilatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isAdmin() || (bool) optional($user->yetkiKaydi)->tahsilat_olusturabilir);
    }

    public function rules(): array
    {
        return [
            'protokolsuz' => ['required', 'boolean'],
            'protokol_id' => ['nullable', 'required_if:protokolsuz,0', 'exists:protokoller,id'],
            'odeme_kalemi' => ['nullable', 'required_if:protokolsuz,0', 'string'],
            'muvekkil_id' => ['required', 'exists:muvekkiller,id'],
            'borclu_adi' => ['required', 'string', 'max:255'],
            'borclu_tckn_vkn' => ['nullable', 'string', 'max:20'],
            'tahsilat_tarihi' => ['required', 'date'],
            'tutar' => ['required', 'regex:/^\d+(\.\d{2})?$/'],
            'tahsilat_yontemi' => ['required', Rule::in(array_keys(config('tahsilat.tahsilat_yontemleri', [])))],
            'tahsilat_birimleri' => ['required', 'array', 'min:1'],
            'tahsilat_birimleri.*' => ['required', Rule::in(array_keys(config('tahsilat.tahsilat_birimleri', [])))],
            'notlar' => ['nullable', 'string'],
            'dekont' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:15360'],
        ];
    }
}
