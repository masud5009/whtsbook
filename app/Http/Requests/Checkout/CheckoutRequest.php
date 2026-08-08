<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
    public function rules()
    {
        $rules =  [
            'first_name' => 'required',
            'last_name' => 'required',
            'company_name' => 'required',
            'username' => 'required|alpha_num|unique:users',
            'password' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'country' => 'required',
            'price' => 'required',
            'city' => 'required',
            'address' => 'required',
            'payment_method' => $this->price != 0 ? 'required' : '',
            'receipt' => $this->is_receipt == 1 ? 'required | mimes:jpeg,jpg,png' : '',
        ];

        if ($this->payment_method === 'stripe') {
            $rules['stripeToken'] = 'required';
        }

        if ($this->payment_method === 'iyzico') {
            $rules['identity_number'] = 'required';
            $rules['zip_code'] = 'required';
        }
        return $rules;
    }

    public function messages(): array
    {

        return [
            'first_name.required' => __('The first name field is required.'),
            'last_name.required' => __('The last name field is required.'),
            'company_name.required' => __('The company name field is required.'),
            'username.required' => __('The username field is required.'),
            'password.required' => __('The password field is required.'),
            'email.required' => __('The email field is required.'),
            'email.email' => __('Your Email is not Verified!'),
            'phone.required' => __('The phone field is required.'),
            'country.required' => __('The country name field is required.'),
            'city.required' => __('The city name field is required.'),
            'zip_code.required' => __('The zip code name field is required.'),
            'price.required' => __('The price field is required.'),
            'price.required' => __('The price field is required.'),
            'payment_method.required' => __('The payment method field is required.'),
            'receipt.required' => __('The receipt field image is required when instruction required receipt image')
        ];
    }
}
