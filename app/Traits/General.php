<?php

namespace App\Traits;

trait General
{
  public static function generateValidationRules($request, $languages, $fields, $nullableFields = [])
  {
    $rules = [];

    foreach ($languages as $language) {
      $prefix = $language->code . '_';

      if ($language->is_default == 1) {
        // Default language: make some fields nullable and others required
        foreach ($fields as $field) {
          if (in_array($field, $nullableFields)) {
            // Fields that can be nullable in the default language
            $rules[$prefix . $field] = 'nullable|max:255';
          } else {
            // Other fields remain required
            $rules[$prefix . $field] = 'required|max:255';
          }
        }
      } else {
        // For non-default languages, check if any field has input (excluding meta_keywords and meta_description)
        $hasInput = false;

        foreach ($fields as $field) {
          if ($request->input($prefix . $field)) {
            $hasInput = true;
            break;
          }
        }

        foreach ($fields as $field) {
          // For meta_keywords and meta_description, they are nullable in non-default languages
          if (in_array($field, $nullableFields)) {
            $rules[$prefix . $field] = 'nullable|max:255';
          } else {
            // If any field has input, make all fields required (except meta_keywords and meta_description)
            if ($hasInput) {
              $rules[$prefix . $field] = 'required|max:255';
            } else {
              // Otherwise, make fields nullable
              $rules[$prefix . $field] = 'nullable|max:255';
            }
          }
        }
      }
    }

    return $rules;
  }



  public static  function generateValidationMessages($languages, $fields)
  {
    $messages = [];

    foreach ($languages as $language) {
      $prefix = $language->code . '_';

      foreach ($fields as $field) {
        $messages[$prefix . $field . '.required'] = __('The ' . str_replace('_', ' ', $field) . ' field is required for') . ' ' . $language->name . ' ' . __('language');
        $messages[$prefix . $field . '.max'] = __('The ' . str_replace('_', ' ', $field) . ' field cannot contain more than :max characters for') . ' ' . $language->name . ' ' . __('language');
      }
    }

    return $messages;
  }
}
