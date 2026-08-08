<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MailTemplateController extends Controller
{
    public function mailTemplates()
    {
        $templates = EmailTemplate::paginate(10);
        return view('admin.basic.email.mail_templates', compact('templates'));
    }

    public function editMailTemplate($id)
    {
        $templateInfo = EmailTemplate::findOrFail($id);
        return view('admin.basic.email.edit_mail_template', compact('templateInfo'));
    }

    public function updateMailTemplate(Request $request, $id)
    {
        $rules = [
            'email_subject' => 'required',
            'email_body' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
         return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        EmailTemplate::findOrFail($id)->update($request->except('email_type', 'email_body') + [
            'email_body' => Purifier::clean($request->email_body, 'youtube')
        ]);
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }
}
