<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
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
        return [
            'name' => 'required',
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'start_date' => 'required | date',
            'end_date' => 'required |date',
            'serial_number' => 'required',
        ];
    }
}
