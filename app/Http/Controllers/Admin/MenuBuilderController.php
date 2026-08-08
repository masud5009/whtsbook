<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuBuilderController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;

        // get page names of selected language
        $pages = Page::where('language_id', $lang->id)->where('status', 1)->get();
        $data["pages"] = $pages;

        // get previous menus
        $menu = Menu::where('language_id', $lang->id)->first();
        $data['prevMenu'] = '';
        if (!empty($menu)) {
            $data['prevMenu'] = $menu->menus;
        }
        return view('admin.menu_builder.index', $data);
    }

    public function update(Request $request)
    {
        Menu::where('language_id', $request->language_id)->delete();
        $menu = new Menu;
        $menu->language_id = $request->language_id;
        $menu->menus = $request->str;
        $menu->save();

        return response()->json(['status' => 'success', 'message' => __('Updated Successfully')]);
    }
}
