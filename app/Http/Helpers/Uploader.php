<?php

namespace App\Http\Helpers;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class Uploader
{
    public static function upload_picture($directory, $img, $user_id = null): string
    {

        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }

        $file_name = sha1(time() . rand());
        $ext = $img->getClientOriginalExtension();
        $newFileName = $file_name . "." . $ext;

        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
                die('Failed to create folders...');
            }
        }
        $img->move($directory, $newFileName);
        return $newFileName;
    }

    public static function update_picture($directory, $img, $old_img, $user_id = null): string
    {
        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }

        $file_name = sha1(time() . rand());
        $ext = $img->getClientOriginalExtension();
        $newFileName = $file_name . "." . $ext;
        $oldImgPath = $directory . '/' . $old_img;

        @unlink($oldImgPath);

        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
                die('Failed to create folders...');
            }
        }

        $img->move($directory, $newFileName);
        return $newFileName;
    }

    public static function upload_file($directory, $file, $user_id = null)
    {
        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }
        $file_name = sha1(time() . rand());
        $originalName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();
        $newFileName = $file_name . "." . $ext;

        if (!file_exists($directory)) {
            if (!mkdir($directory, 0755, true)) {
                die('Failed to create folders...');
            }
        }
        $file->move($directory, $newFileName);
        return [
            'originalName' => $originalName,
            'uniqueName' => $newFileName
        ];
    }

    public static function getImageUrl($directory, $img, $defaultUrl = 'assets/tenant/img/default.jpg'): string
    {
        $url = $directory . '/' . $img;
        if (!is_null($img)) {
            return asset($url);
        } else {
            return asset($defaultUrl);
        }
    }
    public static function downloadFile($directory, $file_unique_name, $originalName, $bs)
    {
        $pathToFile = $directory . '/' . $file_unique_name;

        return response()->download($pathToFile, $originalName);
    }

    public static function remove($directory, $filename, $bs = null, $userId = null): void
    {
        $pathToFile = $directory . '/' . $filename;
        if (file_exists($pathToFile)) {
            @unlink($pathToFile);
        }
    }
    public static function thumbnail_image($directory, $file, $exiting_image = null)
    {


        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }


        if(!is_null($exiting_image)){
            @unlink($directory . $exiting_image);
        }
        $image_name2 = uniqid() . '.png';
        $manager = new ImageManager(Driver::class);
        $image1 = $manager->read($file->getPathname());
        $image1->resize(370, 250);
        $image1->save($directory . $image_name2);
        return $image_name2;
    }
    public static function thumbnail_image_2($directory, $file, $exiting_image = null)
    {
        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }

        @mkdir($directory, 0775, true);
        if (!is_null($exiting_image)) {
            @unlink($directory . $exiting_image);
        }
        $image_name2 = uniqid() . '.png';
        $manager = new ImageManager(Driver::class);
        $image1 = $manager->read($file->getPathname());
        $image1->resize(300, 360);
        $image1->save($directory . $image_name2);
        return $image_name2;
    }
    public static function profile_image($directory, $file, $exiting_image = null)
    {

        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }
        @mkdir($directory, 0775, true);
        if (!is_null($exiting_image)) {
            @unlink($directory . $exiting_image);
        }
        $image_name2 = uniqid() . '.png';
        $manager = new ImageManager(Driver::class);
        $image1 = $manager->read($file->getPathname());
        $image1->resize(100, 100);
        $image1->save($directory . $image_name2);
        return $image_name2;
    }
    public static function logo_image($directory, $file, $exiting_image = null,$width = 322, $heigh=115)
    {
        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }

        @mkdir($directory, 0775, true);
        if (!is_null($exiting_image)) {
            @unlink($directory . $exiting_image);
        }
        $image_name2 = uniqid() . '.png';
        $manager = new ImageManager(Driver::class);
        $image1 = $manager->read($file->getPathname());
        $image1->resize($width, $heigh);
        $image1->save($directory . $image_name2);
        return $image_name2;
    }
}
