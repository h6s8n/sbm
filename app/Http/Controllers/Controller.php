<?php

namespace App\Http\Controllers;

use App\Model\Platform\Setting;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function update_optien($key , $value){

        $request = setting::where('key', $key)->first();
        if($request){

            $request->value = $value;
            $request->save();

            return $request->id;
        }else{
            $requestNew = new setting;
            $requestNew->key = $key;
            $requestNew->value = $value;
            $requestNew->save();

            return $requestNew->id;
        }

    }

    public function get_optien($key){

        $request = setting::where('key', $key)->first();
        if($request) return $request->value;

        return false;

    }


    public function fileFinalPath1($status = 'icon')
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;

        $filesystem = new Filesystem();


        $root_path = $_SERVER['DOCUMENT_ROOT'];



        $root_Path =  "{$root_path}/statics-public";
        $file_path =  "{$status}/{$year}/{$month}/{$day}/";

        if(!$filesystem->exists("{$root_Path}/{$status}")){
            $filesystem->makeDirectory("{$root_Path}/{$status}");
        }
        if(!$filesystem->exists("{$root_Path}/{$status}/{$year}")){
            $filesystem->makeDirectory("{$root_Path}/{$status}/{$year}");
        }
        if(!$filesystem->exists("{$root_Path}/{$status}/{$year}/{$month}")){
            $filesystem->makeDirectory("{$root_Path}/{$status}/{$year}/{$month}");
        }
        if(!$filesystem->exists("{$root_Path}/{$status}/{$year}/{$month}/{$day}")){
            $filesystem->makeDirectory("{$root_Path}/{$status}/{$year}/{$month}/{$day}");
        }


        //Storage::makeDirectory("{$root_Path}/{$status}");
        //$imageServer = '/statics_public';
        $imageServer = get_ev('statics_server');

        return [
            'root_path' => $root_Path,
            'file_path' => $file_path,
            'path' => "{$root_Path}/{$file_path}",
            'url' => "{$imageServer}/{$file_path}"
        ];

    }

    public function fileFinalPath($status = 'icon')
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;

        $dataDisk = "/mnt/datadisk";
        $root_Path =  "/statics-public";
        $file_path =  "{$status}/{$year}/{$month}/{$day}/";
        $imageServer = 'https://sandbox.sbm24.net/';


        if(!is_dir("{$dataDisk}{$root_Path}/{$status}")){
            Storage::disk('datadisk')->makeDirectory("{$root_Path}/{$status}");
        }
        if(!is_dir("{$dataDisk}{$root_Path}/{$status}/{$year}")){
            Storage::disk('datadisk')->makeDirectory("{$root_Path}/{$status}/{$year}");
        }
        if(!is_dir("{$dataDisk}{$root_Path}/{$status}/{$year}/{$month}")){
            Storage::disk('datadisk')->makeDirectory("{$root_Path}/{$status}/{$year}/{$month}");
        }
        if(!is_dir("{$dataDisk}{$root_Path}/{$status}/{$year}/{$month}/{$day}")){
            Storage::disk('datadisk')->makeDirectory("{$root_Path}/{$status}/{$year}/{$month}/{$day}");
        }

        return [
            'root_path' => $root_Path,
            'file_path' => $file_path,
            'image_server' => $imageServer,
        ];

    }

    public function uploadImageCt1($file_name, $status = 'files'){

        $filesystem = new Filesystem();

        $Path = $this->fileFinalPath($status);

        $root_Path =  $Path['root_path'];
        $imagePath =  $Path['file_path'];
        $path_blank =  $Path['path'];
        $imageServer = $Path['url'];


        $file = $this->request->file($file_name);
        $fileName = $file->getClientOriginalName();

        $path = "{$path_blank}/{$fileName}";


        $pass = explode('.' , $fileName);
        $pass = $pass[count($pass) - 1];
        $fileName = str_random(10) . "." . $pass;

        $file->move($path_blank, $fileName);

        return "{$imageServer}{$fileName}";

    }

    public function uploadImageCt($file_name, $status = 'files'){

        $file = $this->request->file($file_name);

        $Path = $this->fileFinalPath($status);

        $root_Path =  $Path['root_path'];
        $file_path =  $Path['file_path'];
        $imageServer =  $Path['image_server'];

        $storageFile = Storage::disk('datadisk')->put("{$root_Path}/{$file_path}",$file);

        return "{$imageServer}{$storageFile}";

    }

    public function uploadAvatar1($file_name, $status = 'images'){

        $filesystem = new Filesystem();

        $Path = $this->fileFinalPath($status);

        $root_Path =  $Path['root_path'];
        $imagePath =  $Path['file_path'];
        $path_blank =  $Path['path'];
        $imageServer = $Path['url'];

        $file = $this->request->file($file_name);
        $fileName = $file->getClientOriginalName();

        $path = "{$path_blank}/{$fileName}";

        $pass = explode('.' , $fileName);
        $pass = $pass[count($pass) - 1];
        $fileName = str_random(10) . "." . $pass;

        $file->move($path_blank, $fileName);


        Image::make("{$path_blank}/{$fileName}")->resize(181, null, function ($constraint) {$constraint->aspectRatio(); })
            ->save("{$path_blank}/resize_{$fileName}");

        return "{$imageServer}resize_{$fileName}";

    }

    public function uploadAvatar($file_name, $status = 'images'){

        $file = $this->request->file($file_name);

        $Path = $this->fileFinalPath($status);

        $root_Path =  $Path['root_path'];
        $file_path =  $Path['file_path'];
        $imageServer =  $Path['image_server'];

        $storageFile = Storage::disk('datadisk')->put("{$root_Path}/{$file_path}",$file);

        $pass = explode('/' , $storageFile);
        $pass = $pass[count($pass) - 1];
        $fileName = "resize_" . $pass;

        Image::make("/mnt/datadisk/{$storageFile}")->resize(181, null, function ($constraint) {$constraint->aspectRatio(); })
            ->save("/mnt/datadisk/{$root_Path}/{$file_path}/{$fileName}");

        return "{$imageServer}{$root_Path}/{$file_path}/{$fileName}";

    }

}
