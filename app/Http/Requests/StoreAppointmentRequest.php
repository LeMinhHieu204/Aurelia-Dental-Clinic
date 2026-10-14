<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['required', 'string', 'min:7', 'max:40'],
            'treatment_id' => ['required', Rule::exists('treatments', 'id')->where(fn ($query) => $query->where('is_published', true)->whereNull('archived_at'))],
            'doctor_id' => ['nullable', Rule::exists('doctors', 'id')->where(fn ($query) => $query->where('is_active', true)->where('is_published', true)->whereNull('archived_at'))],
            'location_id' => ['required', Rule::exists('locations', 'id')->where(fn ($query) => $query->where('is_active', true)->where('public_visibility', true))],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'message' => ['nullable', 'string', 'max:2000'],
            'privacy_acknowledgement' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.max' => 'Unable to process this request.',
            'privacy_acknowledgement.accepted' => 'Please acknowledge the privacy notice before sending your request.',
        ];
    }
}
