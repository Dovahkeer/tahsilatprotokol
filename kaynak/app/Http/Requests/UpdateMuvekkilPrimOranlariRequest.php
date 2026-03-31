<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMuvekkilPrimOranlariRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'muvekkil_oranlari' => ['required', 'array'],
            'muvekkil_oranlari.*.muvekkil_id' => ['required', 'exists:muvekkiller,id'],
            'muvekkil_oranlari.*.prim_orani' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'muvekkil_oranlari.*.aktif' => ['required', 'boolean'],
        ];
    }
}
