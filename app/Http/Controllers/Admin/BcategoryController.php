<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\Bcategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BcategoryController extends Controller
{
    public function index(Request $request)
    {
        $data['langs'] = Language::query()->get();
        $lang = Language::where('code', $request->language)->firstOrFail();
        $lang_id = $lang->id;
        $data['bcategorys'] = Bcategory::where('language_id', $lang_id)->orderBy('id', 'DESC')->get();
        $data['lang_id'] = $lang_id;

        return view('admin.blog.bcategory.index', $data);
    }

    public function edit($id)
    {

        $data['bcategory'] = Bcategory::findOrFail($id);
        $data['languages'] = Language::query()->get();
        return view('admin.blog.bcategory.edit', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'status' => 'required',
            'serial_number' => 'required|integer',
        ];

        $messages = [];
        $languages = Language::query()->get();
        foreach ($languages as $lang) {
            $slug = slug_create($request[$lang->code . '_name']);
            $rules[$lang->code . '_name'] =
                [
                    'required',
                    'max:255',
                    function ($attribute, $value, $fail) use ($slug, $lang) {
                        $bis = Bcategory::where('language_id', $lang->id)
                            ->get();
                        foreach ($bis as $key => $bi) {
                            if (strtolower($slug) == strtolower($bi->slug)) {
                                $fail('The name field must be unique for ' . $lang->name . ' language.');
                            }
                        }
                    }
                ];
            $messages[$lang->code . '_name.required'] = __('The category name field is required for') . ' ' . $lang->name . ' ' . __('language');
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
          return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $index_id = uniqid();
        foreach ($languages as $language) {
            $bcategory = new Bcategory();
            $bcategory->indx = $index_id;
            $bcategory->language_id = $language->id;
            $bcategory->name = $request[$language->code . '_name'];
            $bcategory->slug = slug_create($request[$language->code . '_name']);
            $bcategory->status = $request->status;
            $bcategory->serial_number = $request->serial_number;
            $bcategory->save();
        }
        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $rules = [
            'status' => 'required',
            'serial_number' => 'required|integer',
        ];
        $languages = Language::query()->get();
        foreach ($languages as $language) {
            $slug = slug_create($request[$language->code . '_name']);
            $indx = $request->category_indx;
            $rules[$language->code . '_name'] = [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($slug, $indx, $language) {
                    $bis = Bcategory::where('indx', '<>', $indx)
                        ->where('language_id', $language->id)
                        ->get();
                    foreach ($bis as $key => $bi) {
                        if (strtolower($slug) == strtolower($bi->slug)) {
                            $fail(__('The title field must be unique for') . ' ' . $language->name . ' ' . __('language.'));
                        }
                    }
                }
            ];

            $messages[$language->code . '_name.required'] = __('The name field is required for') . ' ' . $language->name . ' ' . __('language');
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
         return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }


        $bcategory = Bcategory::findOrFail($request->category_id);
        $indx = is_null($bcategory->indx) ? uniqid() : $bcategory->indx;

        foreach ($languages as $language) {
            $bcategory = Bcategory::where('id', $request[$language->code . '_id'])->first();
            if (empty($bcategory)) {
                $bcategory = new Bcategory();
            }
            $bcategory->indx = $indx;
            $bcategory->language_id = $language->id;
            $bcategory->name = $request[$language->code . '_name'];
            $bcategory->slug = slug_create($request[$language->code . '_name']);
            $bcategory->status = $request->status;
            $bcategory->serial_number = $request->serial_number;
            $bcategory->save();
        }


        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $bcategory = Bcategory::findOrFail($request->bcategory_id);
        if ($bcategory->blogs()->count() > 0) {
            Session::flash('warning', __('First, delete all the blogs under this category!'));
            return back();
        }
        $bcategory->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $bcategory = Bcategory::findOrFail($id);
            if ($bcategory->blogs()->count() > 0) {
                Session::flash('warning', __('First, delete all the blogs under the selected categories!'));
                return "success";
            }
        }
        foreach ($ids as $id) {
            $bcategory = Bcategory::findOrFail($id);
            $bcategory->delete();
        }

        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
