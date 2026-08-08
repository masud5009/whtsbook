<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User\MailTemplate;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MailTemplateController extends Controller
{
    public function index()
    {
        $user_id = Auth::guard('web')->user()->id;

        $totalTemplates = MailTemplate::where('user_id', $user_id)->count();
        if ($totalTemplates == 0) {
            $this->storeTemplate();
        }

        $templates = MailTemplate::where('user_id', $user_id)->get();
        return view('user.settings.email.templates', compact('templates'));
    }

    public function edit($id)
    {
        $templateInfo = MailTemplate::where('user_id', Auth::guard('web')->user()->id)->findOrFail($id);
        return view('user.settings.email.edit-template', compact('templateInfo'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'mail_subject' => 'required',
            'mail_body' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        MailTemplate::where('user_id', Auth::guard('web')->user()->id)->findOrFail($id)->update($request->except('mail_type', 'mail_body') + [
            'mail_body' => Purifier::clean($request->mail_body, 'youtube')
        ]);

        Session::flash('success', __('Updated Successfully'));
        return redirect()->back();
    }


    private function storeTemplate()
    {
        $defaultTemplates = MailTemplate::where('user_id', 0)->get();
        foreach ($defaultTemplates as $template) {
            MailTemplate::create([
                'user_id' => Auth::guard('web')->user()->id,
                'mail_type' => $template->mail_type,
                'mail_subject' => $template->mail_subject,
                'mail_body' => Purifier::clean($template->mail_body, 'youtube')
            ]);
        }
        return true;
    }
}
