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

            // YENİ: Mail Order seçildiyse POS Cihazı zorunludur
            'pos_cihazi' => ['nullable', 'string', 'max:255', 'required_if:tahsilat_yontemi,vekil_hesabina_mail_order,vekalet_ucreti_mail_order'],

            'tahsilat_birimleri' => ['required', 'array', 'min:1'],
            'tahsilat_birimleri.*' => ['required', Rule::in(array_keys(config('tahsilat.tahsilat_birimleri', [])))],
            'notlar' => ['nullable', 'string'],

            // YENİ: Elden alındı DEĞİLSE dekont zorunludur
            'dekont' => ['required_unless:tahsilat_yontemi,elden_alindi,vekalet_ucreti_elden_alindi', 'file', 'mimes:pdf,jpg,jpeg,png,webp,bmp,gif,tiff,tif', 'max:15360'],
        ];
    }
}
