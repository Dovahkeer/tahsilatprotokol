<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrimKademeAsamaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'asamalar' => ['required', 'array'],
            'asamalar.*.kademe' => ['required', 'string'],
            'asamalar.*.asama_no' => ['required', 'integer', 'min:1'],
            'asamalar.*.esik_tutari' => ['required', 'numeric', 'min:0'],
            'asamalar.*.prim_orani' => ['required', 'numeric', 'min:0', 'max:100'],
            'asamalar.*.aktif' => ['required', 'boolean'],
        ];
    }
}
