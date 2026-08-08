<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        $demoMode = env('DEMO_MODE', 'active');
        $data['username'] = $demoMode === 'active' ? 'admin' : '';
        $data['password'] = $demoMode === 'active' ? 'admin' : '';

        return view('admin.login',$data);
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'username'   => 'required',
            'password' => 'required'
        ]);
        if (Auth::guard('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->back()->with('alert', __('Username and Password Not Matched'));
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
