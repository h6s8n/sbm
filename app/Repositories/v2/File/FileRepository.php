<?php


namespace App\Repositories\v2\File;

use App\Traites\RepositoryResponseTrait;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\ImageManagerStatic as Image;

class FileRepository implements FileInterface
{
    use RepositoryResponseTrait;

    public function fileFinalPath($status = 'icon')
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;

        $filesystem = new Filesystem();
        $root_path = $_SERVER['DOCUMENT_ROOT'];


        $root_Path = "{$root_path}/statics-public";
        $file_path = "{$status}/{$year}/{$month}/{$day}/";


        if (!$filesystem->exists("{$root_Path}/{$status}")) {
            $filesystem->makeDirectory("{$root_Path}/{$status}");
        }
        if (!$filesystem->exists("{$root_Path}/{$status}/{$year}")) {
            $filesystem->makeDirectory("{$root_Path}/{$status}/{$year}");
        }
        if (!$filesystem->exists("{$root_Path}/{$status}/{$year}/{$month}")) {
            $filesystem->makeDirectory("{$root_Path}/{$status}/{$year}/{$month}");
        }
        if (!$filesystem->exists("{$root_Path}/{$status}/{$year}/{$month}/{$day}")) {
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

    public function upload($file, $status = 'files')
    {

        $filesystem = new Filesystem();

        $Path = $this->fileFinalPath($status);

        $root_Path = $Path['root_path'];
        $imagePath = $Path['file_path'];
        $path_blank = $Path['path'];
        $imageServer = $Path['url'];

        $fileName = $file->getClientOriginalName();

        $path = "{$path_blank}/{$fileName}";


        if ($filesystem->exists($path)) {
            $fileName = Carbon::now()->timestamp . "-{$fileName}";
        }

        $file->move($path_blank, $fileName);

        return $this->SuccessResponse("{$imageServer}{$fileName}");

    }

    public function UploadLogoOrProfile($file, $status = 'files',
                                        $resize = false,
                                        $extension = 'webp')
    {

        $filesystem = new Filesystem();

        $Path = $this->fileFinalPath($status);

        $root_Path = $Path['root_path'];
        $imagePath = $Path['file_path'];
        $path_blank = $Path['path'];
        $imageServer = $Path['url'];

        $fileName = $file->getClientOriginalName();

        $path = "{$path_blank}/{$fileName}";


        if($filesystem->exists($path)){
            $fileName = Carbon::now()->timestamp . "-{$fileName}.".$extension;
        }

        $file->move($path_blank, $fileName);


        Image::make("{$path_blank}/{$fileName}")->resize(181, null, function ($constraint) {$constraint->aspectRatio(); })
            ->save("{$path_blank}/resize_{$fileName}");

        return $this->SuccessResponse("{$imageServer}resize_{$fileName}");

    }

}
