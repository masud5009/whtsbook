<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class ExtendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules =  [
            'price' => 'required',
            'package_id' => 'required',
            'start_date' => 'required',
            'expire_date' => 'required',
            'payment_method' => $this->price != 0 ? 'required' : '',
            'receipt' => $this->is_receipt == 1 ? 'required | mimes:jpeg,jpg,png' : '',
        ];

        if ($this->payment_method === 'stripe') {
            $rules['stripeToken'] = 'required';
        }

        if ($this->payment_method === 'iyzico') {
            $rules['identity_number'] = 'required';
            $rules['address'] = 'required';
            $rules['zip_code'] = 'required';
            $rules['country'] = 'required';
            $rules['city'] = 'required';
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'receipt.required' => __('The receipt field image is required when instruction required receipt image')
        ];
    }
}
