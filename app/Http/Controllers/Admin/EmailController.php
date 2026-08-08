<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    public function mailFromAdmin()
    {
        $abe = BasicExtended::select(
            'is_smtp',
            'smtp_host',
            'smtp_port',
            'encryption',
            'smtp_username',
            'smtp_password',
            'from_mail',
            'from_name'
        )->first();

        $demoMode = env('DEMO_MODE', 'active');

        if ($demoMode === 'active') {
            $abe->smtp_host = 'your_smtp_host';
            $abe->smtp_port = 'your_smtp_port';
            $abe->encryption = 'your_encryption';
            $abe->smtp_username = 'your_smtp_username';
            $abe->smtp_password = 'your_smtp_password';
            $abe->from_mail = 'your_email@example.com';
            $abe->from_name = 'your_name';
        }

        return view('admin.basic.email.mail_from_admin', compact('abe'));
    }

    public function updateMailFromAdmin(Request $request)
    {
        $rules = [
            'from_mail' => 'required_if:is_smtp,1',
            'from_name' => 'required_if:is_smtp,1',
            'is_smtp' => 'required',
            'smtp_host' => 'required_if:is_smtp,1',
            'smtp_port' => 'required_if:is_smtp,1',
            'encryption' => 'required_if:is_smtp,1',
            'smtp_username' => 'required_if:is_smtp,1',
            'smtp_password' => 'required_if:is_smtp,1',
        ];

        $messages = [
            'from_mail.required_if' => __('The smtp host field is required when smtp status is active.'),
            'from_name.required_if' => __('The from name field is required when smtp status is active.'),
            'smtp_host.required_if' => __('The smtp host field is required when smtp status is active.'),
            'smtp_port.required_if' => __('The smtp port field is required when smtp status is active.'),
            'encryption.required_if' => __('The encryption field is required when smtp status is active.'),
            'smtp_username.required_if' => __('The smtp username field is required when smtp status is active.'),
            'smtp_password.required_if' => __('The smtp password field is required when smtp status is active.')
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }



        $bes = BasicExtended::all();
        foreach ($bes as $key => $be) {
            $be->from_mail = $request->from_mail;
            $be->from_name = $request->from_name;
            $be->is_smtp = $request->is_smtp;
            $be->smtp_host = $request->smtp_host;
            $be->smtp_port = $request->smtp_port;
            $be->encryption = $request->encryption;
            $be->smtp_username = $request->smtp_username;
            $be->smtp_password = $request->smtp_password;
            $be->save();
        }

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function mailToAdmin()
    {
        $data['abe'] = BasicExtended::first();
        return view('admin.basic.email.mail_to_admin', $data);
    }

    public function updateMailToAdmin(Request $request)
    {
        $messages = [
            'to_mail.required' => __('Mail Address is required.')
        ];

        $request->validate([
            'to_mail' => 'required',
        ], $messages);

        $bes = BasicExtended::all();
        foreach ($bes as $key => $be) {
            $be->to_mail = $request->to_mail;
            $be->save();
        }

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }
}
