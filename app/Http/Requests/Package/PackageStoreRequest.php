<?php

namespace App\Http\Requests\Package;

use App\Traits\AiCredit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Http\FormRequest;

class PackageStoreRequest extends FormRequest
{
    use AiCredit;
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
        $admin_total_ai_token = $this->admin_remaining_credit();
        return [

            'title' => 'required|max:255',
            'term' => 'required',
            'price' => 'required',
            'status' => 'required',
            'room_categories_limit' => 'required',
            'room_booking_limit' => 'required',
            'room_limit' => 'required',
            'trial_days' => 'required_if:is_trial,1',
            'language_limit' => 'required',
            'total_ai_token' => ['required', 'integer', 'min:100000', 'max:' . $admin_total_ai_token],
            'whatsapp_limit' => 'required|min:1'
        ];
    }
    public function messages(): array
    {
        return [
            'trial_days.required_if' => 'Trial days is required',
        ];
    }
}
