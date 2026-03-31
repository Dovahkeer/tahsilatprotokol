<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProtokolRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $protokol = $this->route('protokol');

        return $user && (
            $user->isAdmin()
            || (bool) optional($user->yetkiKaydi)->protokol_duzenleyebilir
            || ((string) ($protokol?->created_by ?? '') === (string) $user->id)
        );
    }

    public function rules(): array
    {
        return [
            'muvekkil_id' => ['required', 'exists:muvekkiller,id'],
            'portfoy_id' => ['nullable', 'exists:portfoyler,id'],
            'protokol_tarihi' => ['required', 'date'],
            'borclu_adi' => ['required', 'string', 'max:255'],
            'borclu_tckn_vkn' => ['nullable', 'string', 'max:20'],
            'muhatap_adi' => ['nullable', 'string', 'max:255'],
            'muhatap_telefon' => ['nullable', 'string', 'max:30'],
            'pesinat' => ['required', 'regex:/^\d+(\.\d{2})?$/'],
            'toplam_protokol_tutari' => ['required', 'regex:/^\d+(\.\d{2})?$/'],
            'aktif' => ['sometimes', 'boolean'],
            'hacizciler' => ['required', 'array', 'min:1'],
            'hacizciler.*.hacizci_id' => ['required', 'distinct', 'exists:hacizciler,id'],
            'hacizciler.*.haciz_turu' => ['required', Rule::in(array_keys(config('tahsilat.haciz_turleri', [])))],
            'hacizciler.*.pay_orani' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hacizciler = $this->input('hacizciler', []);
            if (count($hacizciler) === 3) {
                $toplam = collect($hacizciler)->sum(fn (array $hacizci) => (float) ($hacizci['pay_orani'] ?? 0));

                if (collect($hacizciler)->contains(fn (array $hacizci) => $hacizci['pay_orani'] === null || $hacizci['pay_orani'] === '')) {
                    $validator->errors()->add('hacizciler', 'Üç hacizcili protokolde pay oranı zorunludur.');
                }

                if (abs($toplam - 100.0) > 0.01) {
                    $validator->errors()->add('hacizciler', 'Üç hacizcili protokolde pay oranları toplamı 100 olmalıdır.');
                }
            }
        });
    }
}
