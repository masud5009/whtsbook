<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transcation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TranscationController extends Controller
{
    //transcation 
    public function transcation()
    {
        $transcations = Transcation::orderBy('id', 'desc')->paginate(10);
        return view('admin.transcation', compact('transcations'));
    }

    //destroy
    public function destroy(Request $request)
    {
        $transcation = Transcation::findOrFail($request->id);
        $transcation->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back()->with('success');
    }

    //destroy
    public function bulk_destroy(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $transcation = Transcation::findOrFail($id);
            $transcation->delete();
        }
        
        Session::flash('success', __('Deleted Successfully'));
        return 'success';
    }
}
