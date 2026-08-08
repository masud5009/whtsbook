<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User\OfflineGateway;
use App\Models\User\PaymentGateway;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class GatewayController extends Controller
{
    public function index()
    {
        $user_id = Auth::guard('web')->user()->id;
        $gateways = PaymentGateway::allGateways($user_id);
        return view('user.gateways.index', compact('gateways'));
    }


    public function updateGateway(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;

        $keyword = (string) $request->keyword;

        $rules = PaymentGateway::validationRules($keyword);

        $bag = 'errors_' . str_replace('.', '_', $keyword);
        $request->validateWithBag($bag, $rules);

        $result = PaymentGateway::storeGateway($request, $userId);

        if ($result['status'] === 'success') {
            Session::flash('success', $result['message']);
            return back();
        }

        Session::flash('error', $result['message']);
        return back();
    }



    public function offline(Request $request)
    {
        $data['ogateways'] = OfflineGateway::query()
            ->where('user_id', Auth::guard('web')->user()->id)
            ->orderBy('serial_number', 'asc')
            ->get();
        return view('user.gateways.offline.index', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'short_description' => 'nullable',
            'serial_number' => 'required|integer',

        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json(
                [
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                400
            );
        }
        OfflineGateway::create($request->except('user_id', 'instructions') + [
            'user_id' => Auth::guard('web')->user()->id,
            'instructions' => Purifier::clean($request->instructions, 'youtube'),
        ]);

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
            return Response::json(
                [
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                400
            );
        }

        $in = $request->except('_token', 'ogateway_id', 'instructions');
        $in['instructions'] = clean($request->instructions);
        OfflineGateway::query()
            ->where('user_id', Auth::guard('web')->user()->id)
            ->where('id', $request->ogateway_id)
            ->update($in);

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function status(Request $request)
    {
        OfflineGateway::query()
            ->where('user_id', Auth::guard('web')->user()->id)
            ->find($request->ogateway_id)
            ->update(['status' => $request->status]);
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        OfflineGateway::query()
            ->where('user_id', Auth::guard('web')->user()->id)
            ->findOrFail($request->offline_gateway_id)
            ->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }
}
