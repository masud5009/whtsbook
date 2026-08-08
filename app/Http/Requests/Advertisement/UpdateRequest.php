<?php

namespace App\Http\Requests\Advertisement;


use App\Models\User\Advertisement;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
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
    $ad = Advertisement::query()->where('user_id', Auth::guard('web')->user()->id)->find($this->id);

    $array = [
      'ad_type' => 'required',
      'resolution_type' => 'required|numeric'
    ];

    if (($this->ad_type == 'banner') && is_null($ad->image) && !$this->hasFile('image')) {
      $array['image'] = 'required';
    }
    if ($this->hasFile('image')) {
      $array['image'] = new ImageMimeTypeRule();
    }

    $array['url'] = [
      'required_if:ad_type,banner',
      $this->filled('url') ? 'url' : ''
    ];

    $array['slot'] = 'required_if:ad_type,adsense';

    return $array;
  }

  public function messages()
  {
    return [
      'image.required' => 'The image field is required when ad type is banner.'
    ];
  }
}
