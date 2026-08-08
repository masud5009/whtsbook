<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $data['users'] = Admin::all();
        $data['roles'] = Role::all();
        return view('admin.user.index', $data);
    }

    public function edit($id)
    {
        $data['user'] = Admin::findOrFail($id);
        $data['roles'] = Role::all();
        return view('admin.user.edit', $data);
    }


    public function store(Request $request)
    {
        $rules = [
            'username' => 'required|max:255|unique:admins',
            'email' => 'required|email|max:255|unique:admins',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'password' => 'required|confirmed',
            'role_id' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
           return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $user = new Admin;
        $input = $request->all();
        if ($request->hasFile('image')) {
            $image = $request->image;
            $name =  uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/admin/img/propics/'), $name);
            $input['image'] = $name;
        }
        $input['password'] = bcrypt($request['password']);
        $user->create($input);

        Session::flash('success', __('Created Successfully'));
        return "success";
    }


    public function update(Request $request)
    {
        $user = Admin::findOrFail($request->user_id);
        $rules = [
            'username' => [
                'required',
                'max:255',
                Rule::unique('admins')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($user->id),
            ],
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'role_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $input = $request->all();
        if ($request->hasFile('image')) {
            @unlink(public_path('assets/admin/img/propics/' . $user->image));
            $image = $request->image;
            $name =  uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/admin/img/propics/'), $name);
            $input['image'] = $name;
        }
        $user->update($input);
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        if ($request->user_id == 1) {
            Session::flash('warning', 'You cannot delete the owner!');
            return back();
        }
        $user = Admin::findOrFail($request->user_id);
        @unlink(public_path('assets/admin/img/propics/' . $user->image));
        $user->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function managePermissions($id)
    {
        $data['user'] = Admin::find($id);
        return view('admin.user.permission.manage', $data);
    }

    public function updatePermissions(Request $request)
    {
        $permissions = json_encode($request->permissions);
        $user = Admin::find($request->user_id);
        $user->permissions = $permissions;
        $user->save();

        Session::flash('success', __('Updated Successfully'));
        return back();
    }
}
