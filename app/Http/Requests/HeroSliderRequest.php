<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeroSliderRequest extends FormRequest
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
      'title' => 'required',
      'img' => 'required|image|mimes:jpeg,png,jpg|max:2048',
      'subtitle' => 'required',
      'description' => 'required',
      'btn_name' => 'nullable',
      'btn_url' => 'nullable',
      'serial_number' => 'required'
    ];
  }

  public function messages()
  {
    return [
      'btn_name.required' => __('The button name field is required.'),
      'btn_url.required' => __('The button url field is required.'),
      'img.required' => __('The image field is required.')
    ];
  }
}
