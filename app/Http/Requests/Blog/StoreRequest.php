<?php

namespace App\Http\Requests\Blog;

use App\Models\User\CustomPage\PageContent;
use App\Models\User\Journal\BlogInformation;
use App\Models\User\Language;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
    $ruleArray = [
      'image' => [
        'required',
        new ImageMimeTypeRule()
      ],
      'serial_number' => 'required|numeric',
      'category_id' => 'required',
    ];

    $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
    $defaulLang = Language::where([['user_id', Auth::guard('web')->user()->id], ['is_default', 1]])->first();

    $ruleArray[$defaulLang->code . '_title'] = 'required|max:255';
    $ruleArray[$defaulLang->code . '_author'] = 'required';
    $ruleArray[$defaulLang->code . '_content'] = 'required|min:15';

    foreach ($languages as $language) {
      $request = $this->request->all();
      if ($request[$language->code . '_title'] || $request[$language->code . '_author'] || $request[$language->code . '_content'] || $request[$language->code . '_meta_keywords'] || $request[$language->code . '_meta_description']) {
        $slug = slug_create($request[$language->code . '_title']);

        $ruleArray[$language->code . '_title'] = [
          'required',
          'max:255',
          function ($attribute, $value, $fail) use ($slug, $language) {
            $bis = BlogInformation::where('language_id', $language->id)->where('user_id', Auth::guard('web')->user()->id)->get();
            foreach ($bis as $key => $bi) {
              if (strtolower($slug) == strtolower($bi->slug)) {
                $fail('The title field must be unique for ' . $language->name . ' language.');
              }
            }
          }
        ];
        $ruleArray[$language->code . '_author'] = 'required';
        $ruleArray[$language->code . '_content'] = 'required';
      }
    }
    return $ruleArray;
  }

  public function messages(): array
  {
    $messageArray = [
      'category_id.required' => __('The category field is required.')
    ];

    $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
    foreach ($languages as $language) {
      $messageArray[$language->code . '_title.required'] = __('The title field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
      $messageArray[$language->code . '_title.max'] = __('The title field cannot contain more than 255 characters for') . ' ' . $language->name . ' ' . ('language') . '.';
      $messageArray[$language->code . '_title.unique'] = __('The title field must be unique for') . ' ' . $language->name . ' ' .  __('language') . '.';
      $messageArray[$language->code . '_author.required'] = __('The author field is required for') . ' ' . $language->name . ' ' .  __('language') . '.';
      $messageArray[$language->code . '_content.required'] = __('The content field is required for') . ' ' . $language->name . ' ' .  __('language') . '.';
      $messageArray[$language->code . '_content.min'] = __('The content must be at least 30 characters for') . ' ' . $language->name . ' ' . __('language') . '.';
    }

    return $messageArray;
  }
}
