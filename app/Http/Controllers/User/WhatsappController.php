<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User\Whatsapp;
use App\Models\User\BotShareInfo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class WhatsappController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Auth::guard('web')->user()->id;
        $data['wp_infos'] =  Whatsapp::where('user_id', $user_id)->get();

        return view('user.whatsapp.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;

        $request->validate([
            'whatsapp_from_number'          => 'required|string|max:30',
            'whatsapp_number_id'            => 'required|string|max:50',
            'whatsapp_business_account_number' => 'required|string|max:50',
            'whatsapp_verify_token'         => 'required|string|max:255',
            'whatsapp_access_token'         => 'required|string',
            'status'                        => 'required|boolean',
        ]);

        Whatsapp::create([
            'user_id'                        => $userId,
            'wp_from_number'                 => $request->whatsapp_from_number,
            'wp_phone_number'                => $request->whatsapp_number_id,
            'wp_business_acc_number'         => $request->whatsapp_business_account_number,
            'wp_verify_token'                => $request->whatsapp_verify_token,
            'wp_access_token'                => $request->whatsapp_access_token,
            'status'                         => $request->status,
        ]);

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;

        $request->validate([
            'whatsapp_from_number'          => 'required|string|max:30',
            'whatsapp_number_id'            => 'required|string|max:50',
            'whatsapp_business_account_number' => 'required|string|max:50',
            'whatsapp_verify_token'         => 'required|string|max:255',
            'whatsapp_access_token'         => 'required|string',
            'status'                        => 'required|boolean',
        ]);

        Whatsapp::findOrFail($request->id)->update([
            'user_id'                        => $userId,
            'wp_from_number'                 => $request->whatsapp_from_number,
            'wp_phone_number'                => $request->whatsapp_number_id,
            'wp_business_acc_number'         => $request->whatsapp_business_account_number,
            'wp_verify_token'                => $request->whatsapp_verify_token,
            'wp_access_token'                => $request->whatsapp_access_token,
            'status'                         => $request->status,
        ]);

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request)
    {
        BotShareInfo::where('wp_id', $request->id)->delete();
        Whatsapp::findOrFail($request->id)->delete();
        Session::flash('success', __('Deleted Successfully'));
        return redirect()->back();
    }
    /**
     * Bulk Delete
     */

    public function bulk_delete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            BotShareInfo::where('wp_id', $id)->delete();
            Whatsapp::findOrFail($id)->delete();
        }
        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }

    /**
     *Share Information : If customer ask to share location, number, etc. via WhatsApp
     */
    public function shareInformation($wp_id)
    {
        $share_info = BotShareInfo::firstOrCreate(
            ['user_id' => Auth::guard('web')->user()->id, 'wp_id' => $wp_id],
            ['wp_id' => $wp_id, 'hotel_name' => null, 'email_address' => [], 'phone_numbers' => [], 'locations' => []]
        );

        return view('user.whatsapp.share_information', compact('share_info', 'wp_id'));
    }

    public function updateShareInformation(Request $request)
    {
        $validated = $request->validate([
            'wp_id'          => 'required|integer',
            'hotel_name'     => 'nullable|string|max:255',
            'email_address'  => 'nullable|string',
            'phone_numbers'  => 'nullable|string'
        ]);

        $userId = Auth::guard('web')->id();

        $botInfo = BotShareInfo::where('wp_id', $validated['wp_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        $botInfo->update([
            'hotel_name'    => $validated['hotel_name'] ?? null,
            'email_address' => $this->toJsonArray($validated['email_address'] ?? null),
            'phone_numbers' => $this->toJsonArray($validated['phone_numbers'] ?? null),
        ]);

        return back()->with('success', __('Information updated successfully.'));
    }


    private function toJsonArray($value)
    {
        if (blank($value)) return null;
        $array = array_filter(array_map('trim', explode(',', $value)));
        return !empty($array) ? $array : null;
    }


    public function configureBookingFields($wp_id)
    {
        $whatsapp = Whatsapp::findOrFail($wp_id);
        $customFields = $whatsapp->custom_booking_fields;

        // Ensure customFields is always an array
        if (is_string($customFields)) {
            $customFields = json_decode($customFields, true) ?? [];
        } elseif (is_null($customFields)) {
            $customFields = [];
        }

        return view('user.whatsapp.configure_booking_fields', compact('wp_id', 'customFields'));
    }

    public function updateconfigureBookingFields(Request $request)
    {
        $request->validate([
            'wp_id' => 'required|integer',
            'fields_data' => 'required|json'
        ]);

        $userId = Auth::guard('web')->user()->id;
        $whatsapp = Whatsapp::where('id', $request->wp_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $fieldsData = json_decode($request->fields_data, true);

        // Validate fields data
        if (!is_array($fieldsData)) {
            return response()->json(['error' => __('No fields provided')], 422);
        }

        // Validate each field
        foreach ($fieldsData as $field) {
            if (empty($field['label'])) {
                return response()->json(['error' => __('All fields must have a label')], 422);
            }
        }

        // Save to database
        $whatsapp->custom_booking_fields = $fieldsData;
        $whatsapp->save();

        return response()->json(['success' => true]);
    }
}
