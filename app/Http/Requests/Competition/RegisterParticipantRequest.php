<?php

namespace App\Http\Requests\Competition;

use Illuminate\Foundation\Http\FormRequest;

class RegisterParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'whatsapp_opted_in' => [
                'boolean',
            ],
            'accept_terms' => [
                'required',
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'name.min' => 'الاسم يجب أن يكون حرفين على الأقل',
            'name.max' => 'الاسم طويل جداً',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'accept_terms.required' => 'يجب الموافقة على الشروط والأحكام',
            'accept_terms.accepted' => 'يجب الموافقة على الشروط والأحكام',
        ];
    }
}
