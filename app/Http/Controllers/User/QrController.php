<?php

namespace App\Http\Controllers\User;

use App\Constants\Constant;
use Illuminate\Http\Request;
use App\Models\User\UserQrCode;
use App\Models\User\BasicSetting;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Uploader;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    public function index()
    {
        $data['qrcodes'] = UserQrCode::orderBy('id', 'DESC')->get();
        return view('user.qr.index', $data);
    }

    public function qrCode()
    {
        $basic = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();
        if (empty($basic->qr_image) || !file_exists(public_path(Constant::WEBSITE_QR .'/' . $basic->qr_image))) {
            $directory = public_path(Constant::WEBSITE_QR);
            @mkdir(public_path($directory), 0775, true);
            $fileName = uniqid() . '.png';

            QrCode::size(250)->errorCorrection('H')
                ->color(0, 0, 0)
                ->format('png')
                ->style('square')
                ->eye('square')
                ->generate(url('/'), $directory . $fileName);

            $basic->qr_image = $fileName;
            $basic->qr_url = url('/');
            $basic->save();
        }

        $data['abs'] = $basic;
        return view('user.qr.generate', $data);
    }

    public function generate(Request $request)
    {
        if (!$request->filled('url')) {
            return "url_empty";
        }
        $img = $request->file('image');
        $type = $request->type;
        $basic = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();
        // set default values for all params of qr image, if there is no value for a param
        $color = hex2rgb($request->color);

        $directory = Constant::WEBSITE_QR .'/';
        @mkdir(public_path($directory), 0775, true);
        $qrImage = uniqid() . '.png';
        @unlink(public_path($directory) . $basic->qr_image);
        // new QR code init
        $qrcode = QrCode::size($request->size)
            ->errorCorrection('H')
            ->margin($request->margin)
            ->color($color['red'], $color['green'], $color['blue'])
            ->format('png')
            ->style($request->style)
            ->eye($request->eye_style);

        if ($type == 'image' && $request->hasFile('image')) {

            if ($basic->qr_inserted_image && file_exists(public_path($directory) . $basic->qr_inserted_image)) {
                unlink(public_path($directory) . $basic->qr_inserted_image);
            }
            $mergedImage = uniqid() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path($directory), $mergedImage);
        }

        // generating & saving the qr code in folder
        $qrcode->generate($request->url, public_path($directory) . $qrImage);

        // calculate the inserted image size
        $qrSize = $request->size;

        if ($type == 'image') {
            $imageSize = $request->image_size;
            $insertedImgSize = ($qrSize * $imageSize) / 100;

            // inserting image using Image Intervention & saving the qr code in folder
            if ($request->hasFile('image')) {
                $qr = Image::make(public_path($directory) . $qrImage);
                $logo = Image::make(public_path($directory) . $mergedImage);
                $logo->resize(null, $insertedImgSize, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $logoWidth = $logo->width();
                $logoHeight = $logo->height();

                $qr->insert($logo, 'top-left', (int) (((($qrSize - $logoWidth) * $request->image_x) / 100)), (int) (((($qrSize - $logoHeight) * $request->image_y) / 100)));
                $qr->save(public_path($directory) . $qrImage);
            } else {
                if (!empty($basic->qr_inserted_image) && file_exists(public_path($directory) . $basic->qr_inserted_image)) {
                    $qr = Image::make(public_path($directory) . $qrImage);
                    $logo = Image::make(public_path($directory) . $basic->qr_inserted_image);
                    $logo->resize(null, $insertedImgSize, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $logoWidth = $logo->width();
                    $logoHeight = $logo->height();

                    $qr->insert($logo, 'top-left', (int) (((($qrSize - $logoWidth) * $request->image_x) / 100)), (int) (((($qrSize - $logoHeight) * $request->image_y) / 100)));
                    $qr->save(public_path($directory) . $qrImage);
                }
            }
        }

        if ($type == 'text') {
            $textSize = $request->text_size;
            $insertedImgSize = ($qrSize * $textSize) / 100;

            $qr = Image::make(public_path($directory) . $qrImage);

            $logo = Image::canvas($request->text_width, $insertedImgSize, "#ffffff")
                ->text($request->text, 0, 0, function ($font) use ($request, $insertedImgSize) {
                    $font->file(public_path('assets/tenant/fonts/Lato-Regular.ttf'));
                    $font->size($insertedImgSize);
                    $font->color('#' . $request->text_color);
                    $font->align('left');
                    $font->valign('top');
                });

            $logoWidth = $logo->width();
            $logoHeight = $logo->height();

            // use callback to define details
            $qr->insert($logo, 'top-left', (int) ((($qrSize - $logoWidth) * $request->text_x) / 100), (int) ((($qrSize - $logoHeight) * $request->text_y) / 100));
            $qr->save(public_path($directory) . $qrImage);
        }

        $basic->qr_color = $request->color;
        $basic->qr_size = $request->size;
        $basic->qr_style = $request->style;
        $basic->qr_eye_style = $request->eye_style;
        $basic->qr_image = $qrImage;
        $basic->qr_type = $type;

        if ($type == 'image') {
            if ($request->hasFile('image')) {
                $basic->qr_inserted_image = $mergedImage;
            }

            $basic->qr_inserted_image_size = $imageSize;
            $basic->qr_inserted_image_x = $request->image_x;
            $basic->qr_inserted_image_y = $request->image_y;
        }

        if ($type == 'text' && !empty($request->text)) {
            $basic->qr_text = $request->text;
            $basic->qr_text_color = $request->text_color;
            $basic->qr_text_size = $request->text_size;
            $basic->qr_text_x = $request->text_x;
            $basic->qr_text_y = $request->text_y;
        }

        $basic->qr_margin = $request->margin;
        $basic->qr_url = $request->url;
        $basic->save();
        return asset($directory . $qrImage);
    }

    public function save(Request $request)
    {
        $rules = [
            'name' => 'required|max:255'
        ];
        $request->validate($rules);
        $basic = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();

        $qrcode = new UserQrCode();
        $qrcode->name = $request->name;
        $qrcode->image = $basic->qr_image;
        $qrcode->url = $basic->qr_url;
        $qrcode->save();
        $this->clearFilters($basic);

        Session::flash('success', __('Created Successfully'));
        return back();
    }

    public function clear()
    {
        $basic = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();
        $this->clearFilters($basic, 'clear');
        Session::flash('success', 'Cleared all filters');
        return back();
    }

    public function clearFilters($basic, $type = NULL)
    {
        Uploader::remove(public_path(Constant::WEBSITE_QR), $basic->qr_inserted_image);
        if ($type == 'clear') {
            Uploader::remove(public_path(Constant::WEBSITE_QR), $basic->qr_image);
        }

        $basic->qr_image = NULL;
        $basic->qr_color = '000000';
        $basic->qr_size = 250;
        $basic->qr_style = 'square';
        $basic->qr_eye_style = 'square';
        $basic->qr_margin = 0;
        $basic->qr_text = NULL;
        $basic->qr_text_color = '000000';
        $basic->qr_text_size = 15;
        $basic->qr_text_x = 50;
        $basic->qr_text_y = 50;
        $basic->qr_inserted_image = NULL;
        $basic->qr_inserted_image_size = 20;
        $basic->qr_inserted_image_x = 50;
        $basic->qr_inserted_image_y = 50;
        $basic->qr_type = 'default';
        $basic->qr_url = NULL;
        $basic->save();
    }

    public function delete(Request $request)
    {
        $qrcode = UserQrCode::where('id', $request->qrcode_id)->firstOrFail();
        Uploader::remove(public_path(Constant::WEBSITE_QR), $qrcode->image);
        $qrcode->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $qrcode = UserQrCode::where('id', $id)->firstOrFail();
            Uploader::remove(public_path(Constant::WEBSITE_QR), $qrcode->image);
            $qrcode->delete();
        }
        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }

    public function download($name)
    {
        $userId = getRootUser()->id;
        $bs = AdminBs::select('aws_access_key_id', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket')
            ->first();

        $pathToFile = Constant::WEBSITE_QR_IMAGE . '/' . $name;
        if (!is_null($bs->aws_access_key_id) && !is_null($bs->aws_secret_access_key) && !is_null($bs->aws_default_region) && !is_null($bs->aws_bucket)) {
            setAwsCredentials($bs->aws_access_key_id, $bs->aws_secret_access_key, $bs->aws_default_region, $bs->aws_bucket);
            if (Storage::disk('s3')->exists($pathToFile)) {
                $headers = [
                    'Content-Type'        => 'image/png',
                    'Content-Disposition' => 'attachment; filename="' . $name . '"',
                ];
                return \Response::make(Storage::disk('s3')->get($pathToFile), 200, $headers);
            } else {
                return response()->download(public_path($pathToFile), $name);
            }
        } else {
            return response()->download(public_path($pathToFile), $name);
        }
    }
}
