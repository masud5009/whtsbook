<?php

namespace App\Http\Requests\Room;

use App\Models\User\Language;
use App\Models\User\RoomContent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $defaulLang = Language::where([['user_id', Auth::guard('web')->user()->id], ['is_default', 1]])->first();
        $rules = [
            'status' => 'required',
            'regular_price' => 'required|min:0',
            'weekend_price' => 'nullable|min:0',
            'seasonal_price' => 'nullable|min:0',
            'seasonal_weekend_price' => 'nullable|min:0',
            'bed' => 'required',
            'bath' => 'required',
            'amenities' => 'required',
            'adult' => 'nullable|numeric',
            'payment_system' => 'required',
            'advance_amount' => 'required_if:payment_system,advance',
            'room_details_page' => 'required',
        ];
        if ($this->input('weekend_price')) {
            $rules['selected_days'] = 'required';
        }
        if ($this->input('seasonal_price')) {
            $rules['seasonal_dates'] = 'required';
        }
        if ($this->input('seasonal_weekend_price')) {
            $rules['selected_seasonal_days'] = 'required';
        }
        if ($this->input('room_details_page')  == 0) {
            $rules['details_link'] = 'required';
        }
        $rules[$defaulLang->code . '_title'] = 'required|max:255';
        $rules[$defaulLang->code . '_summary'] = 'required';
        $rules[$defaulLang->code . '_description'] = 'required|min:15';

        return $rules;
    }

    public function messages()
    {
        $messages = [];
        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();

        $messages['selected_days.required'] = __('You must be set weekend days if you want to set weekend price.');
        $messages['seasonal_dates.required'] = __('You must be set seasonal dates if you want to set seasonal price.');
        $messages['selected_seasonal_days.required'] = __('You must be set seasonal weekend days if you want to set seasonal weekend price.');
        foreach ($languages as $language) {
            $code = $language->code;
            $langName = ' ' . $language->name . ' ' . __('language');
            $hasExistingContent = RoomContent::where('room_id', $this->id)
                ->where('language_id', $language->id)
                ->exists();

            if (
                $hasExistingContent ||
                $this->input($code . '_title') ||
                $this->input($code . '_summary') ||
                $this->input($code . '_description') ||
                $this->input($code . '_meta_keywords') ||
                $this->input($code . '_meta_description')
            ) {
                $rules[$code . '_title'] = 'required';
                $rules[$code . '_summary'] = 'required';
                $rules[$code . '_description'] = 'required';
            }

            $messages[$code . '_title.required'] = __('The title field is required for') . $langName;
            $messages[$code . '_title.max'] = __('The title field cannot contain more than 255 characters for') . $langName;
            $messages[$code . '_summary.required'] = __('The summary field is required for') . $langName;
            $messages[$code . '_description.required'] = __('The description field is required for') . $langName;
            $messages[$code . '_description.min'] = __('The description field atleast have 15 characters for') . $langName;
        }

        return $messages;
    }
}
