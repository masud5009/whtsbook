<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PageHeadingRequest extends FormRequest
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
    $user = Auth::guard('web')->user();
    $permissions = \App\Http\Helpers\UserPermissionHelper::packagePermission($user->id);
    $permissions = json_decode($permissions, true);
    
    $ruleArray =  [
      'contact_page_title' => 'required |  max:255',
      'forget_password_page_title' => 'required | max:255',
      'login_page_title' => 'required | max:255',
      'faq_page_title' => 'required | max:255',
      'error_page_title' => 'required | max:255',
      'signup_page_title' => 'required | max:255',
      'rooms_page_title' => 'required | max:255',
      'room_bookings_page_title' => 'required | max:255',
      'gallery_page_title' => 'required | max:255',
      'reset_password_page_title' => 'required | max:255',
      'services_page_title' => 'required | max:255',
      'change_password_page_title' => 'required | max:255',
      'edit_profile_page_title' => 'required | max:255',
      'dashboard_page_title' => 'required | max:255',
    ];
    if(!empty($permissions) && in_array('Tour Package', $permissions)){
      $ruleArray['packages_page_title'] = 'required | max:255';
      $ruleArray['package_bookings_page_title'] = 'required | max:255';
    }
    if (!empty($permissions) && in_array('Tour Package', $permissions)) {
    $ruleArray['blog_page_title'] = 'required | max:255';
    }

    return $ruleArray;
  }
}
