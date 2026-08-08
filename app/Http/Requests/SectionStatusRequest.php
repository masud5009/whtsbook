<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionStatusRequest extends FormRequest
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
      'course_categories_section_status' => 'numeric',
      'call_to_action_section_status' => 'numeric',
      'featured_courses_section_status' => 'numeric',
      'features_section_status' => 'numeric',
      'video_section_status' => 'numeric',
      'fun_facts_section_status' => 'numeric',
      'testimonials_section_status' => 'numeric',
      'newsletter_section_status' => 'numeric',
      'featured_instructors_section_status' => 'numeric',
      'about_us_section_status' => 'numeric',
      'latest_blog_section_status' => 'numeric',
      'footer_section_status' => 'numeric'
    ];
  }
}
