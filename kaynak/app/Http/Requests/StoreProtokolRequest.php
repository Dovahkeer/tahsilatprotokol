<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProtokolRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isAdmin() || (bool) optional($user->yetkiKaydi)->protokol_olusturabilir);
    }

    public function rules(): array
    {
        return [
            'muvekkil_id' => ['required', 'exists:muvekkiller,id'],
            'portfoy_id' => ['nullable', 'exists:portfoyler,id'],
            'protokol_tarihi' => ['required', 'date'],
            'borclu_adi' => ['required', 'string', 'max:255'],
            'borclu_tckn_vkn' => ['nullable', 'string', 'max:20'],
            'muhatap_adi' => ['required', 'string', 'max:255'],
            'muhatap_telefon' => ['required', 'string', 'max:30'],
            'pesinat' => ['required', 'regex:/^\d+(\.\d{2})?$/'],
            'toplam_protokol_tutari' => ['required', 'regex:/^\d+(\.\d{2})?$/'],
            'hacizciler' => ['required', 'array', 'min:1'],
            'hacizciler.*.hacizci_id' => ['required', 'distinct', 'exists:hacizciler,id'],
            'hacizciler.*.haciz_turu' => ['required', Rule::in(array_keys(config('tahsilat.haciz_turleri', [])))],
            'hacizciler.*.pay_orani' => ['nullable', 'numeric', 'between:0,100'],
            'taksitler' => ['nullable', 'array'],
            'taksitler.*.taksit_tarihi' => ['required_with:taksitler', 'date'],
            'taksitler.*.taksit_tutari' => ['required_with:taksitler', 'regex:/^\d+(\.\d{2})?$/'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hacizciler = $this->input('hacizciler', []);
            if (count($hacizciler) === 3) {
                $toplam = 0.0;
                foreach ($hacizciler as $index => $hacizci) {
                    if ($hacizci['pay_orani'] === null || $hacizci['pay_orani'] === '') {
                        $validator->errors()->add("hacizciler.$index.pay_orani", 'Üç hacizcili protokolde pay oranı zorunludur.');
                        continue;
                    }

                    $toplam += (float) $hacizci['pay_orani'];
                }

                if (abs($toplam - 100.0) > 0.01) {
                    $validator->errors()->add('hacizciler', 'Üç hacizcili protokolde pay oranları toplamı 100 olmalıdır.');
                }
            }
        });
    }
}
