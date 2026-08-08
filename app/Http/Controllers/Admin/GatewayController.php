<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\OfflineGateway;
use App\Models\PaymentGateway;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class GatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::allGateways();
        return view('admin.gateways.index', compact('gateways'));
    }


    public function updateGateway(Request $request)
    {
        $keyword = (string) $request->keyword;

        $rules = PaymentGateway::validationRules($keyword);

        $bag = 'errors_' . str_replace('.', '_', $keyword);
        $request->validateWithBag($bag, $rules);

        $result = PaymentGateway::storeGateway($request);

        if ($result['status'] === 'success') {
            Session::flash('success', $result['message']);
            return back();
        }

        Session::flash('error', $result['message']);
        return back();
    }

    public function offline(Request $request)
    {
        $data['ogateways'] = OfflineGateway::orderBy('serial_number', 'asc')->get();
        return view('admin.gateways.offline.index', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'short_description' => 'nullable',
            'serial_number' => 'required|integer',
            'is_receipt' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $in = $request->except(['short_description', 'instructions']);
        $in['short_description'] = Purifier::clean($request->short_description, 'youtube');
        $in['instructions'] = Purifier::clean($request->instructions, 'youtube');
        OfflineGateway::create($in);

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'short_description' => 'nullable',
            'serial_number' => 'required|integer',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $in = $request->except('_token', 'ogateway_id', 'short_description', 'instructions');
        $in['short_description'] = Purifier::clean($request->short_description, 'youtube');
        $in['instructions'] = Purifier::clean($request->instructions, 'youtube');
        OfflineGateway::where('id', $request->ogateway_id)->update($in);

        Session::flash('success', __("Updated Successfully"));
        return "success";
    }

    public function status(Request $request)
    {
        $og = OfflineGateway::find($request->ogateway_id);
        $og->status = $request->status;
        $og->save();
        Session::flash('success', __("Updated Successfully"));
        return back();
    }

    public function delete(Request $request)
    {
        $ogateway = OfflineGateway::findOrFail($request->ogateway_id);
        $ogateway->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }
}
