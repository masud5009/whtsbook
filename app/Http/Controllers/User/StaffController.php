<?php

namespace App\Http\Controllers\User;

use App\Models\User\Role;
use App\Models\User\Staff;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function index()
    {
        $data['roles'] = Role::query()
            ->where('user_id', Auth::guard('web')->id())
            ->orderBy('name')
            ->get();

        $data['staffs'] = Staff::query()
            ->where('user_id', Auth::guard('web')->id())
            ->with('roleInfo')
            ->latest()
            ->get();

        return view('user.staff.index', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:staff,username',
            'email' => 'required|email|max:255|unique:staff,email',
            'password' => 'required|confirmed|min:6',
            'role' => [
                'required',
                Rule::exists('user_roles', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::guard('web')->id());
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray(),
            ], 400);
        }

        Staff::create([
            'user_id' => Auth::guard('web')->id(),
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        Session::flash('success', __('Created Successfully'));

        return 'success';
    }

    public function update(Request $request)
    {
        $staff = $this->getStaff($request->staff_id);

        $rules = [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('staff', 'username')->ignore($staff->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('staff', 'email')->ignore($staff->id),
            ],
            'password' => 'nullable|confirmed|min:6',
            'role' => [
                'required',
                Rule::exists('user_roles', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::guard('web')->id());
                }),
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray(),
            ], 400);
        }

        $input = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if (!empty($request->password)) {
            $input['password'] = Hash::make($request->password);
        }

        $staff->update($input);

        Session::flash('success', __('Updated Successfully'));

        return 'success';
    }

    public function delete(Request $request)
    {
        $staff = $this->getStaff($request->staff_id);
        $staff->delete();

        Session::flash('success', __('Deleted Successfully'));

        return back();
    }

    private function getStaff($id): Staff
    {
        return Staff::query()
            ->where('user_id', Auth::guard('web')->id())
            ->findOrFail($id);
    }
}
