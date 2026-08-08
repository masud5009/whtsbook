<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->term;
        $data['subscs'] = Subscriber::when($term, function ($query, $term) {
            return $query->where('email', 'LIKE', '%' . $term . '%');
        })->orderBy('id', 'DESC')->paginate(10);
        return view('admin.subscribers.index', $data);
    }

    public function mailsubscriber()
    {
        return view('admin.subscribers.mail');
    }

    public function subscsendmail(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'message' => 'required'
        ]);
        $sub = $request->subject;
        $msg = Purifier::clean($request->message, 'youtube');
        $subscs = Subscriber::all();
        $be = BasicExtended::first();

        $smtp = [
            'transport' => 'smtp',
            'host' => $be->smtp_host,
            'port' => $be->smtp_port,
            'encryption' => $be->encryption,
            'username' => $be->smtp_username,
            'password' => $be->smtp_password,
            'timeout' => null,
            'auth_mode' => null,
        ];
        Config::set('mail.mailers.smtp', $smtp);
        if ($be->is_smtp == 1) {
            try {
                Mail::send([], [], function ($message) use ($be, $msg, $sub, $subscs) {
                    $fromMail = $be->from_mail;
                    $fromName = $be->from_name;
                    // Add BCC recipients
                    foreach ($subscs as $recipient) {
                        $message->subject($sub);
                        $message->from($fromMail, $fromName);
                        $message->html($msg, 'text/html');
                        $message->bcc($recipient->email);
                    }
                });
            } catch (\Exception $e) {
            }
        } else {
            try {
                //Recipients
                Mail::send([], [], function ($message) use ($be, $msg, $sub, $subscs) {
                    $fromMail = $be->from_mail;
                    $fromName = $be->from_name;
                    // Add BCC recipients
                    foreach ($subscs as $recipient) {
                        $message->subject($sub);
                        $message->from($fromMail, $fromName);
                        $message->html($msg, 'text/html');
                        $message->bcc($recipient->email);
                    }
                });
            } catch (\Exception $e) {
            }
        }

        Session::flash('success', __('Mail sent successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        $subscriber = Subscriber::findOrFail($request->subscriber_id);
        $subscriber->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $subscriber = Subscriber::findOrFail($id);
            $subscriber->delete();
        }
        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
