<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sitemap;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Sitemap\SitemapGenerator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        $data['langs'] = Language::all();
        $data['sitemaps'] = Sitemap::orderBy('id', 'DESC')->paginate(10);
        return view('admin.sitemap.index', $data);
    }

    public function store(Request $request)
    {

        $rules = [
            'sitemap_url' => 'required|url',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
           return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $data = new Sitemap();
        $input = $request->all();

        @mkdir(public_path('assets/front/files'), 0755, true);
        $filename = 'sitemap' . uniqid() . '.xml';
        SitemapGenerator::create($request->sitemap_url)->writeToFile(public_path('assets/front/files/' . $filename));
        $input['filename']    = $filename;
        $input['sitemap_url'] = $request->sitemap_url;
        $data->fill($input)->save();

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function download(Request $request)
    {
        return response()->download(public_path('assets/front/files/' . $request->filename));
    }

    public function update(Request $request)
    {
        $data  = Sitemap::find($request->id);
        $input = $request->all();
        @unlink(public_path('assets/front/files/' . $data->filename));
        $filename = 'sitemap' . uniqid() . '.xml';
        SitemapGenerator::create($data->sitemap_url)->writeToFile(public_path('assets/front/files/' . $filename));
        $input['filename']  = $filename;
        $data->update($input);
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function delete($id)
    {
        $sitemap = Sitemap::find($id);
        @unlink(public_path('assets/front/files/' . $sitemap->filename));
        $sitemap->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }
}
