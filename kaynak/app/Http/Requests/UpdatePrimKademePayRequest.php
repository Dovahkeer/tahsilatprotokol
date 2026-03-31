<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrimKademePayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'pay_oranlari' => ['required', 'array'],
            'pay_oranlari.*.ust_kademe' => ['required', 'string'],
            'pay_oranlari.*.alt_kademe' => ['required', 'string'],
            'pay_oranlari.*.ust_kademe_orani' => ['required', 'numeric', 'between:0,100'],
            'pay_oranlari.*.alt_kademe_orani' => ['required', 'numeric', 'between:0,100'],
            'pay_oranlari.*.aktif' => ['required', 'boolean'],
        ];
    }
}
