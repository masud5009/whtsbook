<?php

namespace App\Http\Controllers\User;

use App\Models\WhatsAppChat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BotChatController extends Controller
{
    public function index()
    {
        $userId = Auth::guard('web')->user()->id;
        $data['messages'] = WhatsAppChat::select('customer_phone')
            ->where('user_id', $userId)
            ->groupBy('customer_phone')
            ->get()
            ->map(function ($item) use ($userId) {
                $lastMessage = WhatsAppChat::where('user_id', $userId)
                    ->where('customer_phone', $item->customer_phone)
                    ->latest('received_at')
                    ->first();

                return $lastMessage;
            });
        return view('user.bot_chat.index', $data);
    }

    public function details($id)
    {
        $message = WhatsAppChat::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

        // oi customer_phone er sob failed messages with pagination
        $data['allFailedMessages'] = WhatsAppChat::where('user_id', Auth::id())
            ->where('customer_phone', $message->customer_phone)
            ->orderBy('received_at', 'desc')->get();

        $data['message'] = $message;

        return view('user.bot_chat.details', $data);
    }


    public function updateStatus(Request $request, $id)
    {
        $message = WhatsAppChat::findOrFail($id);

        $newStatus = $request->status;

        // Update the status
        $message->status = $newStatus;
        $message->save();

        // Redirect back with a success message
        return back()->with('success', __('Updated Successfully'));
    }

    public function delete(Request $request)
    {
        WhatsAppChat::where('customer_phone', $request->customer_phone)->delete();
        Session::flash('success', __('Deleted Successfully'));
        return redirect()->back();
    }

    public function bulk_delete(Request $request)
    {
        $phone_numbers = $request->ids;
        foreach ($phone_numbers as $phone_number) {
            WhatsAppChat::where('customer_phone', $phone_number)->delete();
        }
        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
