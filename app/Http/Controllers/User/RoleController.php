<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $data['roles'] = Role::query()
            ->where('user_id', Auth::guard('web')->id())
            ->withCount('staffs')
            ->latest()
            ->get();

        return view('user.staff.roles.index', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user_roles', 'name')->where(function ($query) {
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

        Role::create([
            'user_id' => Auth::guard('web')->id(),
            'name' => $request->name,
            'permissions' => ['Dashboard'],
        ]);

        Session::flash('success', __('Created Successfully'));

        return 'success';
    }

    public function update(Request $request)
    {
        $role = $this->getRole($request->role_id);

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user_roles', 'name')
                    ->ignore($role->id)
                    ->where(function ($query) {
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

        $role->update([
            'name' => $request->name,
        ]);

        Session::flash('success', __('Updated Successfully'));

        return 'success';
    }

    public function delete(Request $request)
    {
        $role = $this->getRole($request->role_id);

        if ($role->staffs()->count() > 0) {
            Session::flash('warning', __('Please delete the staffs under this role first.'));

            return back();
        }

        $role->delete();

        Session::flash('success', __('Deleted Successfully'));

        return back();
    }

    public function managePermissions($id)
    {
        $data['role'] = $this->getRole($id);
        $data['permissionGroups'] = Role::permissionGroups();

        return view('user.staff.roles.permission.manage', $data);
    }

    public function updatePermissions(Request $request)
    {
        $role = $this->getRole($request->role_id);
        $dependencyMap = Role::dependentPermissionParents();

        $permissions = collect($request->input('permissions', []))
            ->filter()
            ->intersect(Role::permissionList())
            ->values();

        foreach ($permissions->all() as $permission) {
            if (isset($dependencyMap[$permission])) {
                $permissions->push($dependencyMap[$permission]);
            }
        }

        $permissions = $permissions
            ->prepend('Dashboard')
            ->unique()
            ->values()
            ->all();

        $role->update([
            'permissions' => $permissions,
        ]);

        Session::flash('success', __('Updated Successfully'));

        return back();
    }

    private function getRole($id): Role
    {
        return Role::query()
            ->where('user_id', Auth::guard('web')->id())
            ->findOrFail($id);
    }
}
