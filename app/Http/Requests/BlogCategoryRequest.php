<?php

namespace App\Http\Requests;

use App\Models\User\Journal\BlogCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BlogCategoryRequest extends FormRequest
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
    $bc = BlogCategory::where('id', (int)$this->id)->select('language_id')->first();
    return [
      'name' => [
        'required',
        request()->isMethod('POST') ? Rule::unique('user_blog_categories')->where(function ($query) {
          return $query->where('user_id', Auth::guard('web')->user()->id)->where('language_id', $this->user_language_id);
        }) : Rule::unique('user_blog_categories')->ignore($this->id)->where(function ($query) use ($bc) {
          return $query->where('user_id', Auth::guard('web')->user()->id)->where('language_id', $bc->language_id);
        })
      ],
      'status' => 'required|numeric',
      'serial_number' => 'required|numeric'
    ];
  }

  public function messages(): array
  {
    return [
      'user_language_id.required' => 'The language field is required.'
    ];
  }
}
