<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User\AutoResMessage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AutoResponseMessage extends Controller
{
    public function templates($wp_id)
    {
        $totalTemplates = AutoResMessage::where('wp_id', $wp_id)
            ->where('user_id', Auth::guard('web')
                ->user()->id)
            ->count();

        if ($totalTemplates == 0) {
            $this->storeTemplate($wp_id);
        }

        $data['templates'] = AutoResMessage::where('wp_id', $wp_id)
            ->where('user_id', Auth::guard('web')->user()->id)
            ->orderBy('serial_number', 'asc')
            ->get();

        return view('user.whatsapp.message-templates.index', $data);
    }

    /**
     * Update specified template
     */
    public function updateTemplate(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $template = AutoResMessage::where('id', $id)
            ->where('user_id', Auth::guard('web')->user()->id)
            ->first();

        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $template->message = $request->message;
        $template->save();

        Session::flash('success', __('Updated successfully'));
        return redirect()->back();
    }

    /**
     * If user don't have any template then create default template for them
     */
    private function storeTemplate($wp_id)
    {
        $defaultTemplates = AutoResMessage::where('wp_id', 0)
            ->where('user_id', 0)
            ->orderBy('serial_number', 'asc')
            ->get();

        foreach ($defaultTemplates as $template) {
            AutoResMessage::create([
                'user_id' => Auth::guard('web')->user()->id,
                'wp_id' => $wp_id,
                'event_type' => $template->event_type,
                'message' => $template->message,
                'serial_number' => $template->serial_number,
            ]);
        }
        return true;
    }
}
