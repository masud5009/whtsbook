<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Models\User\UserTicket;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User\UserConversation;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
  public function index(Request $request)
  {
    $search = $request->search;
    $admin = Admin::first();
    $data['tickets'] = UserTicket::when($search, function ($query, $search) {
      return $query->where('ticket_number', $search);
    })
      ->when($search, function ($query, $search) {
        return $query->orwhere('subject', 'like', '%' . $search . '%');
      })
      ->where('user_id', Auth::guard('web')->user()->id)
      ->where('admin_id', $admin->id)
      ->orderby('last_message', 'DESC')->paginate(10);
    return view('user.tickets.index', $data);
  }

  public function ticketstore(Request $request)
  {
    $admin = Admin::first();
    $rules = [
      'subject' => 'required',
      'message' => 'required',
      'zip_file' => 'nullable|mimes:zip|max:5000',
    ];

    $messages = [
      'zip_file.mimes' => __('Only zip file supported'),
      'zip_file.max' => __('zip file may not be greater than 5 MB'),
    ];

    $request->validate($rules, $messages);
    $input = $request->all();
    if ($request->hasFile('zip_file')) {
      $file = $request->file('zip_file');
      $filename = uniqid() . '.' . $file->getClientOriginalExtension();
      $file->move(public_path('assets/front/user-suppor-file/'), $filename);
      $input['zip_file'] = $filename;
    }


    $message = str_replace(url('/') . public_path('/assets/front/img/'), "{base_url}/" . public_path("assets/front/img/"), $request->message);
    $input['message'] = Purifier::clean($message, 'youtube');
    $input['user_id'] = Auth::guard('web')->user()->id;
    $input['admin_id'] = $admin->id;
    $input['ticket_number'] = rand(1000000, 9999999);
    $input['last_message'] = Carbon::now();

    $data = new UserTicket;
    $data->create($input);
    $files = glob(public_path('assets/front/temp/*'));
    foreach ($files as $file) {
      @unlink($file);
    }

    Session::flash('success', __('Created Successfully'));

    if ($request->ajax()) {
      return response()->json('success');
    }

    return redirect(route('tenant.tickets'));
  }

  public function messages($id)
  {
    $data['ticket'] = UserTicket::where('ticket_number', $id)->firstorFail();
    return view('user.tickets.messages', $data);
  }

  public function zip_upload(Request $request)
  {
    $file = $request->file('file');
    $allowedExts = array('zip');
    $rules = [
      'file' => [
        function ($attribute, $value, $fail) use ($file, $allowedExts) {
          $ext = $file->getClientOriginalExtension();
          if (!in_array($ext, $allowedExts)) {
            return $fail(__("Only zip file supported"));
          }
        },
        'max:5000'
      ],
    ];
    $messages = [
      'file.max' => __('zip file may not be greater than 5 MB'),
    ];
    $validator = Validator::make($request->all(), $rules, $messages);
    if ($validator->fails()) {
      return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
    }

    if ($request->hasFile('file')) {
      $file = $request->file('file');
      $filename = uniqid() . '.' . $file->getClientOriginalExtension();
      $file->move(public_path('assets/front/temp/'), $filename);
      $input['file'] = $filename;
    }

    return response()->json(['data' => 1]);
  }

  public function ticketreply(Request $request, $id)
  {
    $file = $request->file('file');
    $allowedExts = array('zip');
    $rules = [
      'reply' => 'required',
      'file' => [
        function ($attribute, $value, $fail) use ($file, $allowedExts) {
          $ext = $file->getClientOriginalExtension();
          if (!in_array($ext, $allowedExts)) {
            return $fail( __("Only zip file supported"));
          }
        },
        'max:5000'
      ],
    ];

    $messages = [
      'file.max' => __('zip file may not be greater than 5 MB'),
    ];

    $request->validate($rules, $messages);
    $input = $request->all();
    $reply = str_replace(url('/') . public_path('/assets/front/img/'), "{base_url}/" . public_path("assets/front/img/"), $request->reply);
    $input['reply'] = Purifier::clean($reply, 'youtube');
    $input['user_id'] = Auth::guard('web')->user()->id;
    $input['ticket_id'] = $id;
    if ($request->hasFile('file')) {
      $file = $request->file('file');
      $filename = uniqid() . '.' . $file->getClientOriginalExtension();
      $file->move(public_path('assets/front/user-suppor-file/'), $filename);
      $input['file'] = $filename;
    }

    $data = new UserConversation;
    $data->create($input);
    $files = glob(public_path('assets/front/temp/*'));
    foreach ($files as $file) {
      unlink($file);
    }
    UserTicket::where('id', $id)->update([
      'last_message' => Carbon::now(),
    ]);
    Session::flash('success', __('Message Sent Successfully'));
    return back();
  }
}
