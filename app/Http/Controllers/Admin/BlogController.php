<?php

namespace App\Http\Controllers\Admin;

use App\Models\Blog;
use App\Models\Language;
use App\Models\Bcategory;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $lang_id = $lang->id;
        $data['lang_id'] = $lang_id;
        $data['bcats'] = Bcategory::where('language_id', $lang_id)->where('status', 1)->get();
        $data['blogs'] = Blog::query()
        ->join('bcategories', 'blogs.category_index', '=', 'bcategories.indx')
        ->where('blogs.language_id', $lang_id)
        ->where('bcategories.language_id', $lang_id)
        ->orderBy('id', 'DESC')
        ->select('blogs.*', 'bcategories.name as categoryName')
        ->get();


        return view('admin.blog.blog.index', $data);
    }

    public function edit($id)
    {
        $data['blog'] = Blog::findOrFail($id);
        $data['bcats'] = Bcategory::where('language_id', $data['blog']->language_id)->where('status', 1)->get();
        return view('admin.blog.blog.edit', $data);
    }

    public function store(Request $request)
    {
        $img = $request->file('image');
        $allowedExts = array('jpg', 'png', 'jpeg');

        $slug = make_slug($request->title);
        $rules = [
            'language_id' => 'required',
            'title' => 'required|max:255',
            'category' => 'required',
            'content' => 'required',
            'serial_number' => 'required|integer',
            'image' => [
                function ($attribute, $value, $fail) use ($img, $allowedExts) {
                    if (!empty($img)) {
                        $ext = $img->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg image is allowed"));
                        }
                    }
                },
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
           return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $input = $request->all();
        $input['category_index'] = $request->category;
        $input['slug'] = $slug;
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $img->getClientOriginalExtension();
            $request->session()->put('blog_image', $filename);
            $request->file('image')->move(public_path('assets/front/img/blogs/'), $filename);
            $input['main_image'] = $filename;
        }
        $input['content'] = Purifier::clean($request->content, 'youtube');
        $blog = new Blog;
        $blog->create($input);

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {

        $img = $request->file('image');
        $allowedExts = array('jpg', 'png', 'jpeg');
        $slug = make_slug($request->title);
        $blog = Blog::findOrFail($request->blog_id);
        $rules = [
            'title' => 'required|max:255',
            'category' => 'required',
            'content' => 'required',
            'serial_number' => 'required|integer',
            'image' => [
                function ($attribute, $value, $fail) use ($img, $allowedExts) {
                    if (!empty($img)) {
                        $ext = $img->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg image is allowed"));
                        }
                    }
                },
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
         return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $input = $request->all();
        $blog = Blog::findOrFail($request->blog_id);
        $input['category_index'] = $request->category;
        $input['slug'] = $slug;
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $img->getClientOriginalExtension();
            $request->file('image')->move(public_path('assets/front/img/blogs/'), $filename);
            @unlink(public_path('assets/front/img/blogs/' . $blog->main_image));
            $input['main_image'] = $filename;
        }
        $input['content'] = Purifier::clean($request->content, 'youtube');
        $blog->update($input);

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $blog = Blog::findOrFail($request->blog_id);
        @unlink(public_path('assets/front/img/blogs/' . $blog->main_image));
        $blog->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $blog = Blog::findOrFail($id);
            @unlink(public_path('assets/front/img/blogs/' . $blog->main_image));
            $blog->delete();
        }

        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }

    public function getcats($langid)
    {
        $bcategories = Bcategory::where('language_id', $langid)->where('status', 1)->get();
        return $bcategories;
    }
}
