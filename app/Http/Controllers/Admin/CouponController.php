<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $data['coupons'] = Coupon::orderBy('id', 'DESC')->paginate(10);
        $data['packages'] = Package::where('status', '1')->get();
        return view('admin.packages.coupons.index', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'code' => 'required|unique:coupons',
            'type' => 'required',
            'value' => 'required | numeric',
            'start_date' => 'required | date',
            'maximum_uses_limit' => 'required',
            'end_date' => 'required | date',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $input = $request->except('_token');
        $input['packages'] = json_encode($request->packages);
        Coupon::create($input);

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function edit($id)
    {
        $data['coupon'] = Coupon::findOrFail($id);
        $data['packages'] = Package::where('status', '1')->get();
        $data['selectedPackages'] = !empty($data['coupon']->packages) ? json_decode($data['coupon']->packages, true) : [];
        return view('admin.packages.coupons.edit', $data);
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required',
            'code' => 'required|unique:coupons,code,' . $request->coupon_id,
            'type' => 'required',
            'value' => 'required | numeric',
            'start_date' => 'required | date',
            'end_date' => 'required | date',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $input = $request->except('_token', 'coupon_id');
        $data = Coupon::find($request->coupon_id);
        $packages = !empty($request->packages) ? json_encode($request->packages) : NULL;
        $input['packages'] = $packages;
        $data->fill($input)->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $coupon = Coupon::find($request->coupon_id);
        $coupon->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }
}
