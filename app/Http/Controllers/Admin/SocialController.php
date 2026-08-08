<?php

namespace App\Http\Controllers\Admin;

use App\Models\Social;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
  public function index()
  {
    $data['socials'] = Social::orderBy('id', 'DESC')->get();
    return view('admin.basic.social.index', $data);
  }

  public function store(Request $request)
  {
    $rules = [
      'icon' => 'required',
      'url' => 'required',
      'serial_number' => 'required',
      'serial_number' => 'required|integer',
    ];

     $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

    $social = new Social;
    $social->icon = $request->icon;
    $social->url = $request->url;
    $social->serial_number = $request->serial_number;
    $social->save();

    Session::flash('success', __('Created Successfully'));
    return "success";
  }

  public function edit($id)
  {
    $data['social'] = Social::findOrFail($id);
    return view('admin.basic.social.edit', $data);
  }

  public function update(Request $request)
  {
    $request->validate([
      'icon' => 'required',
      'url' => 'required',
      'serial_number' => 'required|integer',
    ]);

    $social = Social::findOrFail($request->socialid);
    $social->icon = $request->icon;
    $social->url = $request->url;
    $social->serial_number = $request->serial_number;
    $social->save();

    Session::flash('success', __('Updated Successfully'));
    return "success";
  }

  public function delete(Request $request)
  {
    $social = Social::findOrFail($request->socialid);
    $social->delete();

    Session::flash('success', __('Deleted Successfully'));
    return back();
  }
}
