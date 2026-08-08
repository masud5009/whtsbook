<?php

namespace App\Http\Controllers\Admin;

use App\Models\Package;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Package\PackageStoreRequest;
use App\Http\Requests\Package\PackageUpdateRequest;

class PackageController extends Controller
{
    public function settings()
    {
        $data['abe'] = BasicExtended::first();
        return view('admin.packages.settings', $data);
    }

    public function updateSettings(Request $request)
    {
        $rules = [
            'expiration_reminder' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
         return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $be = BasicExtended::first();
        $be->expiration_reminder = $request->expiration_reminder;
        $be->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }
    public function features()
    {
        $be = BasicExtended::first();
        $features = json_decode($be->package_features, true);
        $data['features'] = $features;
        return view('admin.packages.features', $data);
    }

    public function updateFeatures(Request $request)
    {
        $RoomPackage = $request->ExternalPackge;
        Session::put('ExternalPackge', $RoomPackage);
        $features = $request->features ? json_encode($request->features) : null;
        $bes = BasicExtended::all();
        foreach ($bes as $key => $be) {
            $be->package_features = $features;
            $be->save();
        }

        Session::flash('success', __('Updated Successfully'));
        return back();
    }
    /**
     * Display a listing of the resource.
     *
     *
     */
    public function index(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $search = $request->search;
        $data['bex'] = $currentLang->basic_extended;
        $data['packages'] = Package::query()->when($search, function ($query, $search) {
            return $query->where('title', 'like', '%' . $search . '%');
        })->orderBy('created_at', 'DESC')->get();
        return view('admin.packages.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     */
    public function store(PackageStoreRequest $request)
    {
        try {
            if (!isset($request->featured)) {
                $request["featured"] = "0";
            }

            $features = $request->features ? json_encode($request->features) : json_encode([]);

            return DB::transaction(function () use ($request, $features) {
                Package::create($request->except('features') + [
                    'slug' => make_slug($request->title),
                    'features' => $features,
                ]);
                Session::flash('success', __("Created Successfully"));
                return "success";
            });
        } catch (\Throwable $e) {

            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return
     */
    public function edit($id)
    {
            if (session()->has('lang')) {
                $currentLang = Language::where('code', session()->get('lang'))->first();
            } else {
                $currentLang = Language::where('is_default', 1)->first();
            }
            $data['bex'] = $currentLang->basic_extended;
            $data['package'] = Package::query()->findOrFail($id);
            return view("admin.packages.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     */
    public function update(PackageUpdateRequest $request)
    {
        try {
            if (!array_key_exists('is_trial', $request->all())) {
                $request['is_trial'] = "0";
                $request['trial_days'] = 0;
            }

            if (!isset($request->featured)) {
                $request["featured"] = "0";
            }

            $features = $request->features ? json_encode($request->features) : json_encode([]);
            return DB::transaction(function () use ($request, $features) {
                Package::query()->findOrFail($request->package_id)
                    ->update($request->except('features', 'package_id') + [
                        'slug' => make_slug($request->title),
                        'features' => $features,
                    ]);
                Session::flash('success', __("Updated Successfully"));
                return "success";
            });
        } catch (\Throwable $e) {
            return $e;
        }
    }

    public function delete(Request $request)
    {
        $package = Package::query()->findOrFail($request->package_id);
        if ($package->memberships()->count() > 0) {
            foreach ($package->memberships as $key => $membership) {
                @unlink(public_path('assets/front/img/membership/receipt/') . $membership->receipt);
                $membership->delete();
            }
        }
        $package->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $ids = $request->ids;
                foreach ($ids as $id) {
                    $package = Package::query()->findOrFail($id);
                    if ($package->memberships()->count() > 0) {
                        foreach ($package->memberships as $key => $membership) {
                            @unlink(public_path('assets/front/img/membership/receipt/') . $membership->receipt);
                            $membership->delete();
                        }
                    }
                    $package->delete();
                }
                Session::flash('success', __('Deleted Successfully'));
                return "success";
            });
        } catch (\Throwable $e) {
            return $e;
        }
    }
}
