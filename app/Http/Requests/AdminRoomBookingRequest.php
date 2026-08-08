<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRoomBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dates' => 'required',
            'total_rooms' => 'required',
            'nights' => 'required',
            'adult' => 'required|numeric|min:1',
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'customer_email' => 'required',
            'payment_method' => 'required',
            'payment_status' => 'required',
            'booking_status' => 'required',
            'paying_amount' => 'required_if:payment_status,3',
            'discount' => 'nullable|numeric',
            'total_rooms' => 'nullable|numeric'
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'guests.min' => 'The guests must be at least 1 person.',
            'customer_name.required' => 'The customer name field is required',
            'customer_phone.required' => 'The customer phone number field is required',
            'customer_email.required' => 'The customer email field is required',
            'payment_method.required' => 'The payment method field is required',
            'payment_status.required' => 'The payment status field is required',
        ];
    }
}
