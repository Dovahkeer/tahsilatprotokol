<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTahsilatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'borclu_adi' => ['required', 'string', 'max:255'],
            'borclu_tckn_vkn' => ['nullable', 'string', 'max:20'],
            'tahsilat_tarihi' => ['required', 'date'],
            'tutar' => ['required', 'regex:/^\d+(\.\d{2})?$/'],
            'tahsilat_yontemi' => ['required', Rule::in(array_keys(config('tahsilat.tahsilat_yontemleri', [])))],
            // YENİ EKLENEN SATIR:
            'pos_cihazi' => ['nullable', 'string', 'max:255', 'required_if:tahsilat_yontemi,vekil_hesabina_mail_order,vekalet_ucreti_mail_order'],

            'tahsilat_birimleri' => ['required', 'array', 'min:1'],
            'tahsilat_birimleri.*' => ['required', Rule::in(array_keys(config('tahsilat.tahsilat_birimleri', [])))],
            'notlar' => ['nullable', 'string'],
        ];
    }
}
