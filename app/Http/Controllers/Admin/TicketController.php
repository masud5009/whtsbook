<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Models\User\UserTicket;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User\UserConversation;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class TicketController extends Controller
{
  public function all(Request $request)
  {
    $search = $request->search;
    $tickets = UserTicket::when($search, function ($query, $search) {
      return $query->where('ticket_number', $search);
    })
      ->when($search, function ($query, $search) {
        return $query->orwhere('subject', 'like', '%' . $search . '%');
      })
      ->orderby('last_message', 'DESC')->paginate(10);

    return view('admin.tickets.index', compact('tickets'));
  }

  public function create()
  {
    $userTickests = User::all();
    return view('admin.tickets.create', compact('userTickests'));
  }

  public function ticketstore(Request $request)
  {

    $file = $request->file('zip_file');
    $allowedExts = array('zip');
    $rules = [
      'subject' => 'required',
      'message' => 'required',
      'zip_file' => [
        function ($attribute, $value, $fail) use ($file, $allowedExts) {

          $ext = $file->getClientOriginalExtension();
          if (!in_array($ext, $allowedExts)) {
            return $fail(__("Only zip file supported"));
          }
        },
        'max:5000'
      ],
    ];


    $request->validate($rules);
    $input = $request->all();

    if ($request->hasFile('zip_file')) {
      $file = $request->file('zip_file');
      $filename = uniqid() . '.' . $file->getClientOriginalExtension();
      $file->move(public_path('assets/front/user-suppor-file/'), $filename);
      $input['zip_file'] = $filename;
    }

    $message = str_replace(url('/') . public_path('/assets/front/img/'), "{base_url}/" . public_path("assets/front/img/"), $request->message);
    $input['message'] = Purifier::clean($message, 'youtube');
    $input['subject'] = $request->subject;
    $input['user_id'] = $request->user_id;
    $input['admin_id'] = Auth::guard('admin')->user()->id;
    $input['ticket_number'] = rand(1000000, 9999999);
    $input['last_message'] = Carbon::now();

    $data = new UserTicket;
    $data->create($input);

    $files = glob(public_path('assets/front/temp/*'));
    foreach ($files as $file) {
      unlink($file);
    }

    Session::flash('success', __('Created Successfully'));
    return redirect(route('admin.tickets.all'));
  }

  public function pending(Request $request)
  {
    $search = $request->search;
    $tickets = UserTicket::where('status', 'pending')
      ->when($search, function ($query, $search) {
        return $query->where('ticket_number', $search);
      })
      ->when($search, function ($query, $search) {
        return $query->orwhere('subject', 'like', '%' . $search . '%');
      })
      ->orderby('id', 'desc')->paginate(10);

    return view('admin.tickets.index', compact('tickets'));
  }

  public function open(Request $request)
  {
    $search = $request->search;
    $tickets = UserTicket::when($search, function ($query, $search) {
      return $query->where('ticket_number', $search);
    })
      ->when($search, function ($query, $search) {
        return $query->orwhere('subject', 'like', '%' . $search . '%');
      })
      ->where('status', 'open')->orderby('last_message', 'DESC')
      ->paginate(10);

    return view('admin.tickets.index', compact('tickets'));
  }

  public function closed(Request $request)
  {
    $search = $request->search;
    $tickets = UserTicket::where('status', 'close')
      ->when($search, function ($query, $search) {
        return $query->where('ticket_number', $search);
      })
      ->when($search, function ($query, $search) {
        return $query->orwhere('subject', 'like', '%' . $search . '%');
      })
      ->orderby('last_message', 'desc')->paginate(10);
    return view('admin.tickets.index', compact('tickets'));
  }

  public function messages($id)
  {
    $ticket = UserTicket::findOrFail($id);
    return view('admin.tickets.messages', compact('ticket'));
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

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
     return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
    }

    if ($request->hasFile('file')) {
      $file = $request->file('file');
      $filename = uniqid() . '.' . $file->getClientOriginalExtension();
      $file->move(public_path('assets/front/temp/'), $filename);
      $input['file'] = $filename;
    }
    return response()->json(['data' => 1]);
  }


  public function ticketReply(Request $request, $id)
  {
    $file = $request->file('file');
    $rules = [
      'reply' => 'required',
    ];

    $ticket = UserTicket::findOrFail($id);
    $request->validate($rules);
    $input = $request->all();

    $reply = str_replace(url('/') . public_path('/assets/front/img/'), "{base_url}/" . public_path("assets/front/img/"), $request->reply);
    $input['reply'] = $reply;
    $input['user_id'] = null;
    $input['message'] = Purifier::clean($request->message, 'youtube');
    $input['ticket_id'] = $ticket->id;
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
      @unlink($file);
    }
    UserTicket::where('id', $id)->update([
      'status' => 'open',
    ]);
    Session::flash('success', __('Message Send Successfully'));
    return back();
  }

  public function ticketclose($id)
  {
    UserTicket::where('id', $id)->update([
      'status' => 'close',
    ]);
    Session::flash('success', __('Updated Successfully'));
    return 'success';
  }

  public function settings()
  {
    $data['abex'] = BasicExtended::first();
    return view('admin.tickets.settings', $data);
  }

  public function updateSettings(Request $request)
  {
    $bexs = BasicExtended::all();
    foreach ($bexs as $bex) {
      $bex->is_ticket = $request->is_ticket;
      $bex->save();
    }

    Session::flash('success', __('Updated Successfully'));
    return back();
  }
}
