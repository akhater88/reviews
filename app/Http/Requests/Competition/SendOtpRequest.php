<?php

namespace App\Http\Requests\Competition;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^(05|5)[0-9]{8}$/', // Saudi mobile format
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'يرجى إدخال رقم جوال سعودي صحيح',
        ];
    }

    /**
     * Get the formatted phone number with country code
     */
    public function getFormattedPhone(): string
    {
        $phone = preg_replace('/\D/', '', $this->phone);

        // Remove leading zero if present
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        // Add country code if not present
        if (!str_starts_with($phone, '966')) {
            $phone = '966' . $phone;
        }

        return $phone;
    }
}
