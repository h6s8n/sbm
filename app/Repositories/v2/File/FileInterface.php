<?php


namespace App\Repositories\v2\File;


interface FileInterface
{
    public function upload($file,$status = 'files');

    public function UploadLogoOrProfile($file, $status = 'files', $resize = false, $extension = 'webp');
}
